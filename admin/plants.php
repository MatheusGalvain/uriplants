<?php
session_start();
include_once('includes/config.php');
require_once('includes/audit.php'); 

// Configurações de erro (apenas para desenvolvimento; remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificação de autenticação
if (strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

// Funções auxiliares para auditoria e obtenção de nomes
function log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id = null) {
    log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);
}

function get_plant_name($con, $plant_id) {
    $sql = "SELECT name FROM Plants WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $plant_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $plant_name);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return $plant_name;
    }
    return "Desconhecida";
}

function get_property_name($con, $property_id) {
    $sql = "SELECT name FROM properties WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $property_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $property_name);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return $property_name;
    }
    return "Desconhecida";
}

// Função para filtrar entrada
function filter_input_data_custom($con, $data) {
    return mysqli_real_escape_string($con, trim($data));
}

// Adicionar Planta com Propriedades e Imagens
if (isset($_POST['add_plant'])) {
    // Dados da Planta
    $name = filter_input_data_custom($con, $_POST['name']);
    $common_names = filter_input_data_custom($con, $_POST['common_names']);
    $division_id = intval($_POST['division_id']);
    $class_id = intval($_POST['class_id']);
    $order_id = intval($_POST['order_id']);
    $family_id = intval($_POST['family_id']);
    $genus_id = intval($_POST['genus_id']);
    $region_id = intval($_POST['region_id']);
    $species = filter_input_data_custom($con, $_POST['species']);
    $applications = filter_input_data_custom($con, $_POST['applications']);
    $ecology = filter_input_data_custom($con, $_POST['ecology']);

    // Propriedades e Imagens enviadas via JavaScript (JSON)
    $properties = isset($_POST['properties']) ? json_decode($_POST['properties'], true) : [];

    if (!empty($properties)) {
        mysqli_begin_transaction($con);
        try {
            // Verifica se a planta já existe
            $stmt = $con->prepare("SELECT id FROM Plants WHERE name = ?");
            if (!$stmt) {
                throw new Exception("Erro na preparação da consulta: " . $con->error);
            }
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                throw new Exception("Uma planta com esse nome já existe.");
            }
            $stmt->close();

            // Inserir nova planta
            $stmt = $con->prepare("INSERT INTO Plants (name, common_names, division_id, class_id, `order_id`, family_id, genus_id, region_id, species, applications, ecology) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Erro na preparação da inserção: " . $con->error);
            }
            $stmt->bind_param("ssiiiiissss", $name, $common_names, $division_id, $class_id, $order_id, $family_id, $genus_id, $region_id, $species, $applications, $ecology);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao adicionar planta: " . $stmt->error);
            }
            $plant_id = mysqli_insert_id($con);
            $stmt->close();

            // Auditoria para inserção de planta
            $table = 'Plants';
            $action_id = 1; // Adição
            $changed_by = $_SESSION['id'];
            $old_value = null;
            $new_value = "Planta: $name, Espécie: $species";
            log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);

            // Inserção das Propriedades e Imagens
            foreach ($properties as $property) {
                $property_id = intval($property['property_id']);
                $source = filter_input_data_custom($con, $property['source']);
                $image = isset($property['image']) ? $property['image'] : null; // Base64 string

                // Inserir na tabela PlantsProperties
                $stmt = $con->prepare("INSERT INTO PlantsProperties (plant_id, property_id) VALUES (?, ?)");
                if (!$stmt) {
                    throw new Exception("Erro na preparação da consulta PlantsProperties: " . $con->error);
                }
                $stmt->bind_param("ii", $plant_id, $property_id);
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao adicionar propriedade da planta: " . $stmt->error);
                }
                $plants_property_id = mysqli_insert_id($con);
                $stmt->close();

                // Auditoria para inserção de propriedade
                $plant_name = get_plant_name($con, $plant_id);
                $property_name = get_property_name($con, $property_id);
                $new_value = "Planta: $plant_name, Propriedade: $property_name";
                log_audit_action($con, 'PlantsProperties', 1, $changed_by, null, $new_value, $plant_id);

                // Inserir imagem, se existir
                if ($image) {
                    // Decodifica a imagem Base64
                    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
                    
                    // Insere na tabela images
                    $stmt = $con->prepare("INSERT INTO images (imagem, source, plants_property_id) VALUES (?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception("Erro na preparação da consulta de imagem: " . $con->error);
                    }
                    $stmt->bind_param("ssi", $image_data, $source, $plants_property_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Erro ao adicionar a imagem: " . $stmt->error);
                    }
                    $image_id = mysqli_insert_id($con);
                    $stmt->close();

                    // Auditoria para inserção de imagem
                    $new_value = "Imagem ID: $image_id, Fonte: $source";
                    log_audit_action($con, 'images', 1, $changed_by, null, $new_value, $plant_id);
                }
            }

            mysqli_commit($con);
            $success = "Planta e propriedades com imagens adicionadas com sucesso.";
        } catch (Exception $e) {
            mysqli_rollback($con);
            $error = $e->getMessage();
        }
    } else {
        $error = "Por favor, adicione pelo menos uma propriedade à planta.";
    }
}

