<?php
require_once __DIR__ . '/../config/database.php';

class PlantController {

    /**
     * Obtém uma única planta pelo ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getSinglePlant($id) {
        if ($id <= 0) {
            return ["message" => "Parâmetro 'id' inválido"];
        }

        $conn = getConnection();

        $sql = "SELECT plants.id, plants.name, plants.common_names, plants.bark_description, plants.trunk_description, 
                plants.leaf_description, plants.flower_description, plants.fruit_description, plants.seed_description, 
                plants.biology, plants.species, plants.applications, plants.ecology, plants.created_at, 
                plants.deleted_at, families.name AS family_name, orders.name AS order_name,
                divisions.name AS division_name, classes.name AS class_name, genus.name AS genus_name,
                regionmap.id AS region_map_id, regionmap.source AS region_map_source, 
                regionmap.description AS region_map_description, regionmap.imagem AS region_map_image,
                properties.name AS property_name,
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
                WHERE plants.id = ?
                ORDER BY images.sort_order ASC"; // Adiciona ordenação

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return ["message" => "Erro na preparação da consulta", "error" => $conn->error];
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

                $stmt->close();
                $conn->close();
                return $plant;
            } else {
                $stmt->close();
                $conn->close();
                return ["message" => "Planta não encontrada"];
            }
        } else {
            $error = ["message" => "Erro ao buscar planta", "error" => $stmt->error];
            $stmt->close();
            $conn->close();
            return $error;
        }
    }

    /**
     * Obtém uma lista de plantas com paginação.
     *
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getPlants($limit = 10, $page = 1) {
        if ($limit <= 0) $limit = 10;
        if ($page <= 0) $page = 1;

        $offset = ($page - 1) * $limit;

        $conn = getConnection();

        // Consulta para obter o total de plantas
        $countSql = "SELECT COUNT(DISTINCT plants.id) as total 
                     FROM plants
                     LEFT JOIN plantsproperties ON plants.id = plantsproperties.plant_id
                     LEFT JOIN properties ON plantsproperties.property_id = properties.id
                     LEFT JOIN images ON plantsproperties.id = images.plants_property_id
                     WHERE properties.name = 'planta'";

        $countStmt = $conn->prepare($countSql);
        if (!$countStmt) {
            return ["message" => "Erro na preparação da consulta de contagem", "error" => $conn->error];
        }

        if (!$countStmt->execute()) {
            $error = ["message" => "Erro ao contar plantas", "error" => $countStmt->error];
            $countStmt->close();
            $conn->close();
            return $error;
        }

        $countResult = $countStmt->get_result();
        $totalPlants = 0;
        if ($row = $countResult->fetch_assoc()) {
            $totalPlants = intval($row['total']);
        }
        $totalPages = ceil($totalPlants / $limit);

        $countStmt->close();

        // Consulta para obter as plantas com limite e offset
        $sql = "SELECT 
                    plants.id,
                    plants.name, 
                    plants.ecology AS description, 
                    images.imagem AS image_blob
                FROM plants
                LEFT JOIN plantsproperties ON plants.id = plantsproperties.plant_id
                LEFT JOIN properties ON plantsproperties.property_id = properties.id
                LEFT JOIN images ON plantsproperties.id = images.plants_property_id
                WHERE properties.name = 'planta'
                GROUP BY plants.id
                ORDER BY images.sort_order ASC, plants.id ASC
                LIMIT ? OFFSET ?"; // Adiciona ordenação

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $conn->close();
            return ["message" => "Erro na preparação da consulta padrão", "error" => $conn->error];
        }

        $stmt->bind_param("ii", $limit, $offset);

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

            $stmt->close();
            $conn->close();

            return [
                "plants" => $plants,
                "totalPlants" => $totalPlants,
                "totalPages" => $totalPages,
                "currentPage" => $page
            ];
        } else {
            $error = ["message" => "Erro ao buscar plantas", "error" => $stmt->error];
            $stmt->close();
            $conn->close();
            return $error;
        }
    }

    public function get() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $plant = $this->getSinglePlant($id);
                echo json_encode($plant, JSON_UNESCAPED_UNICODE);
                return;
            }

            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

            $plantsData = $this->getPlants($limit, $page);
            echo json_encode($plantsData, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["message" => "Método não suportado"], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getOtherPlants($currentPlantId, $limit = 4) {
        $conn = getConnection();
        
        $sql = "SELECT plants.id, plants.name, images.imagem AS image_blob
                FROM plants
                LEFT JOIN plantsproperties ON plants.id = plantsproperties.plant_id
                LEFT JOIN images ON plantsproperties.id = images.plants_property_id
                WHERE plants.id != ? 
                ORDER BY images.sort_order ASC, RAND() LIMIT ?"; // Adiciona ordenação
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return ["message" => "Erro na preparação da consulta", "error" => $conn->error];
        }
    
        $stmt->bind_param("ii", $currentPlantId, $limit);
    
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $otherPlants = [];
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['image_blob'])) {
                    $row['image_blob'] = base64_encode($row['image_blob']);
                }
                $otherPlants[] = $row;
            }
    
            $stmt->close();
            $conn->close();
            return $otherPlants;
        } else {
            $error = ["message" => "Erro ao buscar outras plantas", "error" => $stmt->error];
            $stmt->close();
            $conn->close();
            return $error;
        }
    }

    public function getPlantImages($plantId, $propertyId) {
        $conn = getConnection();
        
        $sql = "SELECT images.imagem AS image_blob, images.source AS image_source, properties.name AS property_name
                FROM images
                JOIN plantsproperties ON images.plants_property_id = plantsproperties.id
                JOIN properties ON plantsproperties.property_id = properties.id
                WHERE plantsproperties.plant_id = ? AND plantsproperties.property_id = ?
                ORDER BY images.sort_order ASC"; // Adiciona ordenação
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return ["message" => "Erro na preparação da consulta", "error" => $conn->error];
        }
    
        $stmt->bind_param("ii", $plantId, $propertyId);
    
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $images = [];
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['image_blob'])) {
                    $row['image_blob'] = base64_encode($row['image_blob']);
                }
                $images[] = $row;
            }
    
            $stmt->close();
            $conn->close();
            return $images;
        } else {
            $error = ["message" => "Erro ao buscar imagens da planta", "error" => $stmt->error];
            $stmt->close();
            $conn->close();
            return $error;
        }
    }
}
?>
