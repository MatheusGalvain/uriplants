<?php
require_once __DIR__ . '/../config/database.php';

class PlantController
{

    /**
     * Obtém uma única planta pelo ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getSinglePlant($id)
    {
        if ($id <= 0) {
            return ["message" => "Parâmetro 'id' inválido"];
        }

        $conn = getConnection();

        $sql = "SELECT Plants.id, Plants.name, Plants.common_names, Plants.bark_description, Plants.trunk_description, 
                Plants.leaf_description, Plants.flower_description, Plants.fruit_description, Plants.seed_description, 
                Plants.biology, Plants.species, Plants.applications, Plants.ecology, Plants.created_at, 
                Plants.deleted_at, Families.name AS family_name, Orders.name AS order_name,
                Divisions.name AS division_name, Classes.name AS class_name, Genus.name AS genus_name,
                RegionMap.id AS region_map_id, RegionMap.source AS region_map_source, 
                RegionMap.description AS region_map_description, RegionMap.imagem AS region_map_image,
                RegionMap.name AS region_map_name,
                Properties.name AS property_name,
                Images.id AS image_id, Images.imagem AS image_blob, Images.source AS image_source
                FROM Plants
                LEFT JOIN Families ON Plants.family_id = Families.id
                LEFT JOIN Orders ON Plants.order_id = Orders.id
                LEFT JOIN Divisions ON Plants.division_id = Divisions.id
                LEFT JOIN Classes ON Plants.class_id = Classes.id
                LEFT JOIN Genus ON Plants.genus_id = Genus.id
                LEFT JOIN RegionMap ON Plants.region_id = RegionMap.id
                LEFT JOIN PlantsProperties ON Plants.id = PlantsProperties.plant_id
                LEFT JOIN Properties ON PlantsProperties.property_id = Properties.id
                LEFT JOIN Images ON PlantsProperties.id = Images.plants_property_id
                WHERE Plants.id = ?
                ORDER BY Images.sort_order ASC";

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
    public function getPlants($limit = 10, $page = 1, $query = '')
    {
        if ($limit <= 0) $limit = 10;
        if ($page <= 0) $page = 1;

        $offset = ($page - 1) * $limit;

        $conn = getConnection();

        // Consulta com query
        $countSql = "SELECT COUNT(DISTINCT Plants.id) as total FROM Plants
                     LEFT JOIN PlantsProperties ON Plants.id = PlantsProperties.plant_id
                     LEFT JOIN Properties ON PlantsProperties.property_id = Properties.id
                     LEFT JOIN Images ON PlantsProperties.id = Images.plants_property_id
                     WHERE Plants.deleted_at IS NULL";

        if (!empty($query)) {
            $countSql .= " AND Plants.name LIKE ?";
            $query = "%" . $query . "%";
        }

        $countStmt = $conn->prepare($countSql);
        if (!$countStmt) {
            return ["message" => "Erro na preparação da consulta de contagem", "error" => $conn->error];
        }

        if (!empty($query)) {
            $countStmt->bind_param("s", $query);
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
        $sql = "SELECT 
                    Plants.id,
                    Plants.name,
                    Plants.common_names, 
                    Plants.ecology AS description, 
                    Images.imagem AS image_blob
                FROM Plants
                LEFT JOIN PlantsProperties ON Plants.id = PlantsProperties.plant_id
                LEFT JOIN Properties ON PlantsProperties.property_id = Properties.id
                LEFT JOIN Images ON PlantsProperties.id = Images.plants_property_id
                WHERE Plants.deleted_at IS NULL";

        if (!empty($query)) {
            $sql .= " AND Plants.name LIKE ?";
        }

        $sql .= " GROUP BY Plants.id
                  ORDER BY Images.sort_order ASC, Plants.id ASC
                  LIMIT ? OFFSET ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $conn->close();
            return ["message" => "Erro na preparação da consulta padrão", "error" => $conn->error];
        }

        if (!empty($query)) {
            $stmt->bind_param("sii", $query, $limit, $offset);
        } else {
            $stmt->bind_param("ii", $limit, $offset);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $plants = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
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

    public function get()
    {
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

            $plantsData = $this->getPlants($limit, $page, isset($_GET['query']) ? $_GET['query'] : '');

            echo json_encode($plantsData, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["message" => "Método não suportado"], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getOtherPlants($currentPlantId, $limit = 4)
    {
        $conn = getConnection();

        $sql = "SELECT Plants.id, Plants.name, Images.imagem AS image_blob
                FROM Plants
                LEFT JOIN PlantsProperties ON Plants.id = PlantsProperties.plant_id
                LEFT JOIN Properties ON PlantsProperties.property_id = Properties.id
                LEFT JOIN Images ON PlantsProperties.id = Images.plants_property_id
                WHERE Plants.id != ? AND Properties.id = 1
                ORDER BY Images.sort_order ASC, RAND() LIMIT ?";

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

    public function getPlantImages($plantId, $propertyId)
    {
        $conn = getConnection();

        $sql = "SELECT Images.imagem AS image_blob, Images.source AS image_source, Properties.name AS property_name
                FROM Images
                JOIN PlantsProperties ON Images.plants_property_id = PlantsProperties.id
                JOIN Properties ON PlantsProperties.property_id = Properties.id
                WHERE PlantsProperties.plant_id = ? AND PlantsProperties.property_id = ?
                ORDER BY Images.sort_order ASC";

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

    public function getUsefullLinks($plantId)
    {
        $conn = getConnection();

        $sql = "SELECT name, link
                FROM UsefulLinks
                WHERE plant_id = ?
                AND deleted_at IS NULL;";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return ["message" => "Erro na preparação da consulta", "error" => $conn->error];
        }

        $stmt->bind_param("i", $plantId);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $u_links = [];
            while ($row = $result->fetch_assoc()) {
                $u_links[] = $row;
            }

            $stmt->close();
            $conn->close();
            return $u_links;
        } else {
            $error = ["message" => "Erro ao buscar imagens da planta", "error" => $stmt->error];
            $stmt->close();
            $conn->close();
            return $error;
        }
    }
}
