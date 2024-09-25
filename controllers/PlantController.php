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

            $requiredFields = ['division_id', 'class_id', 'order_id', 'family_id', 'genus_id', 'species', 'biological_form', 'region_id', 'applications'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    echo json_encode(["message" => "Todos os campos são obrigatórios"]);
                    return;
                }
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
                echo json_encode(["message" => "Planta inserida com sucesso", "plant_id" => $stmt->insert_id]);
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

            $page = isset($_GET['page']) ? intval($_GET['page']) : null;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;

            if ($page !== null && $limit !== null) {
                if ($page <= 0) $page = 1;
                if ($limit <= 0) $limit = 10;

                $offset = ($page - 1) * $limit;

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
                    LIMIT ? OFFSET ?";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    echo json_encode(["message" => "Erro na preparação da consulta de paginação", "error" => $conn->error]);
                    return;
                }

                $stmt->bind_param("ii", $limit, $offset);

                if ($stmt->execute()) {
                    $result = $stmt->get_result();
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

                    $countSql = "SELECT COUNT(*) as total FROM plants";
                    $countResult = $conn->query($countSql);
                    $total = $countResult->fetch_assoc()['total'];
                    $totalPages = ceil($total / $limit);

                    echo json_encode([
                        "page" => $page,
                        "limit" => $limit,
                        "total_pages" => $totalPages,
                        "total_items" => $total,
                        "plants" => $plants
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    echo json_encode(["message" => "Erro ao buscar plantas com paginação", "error" => $stmt->error]);
                }

                $stmt->close();
                $conn->close();
                return;
            }

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
                    LIMIT 1";

            $sql = "SELECT 
                        plants.name, 
                        plants.ecology AS description,
                        (
                            SELECT images.imagem 
                            FROM images
                            JOIN plantsproperties ON images.plants_property_id = plantsproperties.id
                            JOIN properties ON plantsproperties.property_id = properties.id
                            WHERE plantsproperties.plant_id = plants.id AND properties.name = 'planta'
                            LIMIT 1
                        ) AS image_blob
                    FROM plants";

            $result = $conn->query($sql);
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

            $conn->close();
        }
    }
}
?>