// Editar Planta (ajustado de forma similar)
if (isset($_POST['edit_plant'])) {
    // Dados da Planta
    $edit_id = intval($_POST['plant_id']);
    $name = filter_input_data_custom($con, $_POST['name']);
    $common_names = filter_input_data_custom($con, $_POST['common_names']);
    $division_id = intval($_POST['division_id']);
    $class_id = intval($_POST['class_id']);
    $order_id = intval($_POST['order_id']);
    $family_id = intval($_POST['family_id']);
    $genus_id = intval($_POST['genus_id']);
    $region_id = intval($_POST['region_id']);
    $species = filter_input_data_custom($con, $_POST['species']);
    $applications = filter_input_data_custom($con, $_POST['applications']);
    $ecology = filter_input_data_custom($con, $_POST['ecology']);

    // Propriedades adicionadas via JavaScript (JSON)
    $new_properties = isset($_POST['properties']) ? json_decode($_POST['properties'], true) : [];

    if ($edit_id > 0) {
        mysqli_begin_transaction($con);
        try {
            // Verifica se a planta existe e não está deletada
            $stmt = $con->prepare("SELECT name, common_names, division_id, class_id, `order_id`, family_id, genus_id, region_id, species, applications, ecology FROM Plants WHERE id = ? AND deleted_at IS NULL");
            if (!$stmt) {
                throw new Exception("Erro na preparação da consulta: " . $con->error);
            }
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $stmt->bind_result($old_name, $old_common_names, $old_division_id, $old_class_id, $old_order_id, $old_family_id, $old_genus_id, $old_region_id, $old_species, $old_applications, $old_ecology);
            if (!$stmt->fetch()) {
                throw new Exception("Planta não encontrada ou já foi excluída.");
            }
            $stmt->close();

            // Atualizar os dados da planta
            $stmt = $con->prepare("UPDATE Plants SET name = ?, common_names = ?, division_id = ?, class_id = ?, `order_id` = ?, family_id = ?, genus_id = ?, region_id = ?, species = ?, applications = ?, ecology = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Erro na preparação da atualização: " . $con->error);
            }
            $stmt->bind_param("ssiiiiissssi", $name, $common_names, $division_id, $class_id, $order_id, $family_id, $genus_id, $region_id, $species, $applications, $ecology, $edit_id);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao atualizar planta: " . $stmt->error);
            }
            $stmt->close();

            // Auditoria para atualização de planta
            $table = 'Plants';
            $action_id = 2; // Atualização
            $changed_by = $_SESSION['id'];
            $old_value = "Planta: $old_name, Espécie: $old_species";
            $new_value = "Planta: $name, Espécie: $species";
            log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value, $edit_id);

            // Inserção das Novas Propriedades (não editar existentes)
            if (!empty($new_properties)) {
                foreach ($new_properties as $property) {
                    $property_id = intval($property['property_id']);
                    $source = filter_input_data_custom($con, $property['source']);
                    $image = isset($property['image']) ? $property['image'] : null; // Base64 string

                    // Inserir na tabela PlantsProperties
                    $stmt = $con->prepare("INSERT INTO PlantsProperties (plant_id, property_id) VALUES (?, ?)");
                    if (!$stmt) {
                        throw new Exception("Erro na preparação da consulta PlantsProperties: " . $con->error);
                    }
                    $stmt->bind_param("ii", $edit_id, $property_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Erro ao adicionar propriedade da planta: " . $stmt->error);
                    }
                    $plants_property_id = mysqli_insert_id($con);
                    $stmt->close();

                    // Auditoria para inserção de propriedade
                    $plant_name = get_plant_name($con, $edit_id);
                    $property_name = get_property_name($con, $property_id);
                    $new_value = "Planta: $plant_name, Propriedade: $property_name";
                    log_audit_action($con, 'PlantsProperties', 1, $changed_by, null, $new_value, $edit_id);

                    // Inserir imagem, se existir
                    if ($image) {
                        // Decodifica a imagem Base64
                        $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
                        
                        // Insere na tabela images
                        $stmt = $con->prepare("INSERT INTO images (imagem, source, plants_property_id) VALUES (?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception("Erro na preparação da consulta de imagem: " . $con->error);
                        }
                        $stmt->bind_param("ssi", $image_data, $source, $plants_property_id);
                        if (!$stmt->execute()) {
                            throw new Exception("Erro ao adicionar a imagem: " . $stmt->error);
                        }
                        $image_id = mysqli_insert_id($con);
                        $stmt->close();

                        // Auditoria para inserção de imagem
                        $new_value = "Imagem ID: $image_id, Fonte: $source";
                        log_audit_action($con, 'images', 1, $changed_by, null, $new_value, $edit_id);
                    }
                }
            }

            mysqli_commit($con);
            $success = "Planta atualizada com sucesso.";
        } catch (Exception $e) {
            mysqli_rollback($con);
            $error = $e->getMessage();
        }
    } else {
        $error = "ID de planta inválido para edição.";
    }
}

