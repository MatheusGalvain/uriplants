<?php

require_once __DIR__ . '/../config/database.php';

class quizController {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function get() {
        header('Content-Type: application/json');

        try {

            $correctPlantQuery = "SELECT id, name FROM Plants ORDER BY RAND() LIMIT 1";
            $result = $this->conn->query($correctPlantQuery);

            if ($result->num_rows === 0) {
                echo json_encode(["message" => "Nenhuma planta encontrada."]);
                return;
            }

            $correctPlant = $result->fetch_assoc();
            $correctPlantId = $correctPlant['id'];
            $correctPlantName = $correctPlant['name'];

            $imagesQuery = "
                SELECT Images.imagem 
                FROM Images 
                INNER JOIN PlantsProperties ON Images.plants_property_id = PlantsProperties.id 
                WHERE PlantsProperties.plant_id = ? AND PlantsProperties.property_id = 1
            ";
            $stmt = $this->conn->prepare($imagesQuery);
            $stmt->bind_param("i", $correctPlantId);
            $stmt->execute();
            $imagesResult = $stmt->get_result();

            $images = [];
            while ($row = $imagesResult->fetch_assoc()) {

                $base64 = base64_encode($row['imagem']);

                $dataUrl = "data:image/png;base64," . $base64;
                $images[] = $dataUrl;
            }

            if (empty($images)) {
                echo json_encode(["message" => "Nenhuma imagem encontrada para a planta correta."]);
                return;
            }

            $wrongOptionsQuery = "
                SELECT name FROM Plants 
                WHERE id != ? 
                ORDER BY RAND() 
                LIMIT 3
            ";
            $stmt = $this->conn->prepare($wrongOptionsQuery);
            $stmt->bind_param("i", $correctPlantId);
            $stmt->execute();
            $wrongOptionsResult = $stmt->get_result();

            $wrongOptions = [];
            while ($row = $wrongOptionsResult->fetch_assoc()) {
                $wrongOptions[] = $row['name'];
            }

            if (count($wrongOptions) < 3) {
                echo json_encode(["message" => "Não há plantas suficientes para opções incorretas."]);
                return;
            }

            $options = $wrongOptions;
            $options[] = $correctPlantName;
            shuffle($options);

            $response = [
                "images" => $images,
                "question" => "Quem é esse pokémon?",
                "options" => $options,
                "correct_answer" => $correctPlantName
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Erro interno no servidor.", "error" => $e->getMessage()]);
        }
    }

}
?>
