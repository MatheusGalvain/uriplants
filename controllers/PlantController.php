<?php
require_once __DIR__ . '/../config/database.php';
class PlantController {

    public function insert() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!isset($data['name'])) {
                echo json_encode(["message" => "Nome da planta é obrigatório"]);
                return;
            }

            if (!isset($data['division_id']) || !isset($data['class_id']) || !isset($data['order_id']) || 
                !isset($data['family_id']) || !isset($data['genus_id']) || !isset($data['species']) || 
                !isset($data['biological_form']) || !isset($data['region_id']) || !isset($data['applications'])) {
                echo json_encode(["message" => "Todos os campos são obrigatórios"]);
                return;
            }

            $conn = getConnection();

            $stmt = $conn->prepare("INSERT INTO plants (name, division_id, class_id, order_id, family_id, genus_id, species, biological_form, region_id, applications, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("siiiiissis", 
                $data['name'], 
                $data['division_id'], 
                $data['class_id'], 
                $data['order_id'], 
                $data['family_id'], 
                $data['genus_id'], 
                $data['species'], 
                $data['biological_form'], 
                $data['region_id'], 
                $data['applications']
            );

            if ($stmt->execute()) {
                echo json_encode(["message" => "Planta inserida com sucesso"]);
            } else {
                echo json_encode(["message" => "Erro ao inserir planta", "error" => $stmt->error]);
            }

            $stmt->close();
            $conn->close();
        }
    }

    public function get() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $conn = getConnection();

            $sql = "SELECT * FROM plants";
            $result = $conn->query($sql);

            $plants = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $plants[] = $row;
                }
            }

            echo json_encode($plants);

            $conn->close();
        }
    }
}