// Excluir Planta
if (isset($_POST['delete_plant'])) {
    $delete_id = intval($_POST['id']);
    if ($delete_id > 0) {
        mysqli_begin_transaction($con);
        try {
            // Verifica se a planta existe e não está deletada
            $stmt = $con->prepare("SELECT name FROM Plants WHERE id = ? AND deleted_at IS NULL");
            if (!$stmt) {
                throw new Exception("Erro na preparação da consulta: " . $con->error);
            }
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->bind_result($plant_name);
            if (!$stmt->fetch()) {
                throw new Exception("Planta não encontrada ou já foi excluída.");
            }
            $stmt->close();

            // Atualiza o campo deleted_at
            $stmt = $con->prepare("UPDATE Plants SET deleted_at = NOW() WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Erro na preparação da atualização: " . $con->error);
            }
            $stmt->bind_param("i", $delete_id);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao excluir planta: " . $stmt->error);
            }
            $stmt->close();

            // Auditoria para exclusão de planta
            $table = 'Plants';
            $action_id = 3; // Exclusão
            $changed_by = $_SESSION['id'];
            $old_value = "Planta: $plant_name";
            $new_value = null;
            log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value, $delete_id);

            mysqli_commit($con);
            $success = "Planta excluída com sucesso.";
        } catch (Exception $e) {
            mysqli_rollback($con);
            $error = $e->getMessage();
        }
    } else {
        $error = "ID de planta inválido para exclusão.";
    }
}

// Editar Planta
if (isset($_POST['edit_plant'])) {
    // Dados da Planta
    $edit_id = intval($_POST['plant_id']);
    $name = filter_input_data_custom($con, $_POST['name']);
    $common_names = filter_input_data_custom($con, $_POST['common_names']);
    $division_id = intval($_POST['division_id']);
    $class_id = intval($_POST['class_id']);
    $order_id = intval($_POST['order_id']);
    $family_id = intval($_POST['family_id']);
    $genus_id = intval($_POST['genus_id']);
    $region_id = intval($_POST['region_id']);
    $species = filter_input_data_custom($con, $_POST['species']);
    $applications = filter_input_data_custom($con, $_POST['applications']);
    $ecology = filter_input_data_custom($con, $_POST['ecology']);

    // Descrições das propriedades (vindas dos campos de texto)
    $descriptions = [
        '2' => filter_input_data_custom($con, $_POST['trunk_description']),
        '3' => filter_input_data_custom($con, $_POST['bark_description']),
        '4' => filter_input_data_custom($con, $_POST['leaf_description']),
        '5' => filter_input_data_custom($con, $_POST['flower_description']),
        '6' => filter_input_data_custom($con, $_POST['fruit_description']),
        '7' => filter_input_data_custom($con, $_POST['seed_description']),
    ];

    // Propriedades adicionadas via JavaScript (JSON)
    $new_properties = isset($_POST['properties']) ? json_decode($_POST['properties'], true) : [];

    if ($edit_id > 0) {
        mysqli_begin_transaction($con);
        try {
            // Verifica se a planta existe e não está deletada
            $stmt = $con->prepare("SELECT name, common_names, division_id, class_id, `order_id`, family_id, genus_id, region_id, species, applications, ecology FROM Plants WHERE id = ? AND deleted_at IS NULL");
            if (!$stmt) {
                throw new Exception("Erro na preparação da consulta: " . $con->error);
            }
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $stmt->bind_result($old_name, $old_common_names, $old_division_id, $old_class_id, $old_order_id, $old_family_id, $old_genus_id, $old_region_id, $old_species, $old_applications, $old_ecology);
            if (!$stmt->fetch()) {
                throw new Exception("Planta não encontrada ou já foi excluída.");
            }
            $stmt->close();

            // Atualizar os dados da planta
            $stmt = $con->prepare("UPDATE Plants SET name = ?, common_names = ?, division_id = ?, class_id = ?, `order_id` = ?, family_id = ?, genus_id = ?, region_id = ?, species = ?, applications = ?, ecology = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Erro na preparação da atualização: " . $con->error);
            }
            $stmt->bind_param("ssiiiiissssi", $name, $common_names, $division_id, $class_id, $order_id, $family_id, $genus_id, $region_id, $species, $applications, $ecology, $edit_id);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao atualizar planta: " . $stmt->error);
            }
            $stmt->close();

            // Auditoria para atualização de planta
            $table = 'Plants';
            $action_id = 2; // Atualização
            $changed_by = $_SESSION['id'];
            $old_value = "Planta: $old_name, Espécie: $old_species";
            $new_value = "Planta: $name, Espécie: $species";
            log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value, $edit_id);

            // Inserção das Novas Propriedades (não editar existentes)
            if (!empty($new_properties)) {
                foreach ($new_properties as $property) {
                    $property_id = intval($property['property_id']);
                    $source = filter_input_data_custom($con, $property['source']);
                    $image = isset($property['image']) ? $property['image'] : null; // Base64 string

                    // Obter a descrição correspondente à propriedade
                    $description = isset($descriptions[$property_id]) ? $descriptions[$property_id] : '';

                    // Inserir na tabela PlantsProperties
                    $stmt = $con->prepare("INSERT INTO PlantsProperties (plant_id, property_id, description) VALUES (?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception("Erro na preparação da consulta PlantsProperties: " . $con->error);
                    }
                    $stmt->bind_param("iis", $edit_id, $property_id, $description);
                    if (!$stmt->execute()) {
                        throw new Exception("Erro ao adicionar propriedade da planta: " . $stmt->error);
                    }
                    $plants_property_id = mysqli_insert_id($con);
                    $stmt->close();

                    // Auditoria para inserção de propriedade
                    $plant_name = get_plant_name($con, $edit_id);
                    $property_name = get_property_name($con, $property_id);
                    $new_value = "Planta: $plant_name, Propriedade: $property_name, Descrição: $description";
                    log_audit_action($con, 'PlantsProperties', 1, $changed_by, null, $new_value, $edit_id);

                    // Inserir imagem, se existir
                    if ($image) {
                        // Decodifica a imagem Base64
                        $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
                        
                        // Insere na tabela images
                        $stmt = $con->prepare("INSERT INTO images (imagem, source, plants_property_id) VALUES (?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception("Erro na preparação da consulta de imagem: " . $con->error);
                        }
                        $stmt->bind_param("ssi", $image_data, $source, $plants_property_id);
                        if (!$stmt->execute()) {
                            throw new Exception("Erro ao adicionar a imagem: " . $stmt->error);
                        }
                        $image_id = mysqli_insert_id($con);
                        $stmt->close();

                        // Auditoria para inserção de imagem
                        $new_value = "Imagem ID: $image_id, Fonte: $source";
                        log_audit_action($con, 'images', 1, $changed_by, null, $new_value, $edit_id);
                    }
                }
            }

            mysqli_commit($con);
            $success = "Planta atualizada com sucesso.";
        } catch (Exception $e) {
            mysqli_rollback($con);
            $error = $e->getMessage();
        }
    } else {
        $error = "ID de planta inválido para edição.";
    }
}

