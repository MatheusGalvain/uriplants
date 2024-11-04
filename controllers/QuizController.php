<?php
require_once __DIR__ . '/../config/database.php';

class quizController
{
    private $conn;

    public function __construct()
    {
        $this->conn = getConnection();
    }

    public function get()
    {
        header('Content-Type: application/json');

        try {
            // Consulta para obter uma planta aleatória que tenha pelo menos uma imagem
            $correctPlantQuery = "
                SELECT p.id, p.name 
                FROM Plants p
                INNER JOIN PlantsProperties pp ON p.id = pp.plant_id
                INNER JOIN Images i ON pp.id = i.plants_property_id
                WHERE pp.property_id = 1 AND p.deleted_at IS NULL
                GROUP BY p.id
                HAVING COUNT(i.imagem) > 0
                ORDER BY RAND()
                LIMIT 1
            ";
            $result = $this->conn->query($correctPlantQuery);

            if ($result->num_rows === 0) {
                echo json_encode(["message" => "Nenhuma planta com imagens encontrada."]);
                return;
            }

            $correctPlant = $result->fetch_assoc();
            $correctPlantId = $correctPlant['id'];
            $correctPlantName = $correctPlant['name'];

            // Consulta para obter as imagens da planta correta
            $imagesQuery = "
                SELECT i.imagem 
                FROM Images i
                INNER JOIN PlantsProperties pp ON i.plants_property_id = pp.id 
                WHERE pp.plant_id = ? AND pp.property_id = 1
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

            // Consulta para obter as opções incorretas
            $wrongOptionsQuery = "
                SELECT p.name 
                FROM Plants p
                INNER JOIN PlantsProperties pp ON p.id = pp.plant_id
                INNER JOIN Images i ON pp.id = i.plants_property_id
                WHERE p.id != ? AND pp.property_id = 1 AND p.deleted_at IS NULL
                GROUP BY p.id
                HAVING COUNT(i.imagem) > 0
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

            // Embaralha as opções (incluindo a correta)
            $options = $wrongOptions;
            $options[] = $correctPlantName;
            shuffle($options);

            // Resposta final do quiz
            $response = [
                "images" => $images,
                "question" => "Qual o nome dessa planta?",
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
