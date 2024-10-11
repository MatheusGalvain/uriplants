<?php
session_start();
include_once('includes/config.php');
require_once('includes/audit.php'); 

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
    // Atualize a consulta SQL para incluir name e name_ref
    $sql = "SELECT name, name_ref FROM properties WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $property_id);
        mysqli_stmt_execute($stmt);

        // Capture tanto name quanto name_ref
        mysqli_stmt_bind_result($stmt, $property_name, $property_name_ref);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Retorne um array com os dois valores
        return [
            'name' => $property_name,
            'name_ref' => $property_name_ref
        ];
    }
    return "Desconhecida";
}

// Função para filtrar entrada
function filter_input_data_custom($con, $data) {
    return mysqli_real_escape_string($con, trim($data));
}

// Pega o url para gerar o qrcode dinamico
function get_qrcode_url($con) {
    $sql = "SELECT url FROM qrcode_url LIMIT 1";
    
    if ($stmt = $con->prepare($sql)) {
        // Executa a consulta
        if ($stmt->execute()) {
            // Obtém o resultado
            $stmt->bind_result($url);
            if ($stmt->fetch()) {
                $stmt->close();
                return $url;
            } else {
                // Nenhum registro encontrado
                $stmt->close();
                return false;
            }
        } else {
            // Erro na execução da consulta
            error_log("Erro na execução da consulta: " . $stmt->error);
            $stmt->close();
            return false;
        }
    } else {
        // Erro na preparação da consulta
        error_log("Erro na preparação da consulta: " . $con->error);
        return false;
    }
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
    $biology = filter_input_data_custom($con, $_POST['biology']);

    // Novos campos de descrição
    $bark_description = filter_input_data_custom($con, $_POST['bark_description'] ?? '');
    $trunk_description = filter_input_data_custom($con, $_POST['trunk_description'] ?? '');
    $leaf_description = filter_input_data_custom($con, $_POST['leaf_description'] ?? '');
    $flower_description = filter_input_data_custom($con, $_POST['flower_description'] ?? '');
    $fruit_description = filter_input_data_custom($con, $_POST['fruit_description'] ?? '');
    $seed_description = filter_input_data_custom($con, $_POST['seed_description'] ?? '');

    // Coleta de dados das propriedades
    $properties_data = isset($_POST['properties_data']) ? json_decode($_POST['properties_data'], true) : [];

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
        $stmt = $con->prepare("INSERT INTO Plants (name, common_names, division_id, class_id, `order_id`, family_id, genus_id, region_id, species, applications, ecology, biology, bark_description, trunk_description, leaf_description, flower_description, fruit_description, seed_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Erro na preparação da inserção: " . $con->error);
        }
        $stmt->bind_param("ssiiiiiissssssssss", $name, $common_names, $division_id, $class_id, $order_id, $family_id, $genus_id, $region_id, $species, $applications, $ecology, $biology, $bark_description, $trunk_description, $leaf_description, $flower_description, $fruit_description, $seed_description);
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

        // Processar propriedades e imagens
        foreach ($properties_data as $property_id => $images) {
            $property_id = intval($property_id);

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
            $property_data = get_property_name($con, $property_id);
            $property_name = $property_data['name'];
            $new_value = "Planta: $plant_name, Propriedade: $property_name";
            log_audit_action($con, 'PlantsProperties', 1, $changed_by, null, $new_value, $plant_id);

            // Inserir imagens para esta propriedade
            if (!empty($images)) {
                foreach ($images as $imageData) {
                    $source = filter_input_data_custom($con, $imageData['source']);
                    $image = $imageData['image']; // Base64 string

                    // Decodifica a imagem Base64
                    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));

                    // Determina o próximo sort_order para esta propriedade
                    $stmt_order = $con->prepare("SELECT MAX(sort_order) FROM images WHERE plants_property_id = ?");
                    if ($stmt_order) {
                        $stmt_order->bind_param("i", $plants_property_id);
                        $stmt_order->execute();
                        $stmt_order->bind_result($max_sort_order);
                        $stmt_order->fetch();
                        $next_sort_order = $max_sort_order !== null ? $max_sort_order + 1 : 1;
                        $stmt_order->close();
                    } else {
                        $next_sort_order = 1; // Fallback caso a consulta falhe
                    }

                    // Insere na tabela images com sort_order
                    $stmt = $con->prepare("INSERT INTO images (imagem, source, plants_property_id, sort_order) VALUES (?, ?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception("Erro na preparação da consulta de imagem: " . $con->error);
                    }
                    $stmt->bind_param("ssii", $image_data, $source, $plants_property_id, $next_sort_order);
                    if (!$stmt->execute()) {
                        throw new Exception("Erro ao adicionar a imagem: " . $stmt->error);
                    }
                    $image_id = mysqli_insert_id($con);
                    $stmt->close();

                    // Auditoria para inserção de imagem
                    $new_value = "Imagem ID: $image_id, Fonte: $source, Ordem: $next_sort_order";
                    log_audit_action($con, 'images', 1, $changed_by, null, $new_value, $plant_id);
                }
            }
        }
        // Processar Links Úteis
        $usefullinks_data = isset($_POST['usefullinks_data']) ? json_decode($_POST['usefullinks_data'], true) : [];

        foreach ($usefullinks_data as $link) {
            $link_name = filter_input_data_custom($con, $link['name']);
            $link_url = filter_input_data_custom($con, $link['link']);

            $stmt = $con->prepare("INSERT INTO usefullinks (name, link, plant_id) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Erro na preparação da inserção de link: " . $con->error);
            }
            $stmt->bind_param("ssi", $link_name, $link_url, $plant_id);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao adicionar link: " . $stmt->error);
            }
            $usefullink_id = mysqli_insert_id($con);
            $stmt->close();

            // Auditoria para inserção de link
            $new_value = "Link: $link_name, URL: $link_url";
            log_audit_action($con, 'usefullinks', 1, $changed_by, null, $new_value, $plant_id);
        }

        mysqli_commit($con);
        $success = "Planta e propriedades com imagens adicionadas com sucesso.";
    } catch (Exception $e) {
        mysqli_rollback($con);
        $error = $e->getMessage();
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
            mysqli_stmt_bind_result($stmt, $plant_name);
            if (!mysqli_stmt_fetch($stmt)) {
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
    $biology = filter_input_data_custom($con, $_POST['biology']);

    $bark_description = filter_input_data_custom($con, $_POST['bark_description'] ?? '');
    $trunk_description = filter_input_data_custom($con, $_POST['trunk_description'] ?? '');
    $leaf_description = filter_input_data_custom($con, $_POST['leaf_description'] ?? '');
    $flower_description = filter_input_data_custom($con, $_POST['flower_description'] ?? '');
    $fruit_description = filter_input_data_custom($con, $_POST['fruit_description'] ?? '');
    $seed_description = filter_input_data_custom($con, $_POST['seed_description'] ?? '');

    // Coleta de dados das propriedades
    $properties_data = isset($_POST['properties_data']) ? json_decode($_POST['properties_data'], true) : [];

    if ($edit_id > 0) {
        mysqli_begin_transaction($con);
        try {
            // Verifica se a planta existe e não está deletada
            $stmt = $con->prepare("SELECT name FROM Plants WHERE id = ? AND deleted_at IS NULL");
            if (!$stmt) {
                throw new Exception("Erro na preparação da consulta: " . $con->error);
            }
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            mysqli_stmt_bind_result($stmt, $plant_name);
            if (!mysqli_stmt_fetch($stmt)) {
                throw new Exception("Planta não encontrada ou já foi excluída.");
            }
            $stmt->close();

            // Atualizar os dados da planta
            $stmt = $con->prepare("UPDATE Plants SET name = ?, common_names = ?, division_id = ?, class_id = ?, `order_id` = ?, family_id = ?, genus_id = ?, region_id = ?, species = ?, applications = ?, ecology = ?, biology = ?, bark_description = ?, trunk_description = ?, leaf_description = ?, flower_description = ?, fruit_description = ?, seed_description = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Erro na preparação da atualização: " . $con->error);
            }
            $stmt->bind_param("ssiiiiiissssssssssi", $name, $common_names, $division_id, $class_id, $order_id, $family_id, $genus_id, $region_id, $species, $applications, $ecology, $biology, $bark_description, $trunk_description, $leaf_description, $flower_description, $fruit_description, $seed_description, $edit_id);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao atualizar planta: " . $stmt->error);
            }
            $stmt->close();

            // Auditoria para atualização de planta
            $table = 'Plants';
            $action_id = 2; // Atualização
            $changed_by = $_SESSION['id'];
            $old_value = "Planta: $plant_name"; // Você pode expandir para incluir outros campos se desejar
            $new_value = "Planta: $name"; // Similarmente, expanda conforme necessário
            log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value, $edit_id);

            // Obter propriedades existentes
            $stmt = $con->prepare("SELECT property_id, id FROM PlantsProperties WHERE plant_id = ?");
            if (!$stmt) {
                throw new Exception("Erro na preparação da consulta PlantsProperties: " . $con->error);
            }
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing_properties = [];
            while ($row = $result->fetch_assoc()) {
                $existing_properties[$row['property_id']] = $row['id'];
            }
            $stmt->close();

            // Processar propriedades
            foreach ($properties_data as $property_id => $images) {
                $property_id = intval($property_id);

                if (isset($existing_properties[$property_id])) {
                    $plants_property_id = $existing_properties[$property_id];
                } else {
                    // Inserir nova propriedade
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
                    $property_data = get_property_name($con, $property_id);
                    $property_name = $property_data['name'];
                    $new_value = "Planta: $plant_name, Propriedade: $property_name";
                    log_audit_action($con, 'PlantsProperties', 1, $changed_by, null, $new_value, $edit_id);
                }

                // Atualizar imagens existentes e inserir novas imagens com sort_order
                // Primeiro, obter as imagens existentes para esta propriedade
                $stmt = $con->prepare("SELECT id, sort_order FROM images WHERE plants_property_id = ? ORDER BY sort_order ASC");
                if (!$stmt) {
                    throw new Exception("Erro na preparação da consulta de imagens: " . $con->error);
                }
                $stmt->bind_param("i", $plants_property_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $existing_images = [];
                while ($row = $result->fetch_assoc()) {
                    $existing_images[$row['id']] = $row['sort_order'];
                }
                $stmt->close();

                // Coletar IDs das imagens enviadas no formulário
                $form_image_ids = [];
                foreach ($images as $imageData) {
                    if (isset($imageData['id']) && $imageData['id'] > 0) {
                        $form_image_ids[] = intval($imageData['id']);
                    }
                }

                // Determinar quais imagens devem ser removidas (existentes no BD, mas não no formulário)
                $images_to_delete = array_diff(array_keys($existing_images), $form_image_ids);

                // Excluir as imagens que não estão mais presentes no formulário
                if (!empty($images_to_delete)) {
                    // Preparar placeholders para a consulta IN
                    $placeholders = implode(',', array_fill(0, count($images_to_delete), '?'));
                    $delete_sql = "DELETE FROM images WHERE id IN ($placeholders)";
                    $stmt = $con->prepare($delete_sql);
                    if ($stmt) {
                        // Criar o tipo de parâmetro para bind_param
                        $types = str_repeat('i', count($images_to_delete));
                        // Vincular os parâmetros dinamicamente
                        $stmt->bind_param($types, ...$images_to_delete);
                        if (!$stmt->execute()) {
                            throw new Exception("Erro ao excluir imagens: " . $stmt->error);
                        }
                        $stmt->close();

                        // Auditoria para exclusão de imagens
                        foreach ($images_to_delete as $image_id) {
                            $table = 'images';
                            $action_id = 3; // Supondo que 3 represente 'Exclusão' no sistema de auditoria
                            $changed_by = $_SESSION['id'];
                            $old_value = "Imagem ID: $image_id";
                            $new_value = null;
                            log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value, $edit_id);
                        }
                    } else {
                        throw new Exception("Erro na preparação da exclusão de imagens: " . $con->error);
                    }
                }

                // Processar as imagens enviadas
                foreach ($images as $order => $imageData) {
                    if (isset($imageData['id']) && $imageData['id'] > 0) {
                        // Atualizar o sort_order da imagem existente
                        $image_id = intval($imageData['id']);
                        $new_sort_order = intval($order) + 1; // Inicia em 1
                        $stmt = $con->prepare("UPDATE images SET sort_order = ? WHERE id = ?");
                        if ($stmt) {
                            $stmt->bind_param("ii", $new_sort_order, $image_id);
                            if (!$stmt->execute()) {
                                throw new Exception("Erro ao atualizar sort_order da imagem: " . $stmt->error);
                            }
                            $stmt->close();

                            // Auditoria para atualização de sort_order
                            $new_value = "Imagem ID: $image_id, Nova Ordem: $new_sort_order";
                            log_audit_action($con, 'images', 2, $changed_by, null, $new_value, $edit_id);
                        }
                    } else {
                        // Inserir nova imagem
                        $source = filter_input_data_custom($con, $imageData['source']);
                        $image = $imageData['image']; // Base64 string

                        // Decodifica a imagem Base64
                        $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));

                        // Determina o próximo sort_order para esta propriedade
                        $stmt_order = $con->prepare("SELECT MAX(sort_order) FROM images WHERE plants_property_id = ?");
                        if ($stmt_order) {
                            $stmt_order->bind_param("i", $plants_property_id);
                            $stmt_order->execute();
                            $stmt_order->bind_result($max_sort_order);
                            $stmt_order->fetch();
                            $next_sort_order = $max_sort_order !== null ? $max_sort_order + 1 : 1;
                            $stmt_order->close();
                        } else {
                            $next_sort_order = 1; // Fallback caso a consulta falhe
                        }

                        // Insere na tabela images com sort_order
                        $stmt = $con->prepare("INSERT INTO images (imagem, source, plants_property_id, sort_order) VALUES (?, ?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception("Erro na preparação da consulta de imagem: " . $con->error);
                        }
                        $stmt->bind_param("ssii", $image_data, $source, $plants_property_id, $next_sort_order);
                        if (!$stmt->execute()) {
                            throw new Exception("Erro ao adicionar a imagem: " . $stmt->error);
                        }
                        $image_id = mysqli_insert_id($con);
                        $stmt->close();

                        // Auditoria para inserção de imagem
                        $new_value = "Imagem ID: $image_id, Fonte: $source, Ordem: $next_sort_order";
                        log_audit_action($con, 'images', 1, $changed_by, null, $new_value, $edit_id);
                    }
                }

            }

            // Processar Links Úteis
            $usefullinks_data = isset($_POST['usefullinks_data']) ? json_decode($_POST['usefullinks_data'], true) : [];

            // Buscar links existentes no banco de dados
            $stmt = $con->prepare("SELECT id, name, link FROM usefullinks WHERE plant_id = ? AND deleted_at IS NULL");
            if (!$stmt) {
                throw new Exception("Erro na preparação da consulta usefullinks: " . $con->error);
            }
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing_links = [];
            while ($row = $result->fetch_assoc()) {
                $existing_links[$row['id']] = $row;
            }
            $stmt->close();

            // Array para rastrear IDs de links submetidos
            $submitted_link_ids = [];

            // Processar cada link submetido
            foreach ($usefullinks_data as $link) {
                if (isset($link['id']) && intval($link['id']) > 0 && isset($existing_links[$link['id']])) {
                    // Link existente: verificar se houve atualização
                    $link_id = intval($link['id']);
                    $link_name = filter_input_data_custom($con, $link['name']);
                    $link_url = filter_input_data_custom($con, $link['link']);

                    if ($link_name != $existing_links[$link_id]['name'] || $link_url != $existing_links[$link_id]['link']) {
                        // Atualizar o link
                        $stmt = $con->prepare("UPDATE usefullinks SET name = ?, link = ? WHERE id = ?");
                        if (!$stmt) {
                            throw new Exception("Erro na preparação da atualização do link: " . $con->error);
                        }
                        $stmt->bind_param("ssi", $link_name, $link_url, $link_id);
                        if (!$stmt->execute()) {
                            throw new Exception("Erro ao atualizar link: " . $stmt->error);
                        }
                        $stmt->close();

                        // Auditoria para atualização de link
                        $old_value = "Link: " . $existing_links[$link_id]['name'] . ", URL: " . $existing_links[$link_id]['link'];
                        $new_value = "Link: $link_name, URL: $link_url";
                        log_audit_action($con, 'usefullinks', 2, $changed_by, $old_value, $new_value, $edit_id);
                    }

                    $submitted_link_ids[] = $link_id;
                } else {
                    // Novo link: inserir
                    $link_name = filter_input_data_custom($con, $link['name']);
                    $link_url = filter_input_data_custom($con, $link['link']);

                    $stmt = $con->prepare("INSERT INTO usefullinks (name, link, plant_id) VALUES (?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception("Erro na preparação da inserção de link: " . $con->error);
                    }
                    $stmt->bind_param("ssi", $link_name, $link_url, $edit_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Erro ao adicionar link: " . $stmt->error);
                    }
                    $usefullink_id = mysqli_insert_id($con);
                    $stmt->close();

                    // Auditoria para inserção de link
                    $new_value = "Link: $link_name, URL: $link_url";
                    log_audit_action($con, 'usefullinks', 1, $changed_by, null, $new_value, $edit_id);
                }
            }

            // Determinar quais links foram removidos e marcar como excluídos
            $links_to_delete = array_diff(array_keys($existing_links), $submitted_link_ids);
            foreach ($links_to_delete as $link_id) {
                $stmt = $con->prepare("UPDATE usefullinks SET deleted_at = NOW() WHERE id = ?");
                if (!$stmt) {
                    throw new Exception("Erro na preparação da exclusão do link: " . $con->error);
                }
                $stmt->bind_param("i", $link_id);
                if (!$stmt->execute()) {
                    throw new Exception("Erro ao excluir link: " . $stmt->error);
                }
                $stmt->close();

                // Auditoria para exclusão de link
                $old_value = "Link: " . $existing_links[$link_id]['name'] . ", URL: " . $existing_links[$link_id]['link'];
                $new_value = null;
                log_audit_action($con, 'usefullinks', 3, $changed_by, $old_value, $new_value, $edit_id);
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

// Lista de propriedades
$properties_list = [
    ['id' => 2, 'name' => 'Tronco', 'name_ref' => 'trunk'],
    ['id' => 3, 'name' => 'Casca', 'name_ref' => 'bark'],
    ['id' => 4, 'name' => 'Folha', 'name_ref' => 'leaf'],
    ['id' => 5, 'name' => 'Flor', 'name_ref' => 'flower'],
    ['id' => 6, 'name' => 'Fruta', 'name_ref' => 'fruit'],
    ['id' => 7, 'name' => 'Semente', 'name_ref' => 'seed']
];

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
                // Recuperar as imagens existentes com sort_order
                $stmt_images = $con->prepare("
                    SELECT pp.property_id, i.id as image_id, i.imagem, i.source, i.sort_order 
                    FROM PlantsProperties pp
                    JOIN images i ON pp.id = i.plants_property_id
                    WHERE pp.plant_id = ?
                    ORDER BY i.sort_order ASC
                ");
                if ($stmt_images) {
                    $stmt_images->bind_param("i", $edit_id);
                    $stmt_images->execute();
                    $result_images = $stmt_images->get_result();
                    while ($row = $result_images->fetch_assoc()) {
                        $property_id = $row['property_id'];
                        if (!isset($edit_plant['properties'][$property_id])) {
                            $edit_plant['properties'][$property_id] = [];
                        }
                        $edit_plant['properties'][$property_id][] = [
                            'id' => $row['image_id'],
                            'source' => $row['source'],
                            'image' => 'data:image/jpeg;base64,' . base64_encode($row['imagem']),
                            'sort_order' => $row['sort_order']
                        ];
                    }
                    $stmt_images->close();
                }
                $stmt = $con->prepare("SELECT id, name, link FROM usefullinks WHERE plant_id = ? AND deleted_at IS NULL");
                if ($stmt) {
                    $stmt->bind_param("i", $edit_id);
                    $stmt->execute();
                    $result_links = $stmt->get_result();
                    $edit_plant['usefullinks'] = [];
                    while ($row = $result_links->fetch_assoc()) {
                        $edit_plant['usefullinks'][] = $row;
                    }
                  
                }
                $edit_mode = true;
            }
            $stmt->close();
        }
    }
}
$qrcode_base_url = get_qrcode_url($con);
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

        .pagination a,
        .pagination span {
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
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary btn-sm" id="addPropertyButton">Adicionar Imagem</button>
                                    <div id="images_list_1" class="mt-3 d-flex gap-2">
                                        <!-- Propriedades adicionadas aparecerão aqui -->
                                        <?php
                                        ?>
                                    </div>
                                </div>

                                <!-- Dados da Planta -->
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
                                    <label for="division_id" class="form-label">Divisão</label>
                                    <select class="form-select" id="division_id" name="division_id" required>
                                        <option value="0">Selecione a divisão</option>
                                        <?php foreach ($divisions as $division) { ?>
                                            <option value="<?php echo htmlspecialchars($division['id']); ?>" <?php echo ($edit_mode && $edit_plant['division_id'] == $division['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($division['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Classe -->
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Classe</label>
                                    <select class="form-select" id="class_id" name="class_id" required>
                                        <option value="0">Selecione a classe</option>
                                        <?php foreach ($classes as $class) { ?>
                                            <option value="<?php echo htmlspecialchars($class['id']); ?>" <?php echo ($edit_mode && $edit_plant['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Ordem -->
                                <div class="mb-3">
                                    <label for="order_id" class="form-label">Ordem</label>
                                    <select class="form-select" id="order_id" name="order_id" required>
                                        <option value="0">Selecione a ordem</option>
                                        <?php foreach ($orders as $order) { ?>
                                            <option value="<?php echo htmlspecialchars($order['id']); ?>" <?php echo ($edit_mode && $edit_plant['order_id'] == $order['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($order['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Família -->
                                <div class="mb-3">
                                    <label for="family_id" class="form-label">Família</label>
                                    <select class="form-select" id="family_id" name="family_id" required>
                                        <option value="0">Selecione a família</option>
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
                                        <option value="0">Selecione o gênero</option>
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
                                        <option value="0">Selecione a região</option>
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
                                <div class="mb-3">
                                    <label for="biology" class="form-label">Biologia</label>
                                    <textarea class="form-control" id="biology" name="biology"><?php echo $edit_mode ? htmlspecialchars($edit_plant['biology']) : ''; ?></textarea>
                                </div>

                                <!-- Seção de Propriedades com Imagens -->
                                <?php foreach ($properties_list as $property_item): ?>
                                    <div class="property-section mb-4" data-property-id="<?php echo $property_item['id']; ?>">
                                        <h4><?php echo htmlspecialchars($property_item['name']); ?></h4>
                                        <button type="button" class="btn btn-primary btn-sm add-image-button" data-property-id="<?php echo $property_item['id']; ?>">Adicionar Imagem</button>
                                        <div class="images-list image-preview mt-3" id="images_list_<?php echo $property_item['id']; ?>">
                                            <?php 
                                            if ($edit_mode && isset($edit_plant['properties'][$property_item['id']])) {
                                                foreach ($edit_plant['properties'][$property_item['id']] as $image) { ?>
                                                    <div class="image-item" data-id="<?php echo htmlspecialchars($image['id']); ?>">
                                                        <button type="button" class="remove-image">&times;</button>
                                                        <img src="<?php echo htmlspecialchars($image['image']); ?>" alt="Imagem">
                                                    </div>
                                            <?php }
                                            } ?>
                                        </div>
                                        <div class="mb-3 mt-2">
                                            <!-- <label for="description_<?php echo $property_item['id']; ?>" class="form-label">Descrição</label> -->
                                            <?php
                                            // Determina o nome do campo de descrição com base na propriedade
                                            $description_field = strtolower($property_item['name_ref']) . '_description';
                                            ?>
                                            <textarea class="form-control" id="<?php echo $description_field; ?>" name="<?php echo $description_field; ?>"><?php echo $edit_mode ? htmlspecialchars($edit_plant[$description_field]) : ''; ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <!-- Seção de Links Úteis -->
                                <div class="mb-4">
                                    <h4>Links Úteis</h4>
                                    <button type="button" class="btn btn-primary btn-sm" id="addLinkButton">Adicionar Link</button>
                                    <div id="usefullinks_list" class="mt-3">
                                        <!-- Links adicionados aparecerão aqui -->
                                    </div>
                                </div>
                                <input type="hidden" name="usefullinks_data" id="usefullinks_data">


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
                                                <button type="button" class="btn btn-primary btn-sm qrcode-button" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">QR Code</button>
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
                    <div id="error-message" style="display: none;" class="alert alert-danger"></div>
                    <div class="modal-header">
                        <h5 class="modal-title">Adicionar Imagem</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
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

    <!-- Modal para QRCode -->
    <div class="modal fade" id="qrcodeModal" tabindex="-1" aria-labelledby="qrcodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">QR Code da Planta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Nome da Planta:</strong> <span id="plantName"></span></p>
                    <p><strong>ID da Planta:</strong> <span id="plantId"></span></p>
                    <div class="mb-3">
                        <label for="currentQrcodeUrl" class="form-label">URL atual:</label>
                        <input type="text" class="form-control" id="currentQrcodeUrl" readonly>
                    </div>
                    <div class="d-flex justify-content-center">
                        <img id="qrcodeImage" src="" alt="QR Code" class="img-fluid mb-3">
                    </div>
                </div>
                <div class="modal-footer ">
                    <a id="downloadQrcode" href="#" class="btn btn-primary me-2" download="qrcode.png">Baixar QR Code</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {

             // Variável PHP passada para JavaScript
             var qrcodeBaseUrl = "<?php echo htmlspecialchars($qrcode_base_url); ?>";

            // Inicializa o modal do QR Code
            var qrcodeModal = new bootstrap.Modal(document.getElementById('qrcodeModal'));
            var qrcodeImage = document.getElementById('qrcodeImage');
            var downloadQrcode = document.getElementById('downloadQrcode');

            // Elementos da Modal
            var plantNameSpan = document.getElementById('plantName');
            var plantIdSpan = document.getElementById('plantId');
            var currentQrcodeUrlInput = document.getElementById('currentQrcodeUrl');

            // Adiciona evento de clique para todos os botões "QRCode"
            document.querySelectorAll('.qrcode-button').forEach(function(button) {
                button.addEventListener('click', function() {
                    var plantId = this.getAttribute('data-id');
                    var plantName = this.getAttribute('data-name');

                    // Determina se a URL já possui parâmetros
                    var separator = qrcodeBaseUrl.includes('?') ? '&' : '?';

                    // Constrói a URL completa para o QR Code
                    var qrContent = qrcodeBaseUrl + separator + "id=" + encodeURIComponent(plantId);

                    // Utiliza uma API externa para gerar o QR Code
                    var qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" + encodeURIComponent(qrContent);

                    // Preenche os campos da modal
                    plantNameSpan.textContent = plantName;
                    plantIdSpan.textContent = plantId;
                    currentQrcodeUrlInput.value = qrContent;
                    qrcodeImage.src = qrCodeUrl;
                    downloadQrcode.href = qrCodeUrl;

                    // Exibe o modal
                    qrcodeModal.show();
                });
            });
            // Variáveis para armazenar dados
            var addPropertyButton = document.getElementById('addPropertyButton');
            var propertiesData = {}; // Objeto para armazenar dados por propriedade
            var propertyCounter = 0; // Contador para IDs únicos
            var currentPropertyId = null; // ID da propriedade atual

            // Inicializar propriedadesData com dados existentes se estiver no modo de edição
            <?php if ($edit_mode && isset($edit_plant['properties'])) { ?>
                propertiesData = <?php echo json_encode($edit_plant['properties']); ?>;
            <?php } ?>

            // Mostrar/Esconder Formulário de Adição de Planta
            var toggleFormButton = document.getElementById('toggleForm');
            var plantForm = document.getElementById('plant-form');
            var plantList = document.getElementById('plant-list');
            var cancelAddPlant = document.getElementById('cancelAddPlant');

            addPropertyButton.addEventListener('click', function() {
                currentPropertyId = 1;
                propertyForm.reset();
                addPropertyModal.show();
            });

            function displayError(message) {
                var errorMessageElement = document.getElementById('error-message');
                errorMessageElement.textContent = message;
                errorMessageElement.style.display = 'block';
            }

            function hideError() {
                var errorMessageElement = document.getElementById('error-message');
                errorMessageElement.style.display = 'none';
            }

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

            // Event listener para botões 'Adicionar Imagem'
            document.querySelectorAll('.add-image-button').forEach(function(button) {
                button.addEventListener('click', function() {
                    var propertyId = this.getAttribute('data-property-id');
                    currentPropertyId = propertyId; // Armazena o ID da propriedade atual
                    // Abre a modal
                    propertyForm.reset();
                    addPropertyModal.show();
                });
            });

            var addPropertyModal = new bootstrap.Modal(document.getElementById('addPropertyModal'));
            var propertyForm = document.getElementById('propertyForm');
            var addPlantFormElement = document.getElementById('addPlantForm');

            // 'propertyForm' submit handler
            propertyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                hideError(); 
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

                    // Verifica o tamanho do arquivo (5 MB = 5 * 1024 * 1024 bytes)
                    var maxFileSize = 5 * 1024 * 1024; // 5 MB
                    if (file.size > maxFileSize) {
                        displayError('O tamanho da imagem não pode exceder 5 MB.');
                        return;
                    }

                    // Cria um objeto URL para exibir a imagem
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        var imageUrl = event.target.result;

                        // Inicializa o array para a propriedade atual se não existir
                        if (!propertiesData[currentPropertyId]) {
                            propertiesData[currentPropertyId] = [];
                        }

                        // Determina o sort_order com base na quantidade de imagens existentes
                        var nextSortOrder = propertiesData[currentPropertyId].length + 1;

                        // Adiciona a imagem ao array da propriedade atual
                        propertiesData[currentPropertyId].push({
                            id: null, // Será atribuído pelo backend
                            source: source,
                            image: imageUrl, // Armazena a URL Base64
                            sort_order: nextSortOrder
                        });

                        // Atualiza a visualização das imagens
                        updateImagesList(currentPropertyId);

                        // Fecha a modal
                        addPropertyModal.hide();
                    };
                    reader.readAsDataURL(file);
                } else {
                    alert('Por favor, preencha todos os campos.');
                }
            });

            // Função para atualizar a lista de imagens de uma propriedade
            function updateImagesList(propertyId) {
                var imagesList = document.getElementById('images_list_' + propertyId);
                imagesList.innerHTML = '';
                var imagesArray = propertiesData[propertyId] || [];

                imagesArray.forEach(function(imageData, index) {
                    var div = document.createElement('div');
                    div.classList.add('image-item');
                    div.setAttribute('data-id', imageData.id !== null ? imageData.id : 'temp-' + index); // Usa ID real ou temporário

                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.classList.add('remove-image');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.onclick = function() {
                        imagesArray.splice(index, 1);
                        // Reatribuir sort_order após remoção
                        imagesArray.forEach(function(img, idx) {
                            img.sort_order = idx + 1;
                        });
                        updateImagesList(propertyId);
                    };

                    var img = document.createElement('img');
                    img.src = imageData.image;
                    img.alt = 'Imagem';

                    div.appendChild(removeBtn);
                    div.appendChild(img);
                    imagesList.appendChild(div);
                });

                // Inicializa o Sortable.js para permitir reordenação via drag-and-drop
                Sortable.create(imagesList, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function(evt) {
                        // Reordena o array com base na nova ordem
                        var orderedIds = Array.from(imagesList.children).map(function(child) {
                            return child.getAttribute('data-id');
                        });

                        propertiesData[propertyId].sort(function(a, b) {
                            var indexA = orderedIds.indexOf(a.id !== null ? a.id.toString() : 'temp-' + propertiesData[propertyId].indexOf(a));
                            var indexB = orderedIds.indexOf(b.id !== null ? b.id.toString() : 'temp-' + propertiesData[propertyId].indexOf(b));
                            return indexA - indexB;
                        });

                        // Reatribuir sort_order após reordenação
                        propertiesData[propertyId].forEach(function(img, idx) {
                            img.sort_order = idx + 1;
                        });
                    }
                });
            }

            // Inicializar listas de imagens existentes
            <?php if ($edit_mode && isset($edit_plant['properties'])) { ?>
                <?php foreach ($edit_plant['properties'] as $property_id => $images) { ?>
                    updateImagesList(<?php echo $property_id; ?>);
                <?php } ?>
            <?php } ?>

            // Handle form submission
            addPlantFormElement.addEventListener('submit', function(e) {
                // Adiciona os dados das propriedades ao campo oculto como JSON
                var propertiesInput = document.createElement('input');
                propertiesInput.type = 'hidden';
                propertiesInput.name = 'properties_data';
                propertiesInput.value = JSON.stringify(propertiesData);
                addPlantFormElement.appendChild(propertiesInput);

                // Envia o formulário normalmente
            });

            // Modal de Confirmação de Exclusão
            var confirmDeleteModal = document.getElementById('confirmDeleteModal');
            confirmDeleteModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var deleteIdInput = confirmDeleteModal.querySelector('#deleteId');
                deleteIdInput.value = id;
            });
        });
    </script>
    <script>

    document.addEventListener('DOMContentLoaded', function() {
        // ... código JavaScript existente ...

        // Variável para armazenar os dados dos links úteis
        var usefullinksData = [];
        <?php if ($edit_mode && isset($edit_plant['usefullinks'])) { ?>
            usefullinksData = <?php echo json_encode($edit_plant['usefullinks']); ?>;
        <?php } ?>

        var addLinkButton = document.getElementById('addLinkButton');
        var usefullinksList = document.getElementById('usefullinks_list');

        // Função para renderizar os links úteis na interface
        function renderUsefullinks() {
            usefullinksList.innerHTML = '';
            usefullinksData.forEach(function(link, index) {
                var linkItem = document.createElement('div');
                linkItem.classList.add('link-item', 'mb-2');
                linkItem.setAttribute('data-index', index);

                var inputGroup = document.createElement('div');
                inputGroup.classList.add('input-group');

                var nameInput = document.createElement('input');
                nameInput.type = 'text';
                nameInput.classList.add('form-control', 'link-name');
                nameInput.placeholder = 'Nome';
                nameInput.value = link.name;
                nameInput.required = true;

                var urlInput = document.createElement('input');
                urlInput.type = 'url';
                urlInput.classList.add('form-control', 'link-url');
                urlInput.placeholder = 'URL';
                urlInput.value = link.link;
                urlInput.required = true;

                var removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.classList.add('btn', 'btn-danger');
                removeButton.innerHTML = '&times;';
                removeButton.addEventListener('click', function() {
                    usefullinksData.splice(index, 1);
                    renderUsefullinks();
                });

                inputGroup.appendChild(nameInput);
                inputGroup.appendChild(urlInput);
                inputGroup.appendChild(removeButton);

                linkItem.appendChild(inputGroup);
                usefullinksList.appendChild(linkItem);

                // Atualizar os dados ao modificar os campos
                nameInput.addEventListener('input', function() {
                    usefullinksData[index].name = this.value;
                });
                urlInput.addEventListener('input', function() {
                    usefullinksData[index].link = this.value;
                });
            });
        }

        // Inicializar a renderização dos links
        renderUsefullinks();

        // Evento para adicionar um novo link útil
        addLinkButton.addEventListener('click', function() {
            usefullinksData.push({
                id: null, // Será definido no backend se for um link existente
                name: '',
                link: ''
            });
            renderUsefullinks();
        });

        // Evento de submissão do formulário para incluir os dados dos links úteis
        var addPlantFormElement = document.getElementById('addPlantForm');
        addPlantFormElement.addEventListener('submit', function(e) {
            // Adiciona os dados dos links úteis no campo oculto
            var usefullinksInput = document.getElementById('usefullinks_data');
            usefullinksInput.value = JSON.stringify(usefullinksData);

            // O restante do processamento do formulário continua normalmente
        });

        // ... restante do código JavaScript existente ...
    });



    </script>
</body>

</html>
