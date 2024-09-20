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
    
            $sql = "SELECT plants.id, plants.name, plants.common_names, 
            plants.species, plants.applications, plants.ecology, plants.created_at, 
            plants.deleted_at, families.name AS family_name, orders.name AS order_name,
            divisions.name AS division_name, classes.name AS class_name, genus.name AS genus_name,
            regionmap.id AS region_map_id, regionmap.source AS region_map_source, 
            regionmap.description AS region_map_description, regionmap.imagem AS region_map_image,
            properties.name AS property_name, plantsproperties.description AS plant_property_description,
            images.id AS image_id, images.imagem AS image_blob, images.source AS image_source
                FROM plants
                LEFT JOIN families ON plants.family_id = families.id
                LEFT JOIN orders ON plants.order_id = orders.id
                LEFT JOIN divisions ON plants.division_id = divisions.id
                LEFT JOIN classes ON plants.class_id = classes.id
                LEFT JOIN genus ON plants.genus_id = genus.id
                LEFT JOIN regionmap ON plants.region_id = regionmap.id
                LEFT JOIN plantsproperties ON plants.id = plantsproperties.plant_id
                LEFT JOIN properties ON plantsproperties.property_id = properties.id
                LEFT JOIN images ON plantsproperties.id = images.plants_property_id";

            $result = $conn->query($sql);
    
            $plants = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    if (!empty($row['region_map_image'])) {
                        $row['region_map_image'] = base64_encode($row['region_map_image']);
                    }
                    if (!empty($row['image_blob'])) {
                        $row['image_blob'] = base64_encode($row['image_blob']);
                    }
                    $plants[] = $row;
                }
            }

            echo json_encode($plants, JSON_UNESCAPED_UNICODE);
    
            $conn->close();
        }
    }
    
}