// Configuração de Paginação
$items_per_page = 10; // Número de plantas por página
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

// Captura do termo de busca
$search = isset($_GET['search']) ? filter_input_data_custom($con, $_GET['search']) : '';
$searchQuery = $search ? "AND (p.name LIKE '%$search%' OR p.common_names LIKE '%$search%')" : "";

// Contagem total de plantas para paginação
$count_sql = "
    SELECT COUNT(*) as total
    FROM Plants p
    LEFT JOIN Divisions d ON p.division_id = d.id
    LEFT JOIN Classes cl ON p.class_id = cl.id
    LEFT JOIN Orders o ON p.order_id = o.id
    LEFT JOIN Families fa ON p.family_id = fa.id
    LEFT JOIN Genus ge ON p.genus_id = ge.id
    LEFT JOIN RegionMap re ON p.region_id = re.id
    WHERE p.deleted_at IS NULL $searchQuery
";
$count_result = mysqli_query($con, $count_sql);
if ($count_result) {
    $count_row = mysqli_fetch_assoc($count_result);
    $total_items = intval($count_row['total']);
} else {
    $total_items = 0;
    $error = "Erro na contagem de plantas: " . mysqli_error($con);
}

$total_pages = ceil($total_items / $items_per_page);
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}
$offset = ($page - 1) * $items_per_page;

