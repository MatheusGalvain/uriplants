<?php
require_once __DIR__ . '/../config/database.php';

class PlantController {

    public function get() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $conn = getConnection();

            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                if ($id <= 0) {
                    echo json_encode(["message" => "Parâmetro 'id' inválido"]);
                    return;
                }

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
                    LEFT JOIN images ON plantsproperties.id = images.plants_property_id
                    WHERE plants.id = ?";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    echo json_encode(["message" => "Erro na preparação da consulta", "error" => $conn->error]);
                    return;
                }

                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $plant = $result->fetch_assoc();
                        
                        if (!empty($plant['region_map_image'])) {
                            $plant['region_map_image'] = base64_encode($plant['region_map_image']);
                        }
                        if (!empty($plant['image_blob'])) {
                            $plant['image_blob'] = base64_encode($plant['image_blob']);
                        }

                        echo json_encode($plant, JSON_UNESCAPED_UNICODE);
                    } else {
                        echo json_encode(["message" => "Planta não encontrada"]);
                    }
                } else {
                    echo json_encode(["message" => "Erro ao buscar planta", "error" => $stmt->error]);
                }

                $stmt->close();
                $conn->close();
                return;
            }

            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1;
            if ($limit <= 0) $limit = 1;

            $sql = "SELECT 
                        plants.name, 
                        plants.ecology AS description, 
                        images.imagem AS image_blob
                    FROM plants
                    LEFT JOIN plantsproperties ON plants.id = plantsproperties.plant_id
                    LEFT JOIN properties ON plantsproperties.property_id = properties.id
                    LEFT JOIN images ON plantsproperties.id = images.plants_property_id
                    WHERE properties.name = 'planta'
                    GROUP BY plants.id
                    ORDER BY plants.id ASC
                    LIMIT ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                echo json_encode(["message" => "Erro na preparação da consulta padrão", "error" => $conn->error]);
                return;
            }

            $stmt->bind_param("i", $limit);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $plants = [];
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        if (!empty($row['image_blob'])) {
                            $row['image_blob'] = base64_encode($row['image_blob']);
                        }
                        $plants[] = $row;
                    }
                }

                echo json_encode($plants, JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(["message" => "Erro ao buscar plantas", "error" => $stmt->error]);
            }

            $stmt->close();
            $conn->close();
        }
    }
}
?>