// Busca e exibição de plantas com paginação
$plantsQuery = mysqli_query($con, "
    SELECT p.*, d.name as division_name, cl.name as class_name, o.name as order_name, fa.name as family_name, ge.name as genus_name, re.name as region_name
    FROM Plants p
    LEFT JOIN Divisions d ON p.division_id = d.id
    LEFT JOIN Classes cl ON p.class_id = cl.id
    LEFT JOIN Orders o ON p.order_id = o.id
    LEFT JOIN Families fa ON p.family_id = fa.id
    LEFT JOIN Genus ge ON p.genus_id = ge.id
    LEFT JOIN RegionMap re ON p.region_id = re.id
    WHERE p.deleted_at IS NULL $searchQuery
    ORDER BY p.created_at DESC
    LIMIT $items_per_page OFFSET $offset
");
if (!$plantsQuery) {
    $error = "Erro na consulta de plantas: " . mysqli_error($con);
}

// Dados para selects
$divisions = mysqli_fetch_all(mysqli_query($con, "SELECT id, name FROM Divisions WHERE deleted_at IS NULL ORDER BY name ASC"), MYSQLI_ASSOC);
$classes = mysqli_fetch_all(mysqli_query($con, "SELECT id, name FROM Classes WHERE deleted_at IS NULL ORDER BY name ASC"), MYSQLI_ASSOC);
$orders = mysqli_fetch_all(mysqli_query($con, "SELECT id, name FROM Orders WHERE deleted_at IS NULL ORDER BY name ASC"), MYSQLI_ASSOC);
$families = mysqli_fetch_all(mysqli_query($con, "SELECT id, name FROM Families WHERE deleted_at IS NULL ORDER BY name ASC"), MYSQLI_ASSOC);
$genus = mysqli_fetch_all(mysqli_query($con, "SELECT id, name FROM Genus WHERE deleted_at IS NULL ORDER BY name ASC"), MYSQLI_ASSOC);
$regionMap = mysqli_fetch_all(mysqli_query($con, "SELECT id, name FROM RegionMap WHERE deleted_at IS NULL ORDER BY name ASC"), MYSQLI_ASSOC);

// Verificar se está no modo de edição
$edit_mode = false;
$edit_plant = [];
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    if ($edit_id > 0) {
        $stmt = $con->prepare("
            SELECT p.*, d.name as division_name, cl.name as class_name, o.name as order_name, 
                   fa.name as family_name, ge.name as genus_name, re.name as region_name
            FROM Plants p
            LEFT JOIN Divisions d ON p.division_id = d.id
            LEFT JOIN Classes cl ON p.class_id = cl.id
            LEFT JOIN Orders o ON p.order_id = o.id
            LEFT JOIN Families fa ON p.family_id = fa.id
            LEFT JOIN Genus ge ON p.genus_id = ge.id
            LEFT JOIN RegionMap re ON p.region_id = re.id
            WHERE p.id = ? AND p.deleted_at IS NULL
        ");
        if ($stmt) {
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $edit_plant = $result->fetch_assoc();
                $edit_mode = true;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Gerenciar Plantas</title>
    <style>
        /* Estilos para a lista de imagens */
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-item {
            position: relative;
            width: 150px;
            height: 150px;
        }

        .image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
        }

        /* Estilos para paginação */
        .pagination {
            justify-content: center;
        }

        .pagination a, .pagination span {
            margin: 0 2px;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Plantas</h1>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <!-- Botão para abrir o formulário de Adição de Planta -->
                    <?php if (!$edit_mode) { ?>
                        <button id="toggleForm" class="btn btn-primary mb-4">Nova Planta</button>
                    <?php } ?>

                    <!-- Formulário de Adição/Editação de Planta -->
                    <div id="plant-form" class="card mb-4" style="<?php echo ($edit_mode || false) ? 'display: block;' : 'display: none;'; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $edit_mode ? 'Editar Planta' : 'Nova Planta'; ?></h5>
                            <form method="POST" action="" id="addPlantForm">
                                <?php if ($edit_mode) { ?>
                                    <input type="hidden" name="plant_id" value="<?php echo htmlspecialchars($edit_plant['id']); ?>">
                                <?php } ?>

                                <!-- Imagens da Planta -->
                                <div class="mb-3">
                                    <label class="form-label">Imagens da Planta</label>
                                    <!-- Descrição da Planta -->
                                    
                                    <button type="button" class="btn btn-primary btn-sm add-image-button" data-property-id="1">Adicionar Imagem</button>
                                    <div class="image-preview mt-2" data-property-id="1">
                                        <!-- Imagens da Planta serão exibidas aqui -->
                                    </div>
                                </div>

                                <!-- Seção de Propriedades -->

                                <div class="mb-3">
                                    <label for="name" class="form-label">*Nome Científico</label>
                                    <input type="text" class="form-control" id="name" name="name" required value="<?php echo $edit_mode ? htmlspecialchars($edit_plant['name']) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="common_names" class="form-label">Nomes Comuns</label>
                                    <textarea class="form-control" id="common_names" name="common_names"><?php echo $edit_mode ? htmlspecialchars($edit_plant['common_names']) : ''; ?></textarea>
                                </div>

                                <!-- Seleção da Divisão -->
                                <div class="mb-3">
                                    <label for="division_id" class="form-label">*Divisão</label>
                                    <select class="form-select" id="division_id" name="division_id" required>
                                        <option value="">Selecione a divisão</option>
                                        <?php foreach ($divisions as $division) { ?>
                                            <option value="<?php echo htmlspecialchars($division['id']); ?>" <?php echo ($edit_mode && $edit_plant['division_id'] == $division['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($division['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Classe -->
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">*Classe</label>
                                    <select class="form-select" id="class_id" name="class_id" required>
                                        <option value="">Selecione a classe</option>
                                        <?php foreach ($classes as $class) { ?>
                                            <option value="<?php echo htmlspecialchars($class['id']); ?>" <?php echo ($edit_mode && $edit_plant['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Ordem -->
                                <div class="mb-3">
                                    <label for="order_id" class="form-label">*Ordem</label>
                                    <select class="form-select" id="order_id" name="order_id" required>
                                        <option value="">Selecione a ordem</option>
                                        <?php foreach ($orders as $order) { ?>
                                            <option value="<?php echo htmlspecialchars($order['id']); ?>" <?php echo ($edit_mode && $edit_plant['order_id'] == $order['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($order['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Família -->
                                <div class="mb-3">
                                    <label for="family_id" class="form-label">*Família</label>
                                    <select class="form-select" id="family_id" name="family_id" required>
                                        <option value="">Selecione a família</option>
                                        <?php foreach ($families as $family) { ?>
                                            <option value="<?php echo htmlspecialchars($family['id']); ?>" <?php echo ($edit_mode && $edit_plant['family_id'] == $family['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($family['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção do Gênero -->
                                <div class="mb-3">
                                    <label for="genus_id" class="form-label">Gênero</label>
                                    <select class="form-select" id="genus_id" name="genus_id">
                                        <option value="">Selecione o gênero</option>
                                        <?php foreach ($genus as $genusItem) { ?>
                                            <option value="<?php echo htmlspecialchars($genusItem['id']); ?>" <?php echo ($edit_mode && $edit_plant['genus_id'] == $genusItem['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($genusItem['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Região -->
                                <div class="mb-3">
                                    <label for="region_id" class="form-label">Região</label>
                                    <select class="form-select" id="region_id" name="region_id">
                                        <option value="">Selecione a região</option>
                                        <?php foreach ($regionMap as $region) { ?>
                                            <option value="<?php echo htmlspecialchars($region['id']); ?>" <?php echo ($edit_mode && $edit_plant['region_id'] == $region['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($region['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Outros campos opcionais -->
                                <div class="mb-3">
                                    <label for="species" class="form-label">Espécie</label>
                                    <input type="text" class="form-control" id="species" name="species" value="<?php echo $edit_mode ? htmlspecialchars($edit_plant['species']) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="applications" class="form-label">Aplicações</label>
                                    <textarea class="form-control" id="applications" name="applications"><?php echo $edit_mode ? htmlspecialchars($edit_plant['applications']) : ''; ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="ecology" class="form-label">Ecologia</label>
                                    <textarea class="form-control" id="ecology" name="ecology"><?php echo $edit_mode ? htmlspecialchars($edit_plant['ecology']) : ''; ?></textarea>
                                </div>

                                <!-- Seção para Tronco -->
                                <div class="mb-3">
                                    <label for="trunk" class="form-label">Tronco</label>
                                    <textarea class="form-control" id="trunk" name="trunk_description"><?php echo $edit_mode ? htmlspecialchars($edit_plant['trunk_description']) : ''; ?></textarea>
                                    <button type="button" class="btn btn-primary btn-sm add-image-button" data-property-id="2">Adicionar Imagem</button>
                                    <div class="image-preview mt-2" data-property-id="2">
                                        <!-- Imagens do Tronco serão exibidas aqui -->
                                    </div>
                                </div>

                                <!-- Seção para Casca -->
                                <div class="mb-3">
                                    <label for="bark" class="form-label">Casca</label>
                                    <textarea class="form-control" id="bark" name="bark_description"><?php echo $edit_mode ? htmlspecialchars($edit_plant['bark_description']) : ''; ?></textarea>
                                    <button type="button" class="btn btn-primary btn-sm add-image-button" data-property-id="3">Adicionar Imagem</button>
                                    <div class="image-preview mt-2" data-property-id="3">
                                        <!-- Imagens da Casca serão exibidas aqui -->
                                    </div>
                                </div>

                                <!-- Seção para Folha -->
                                <div class="mb-3">
                                    <label for="leaf" class="form-label">Folha</label>
                                    <textarea class="form-control" id="leaf" name="leaf_description"><?php echo $edit_mode ? htmlspecialchars($edit_plant['leaf_description']) : ''; ?></textarea>
                                    <button type="button" class="btn btn-primary btn-sm add-image-button" data-property-id="4">Adicionar Imagem</button>
                                    <div class="image-preview mt-2" data-property-id="4">
                                        <!-- Imagens da Folha serão exibidas aqui -->
                                    </div>
                                </div>

                                <!-- Seção para Flor -->
                                <div class="mb-3">
                                    <label for="flower" class="form-label">Flor</label>
                                    <textarea class="form-control" id="flower" name="flower_description"><?php echo $edit_mode ? htmlspecialchars($edit_plant['flower_description']) : ''; ?></textarea>
                                    <button type="button" class="btn btn-primary btn-sm add-image-button" data-property-id="5">Adicionar Imagem</button>
                                    <div class="image-preview mt-2" data-property-id="5">
                                        <!-- Imagens da Flor serão exibidas aqui -->
                                    </div>
                                </div>

                                <!-- Seção para Fruta -->
                                <div class="mb-3">
                                    <label for="fruit" class="form-label">Fruta</label>
                                    <textarea class="form-control" id="fruit" name="fruit_description"><?php echo $edit_mode ? htmlspecialchars($edit_plant['fruit_description']) : ''; ?></textarea>
                                    <button type="button" class="btn btn-primary btn-sm add-image-button" data-property-id="6">Adicionar Imagem</button>
                                    <div class="image-preview mt-2" data-property-id="6">
                                        <!-- Imagens da Fruta serão exibidas aqui -->
                                    </div>
                                </div>

                                <!-- Seção para Semente -->
                                <div class="mb-3">
                                    <label for="seed" class="form-label">Semente</label>
                                    <textarea class="form-control" id="seed" name="seed_description"><?php echo $edit_mode ? htmlspecialchars($edit_plant['seed_description']) : ''; ?></textarea>
                                    <button type="button" class="btn btn-primary btn-sm add-image-button" data-property-id="7">Adicionar Imagem</button>
                                    <div class="image-preview mt-2" data-property-id="7">
                                        <!-- Imagens da Semente serão exibidas aqui -->
                                    </div>
                                </div>

                                <?php if ($edit_mode) { ?>
                                    <button type="submit" name="edit_plant" class="btn btn-success">Atualizar Planta</button>
                                    <a href="plants.php" class="btn btn-secondary">Cancelar</a>
                                <?php } else { ?>
                                    <button type="submit" name="add_plant" class="btn btn-primary">Adicionar Planta</button>
                                    <button type="button" id="cancelAddPlant" class="btn btn-secondary">Cancelar</button>
                                <?php } ?>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de Plantas Cadastradas -->
                    <div id="plant-list" class="card mb-4" style="<?php echo ($edit_mode) ? 'display: none;' : 'display: block;'; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Plantas Cadastradas</h5>
                                <form method="GET" action="" class="d-flex">
                                    <input type="text" class="form-control me-2" name="search" placeholder="Buscar plantas" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" type="submit">Buscar</button>
                                    <?php if ($search) { ?>
                                        <a href="plants.php" class="btn btn-secondary ms-2">Remover Filtro</a>
                                    <?php } ?>
                                </form>
                            </div>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nome da Planta</th>
                                        <th>Divisão</th>
                                        <th>Classe</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $plantCount = 0;
                                        while ($row = mysqli_fetch_array($plantsQuery)) { 
                                            $plantCount++;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['division_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                                            <td>
                                            <a href="?edit=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-success btn-sm">Editar</a>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?php echo htmlspecialchars($row['id']); ?>">Excluir</button>
                                        </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if ($plantCount === 0) { ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Nenhuma planta encontrada.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <!-- Paginação -->
                            <?php if ($total_pages > 1) { ?>
                                <nav>
                                    <ul class="pagination">
                                        <!-- Página Anterior -->
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" aria-label="Anterior">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>

                                        <!-- Páginas -->
                                        <?php
                                        // Definir intervalo de páginas a exibir
                                        $range = 2;
                                        for ($i = max(1, $page - $range); $i <= min($page + $range, $total_pages); $i++) {
                                            if ($i == $page) {
                                                echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                                            } else {
                                                echo '<li class="page-item"><a class="page-link" href="?'. http_build_query(array_merge($_GET, ['page' => $i])) .'">' . $i . '</a></li>';
                                            }
                                        }
                                        ?>

                                        <!-- Próxima Página -->
                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" aria-label="Próximo">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Modal para Adicionar Imagem -->
    <div class="modal fade" id="addPropertyModal" tabindex="-1" aria-labelledby="addPropertyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="propertyForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Adicionar Imagem</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Removido o campo de seleção de propriedade -->
                        <div class="mb-3">
                            <label for="source" class="form-label">Fonte da Imagem</label>
                            <input type="text" class="form-control" id="source" required>
                        </div>
                        <div class="mb-3">
                            <label for="imageFile" class="form-label">Imagem</label>
                            <input type="file" class="form-control" id="imageFile" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar Imagem</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        Você realmente deseja excluir esta planta?
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_plant" class="btn btn-danger">Excluir</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts Necessários -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Inclusão do Sortable.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var addPlantFormElement = document.getElementById('addPlantForm');
        var submitButton = addPlantFormElement.querySelector('button[type="submit"]');

        // Mapeamento de property_id para nomes
        var propertyNames = <?php
        $propertiesArray = [];
        $propertiesResult = mysqli_query($con, "SELECT id, name FROM properties WHERE id != 1");
        while ($property = mysqli_fetch_assoc($propertiesResult)) {
            $propertiesArray[$property['id']] = $property['name'];
        }
        echo json_encode($propertiesArray);
        ?>;

        // Variável para armazenar o property_id atual
        var currentPropertyId = null;

        // Gerenciar Adição de Imagens
        var addPropertyModal = new bootstrap.Modal(document.getElementById('addPropertyModal'));
        var propertyForm = document.getElementById('propertyForm');

        // Array para armazenar propriedades adicionadas
        var propertiesArray = [];
        var propertyCounter = 0; // Contador para IDs únicos

        function updateSubmitButtonState() {
            if (propertiesArray.length === 0) {
                submitButton.disabled = true;
            } else {
                submitButton.disabled = false;
            }
        }

        updateSubmitButtonState();

        // Evento de clique nos botões "Adicionar Imagem"
        document.querySelectorAll('.add-image-button').forEach(function(button) {
            button.addEventListener('click', function() {
                currentPropertyId = this.getAttribute('data-property-id');
                // Limpa o formulário da modal
                propertyForm.reset();
                addPropertyModal.show();
            });
        });

        propertyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var source = document.getElementById('source').value.trim();
            var imageInput = document.getElementById('imageFile');
            var file = imageInput.files[0];

            if (currentPropertyId && source && file) {
                // Verifica o tipo de arquivo
                var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Tipo de imagem inválido. Apenas JPEG, PNG e GIF são permitidos.');
                    return;
                }

                // Cria um objeto URL para exibir a imagem
                var reader = new FileReader();
                reader.onload = function(event) {
                    var imageUrl = event.target.result;

                    // Adiciona a propriedade ao array com um ID único
                    propertiesArray.push({
                        id: propertyCounter++, // ID único
                        property_id: currentPropertyId, // Usa o property_id atual
                        source: source,
                        image: imageUrl // Armazena a URL Base64
                    });

                    // Atualiza a visualização das propriedades
                    updatePropertiesList();

                    // Fecha a modal
                    addPropertyModal.hide();
                };
                reader.readAsDataURL(file);
            } else {
                alert('Por favor, preencha todos os campos.');
            }
            updateSubmitButtonState();
        });

        // Atualiza a visualização das propriedades
        function updatePropertiesList() {
            // Limpa todas as áreas de visualização de imagens
            document.querySelectorAll('.image-preview').forEach(function(preview) {
                preview.innerHTML = '';
            });

            propertiesArray.forEach(function(property, index) {
                var propertyId = property.property_id;

                // Encontra a área de visualização correspondente
                var previewArea = document.querySelector('.image-preview[data-property-id="' + propertyId + '"]');

                if (previewArea) {
                    var div = document.createElement('div');
                    div.classList.add('image-item');
                    div.setAttribute('data-id', property.id); // Atribui o ID único

                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.classList.add('remove-image');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.onclick = function() {
                        propertiesArray.splice(index, 1);
                        updatePropertiesList();
                    };

                    var propertyName = propertyNames[property.property_id] || 'Propriedade Desconhecida';
                    var propertyInfo = document.createElement('div');
                    propertyInfo.innerHTML = `<small>Fonte: ${property.source}</small>`;

                    var img = document.createElement('img');
                    img.src = property.image;
                    img.alt = 'Imagem da Propriedade';

                    div.appendChild(removeBtn);
                    div.appendChild(img);
                    div.appendChild(propertyInfo);
                    previewArea.appendChild(div);
                }
            });
            updateSubmitButtonState();
        }

        // Manipulação do Formulário de Adição/Atualização de Planta para incluir propriedades
        addPlantFormElement.addEventListener('submit', function(e) {
            // Adiciona as propriedades ao campo oculto como JSON
            var propertiesInput = document.createElement('input');
            propertiesInput.type = 'hidden';
            propertiesInput.name = 'properties';
            propertiesInput.value = JSON.stringify(propertiesArray);
            addPlantFormElement.appendChild(propertiesInput);

            // Envia o formulário normalmente
        });

        // Mostrar/Esconder Formulário de Adição de Planta
        var toggleFormButton = document.getElementById('toggleForm');
        var plantForm = document.getElementById('plant-form');
        var plantList = document.getElementById('plant-list');
        var cancelAddPlant = document.getElementById('cancelAddPlant');

        if (toggleFormButton) {
            toggleFormButton.addEventListener('click', function() {
                plantForm.style.display = 'block';
                plantList.style.display = 'none';
                toggleFormButton.style.display = 'none';
            });
        }

        if (cancelAddPlant) {
            cancelAddPlant.addEventListener('click', function() {
                plantForm.style.display = 'none';
                plantList.style.display = 'block';
                toggleFormButton.style.display = 'block';
            });
        }

        // Modal de Confirmação de Exclusão
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var deleteIdInput = confirmDeleteModal.querySelector('#deleteId');
            deleteIdInput.value = id;
        });
    });
    </script>
</body>

</html>
