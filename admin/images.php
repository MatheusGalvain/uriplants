<?php
// images.php

session_start();
include_once('includes/config.php');
require_once('includes/audit.php'); 

// Configuração de exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificação de sessão
if (empty($_SESSION['id'])) {
    header('location:logout.php');
    exit();
}

function log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id = null) {
    log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);
}

function get_plant_name($con, $plant_id) {
    $sql = "SELECT name FROM plants WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $plant_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $plant_name);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return htmlspecialchars($plant_name);
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
        return htmlspecialchars($property_name);
    }
    return "Desconhecida";
}

function upload_image($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erro no upload da imagem.");
    }
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Tipo de imagem inválido. Apenas JPEG, PNG e GIF são permitidos.");
    }
    $image = file_get_contents($file['tmp_name']);
    return $image;
}

function fetch_all_plants_properties_images($con, $search = '') {
    $searchQuery = '';
    $params = [];
    $types = '';

    if ($search) {
        $searchQuery = "AND (p.name LIKE ? OR pr.name LIKE ? OR i.source LIKE ?)";
        $search_param = '%' . $search . '%';
        $params = [$search_param, $search_param, $search_param];
        $types = 'sss';
    }

    $sql = "
        SELECT pp.id, pp.plant_id, pp.property_id, pp.created_at,
               p.name as plant_name, pr.name as property_name,
               i.id as image_id, i.imagem, i.source as image_source, i.sort_order as sort_order
        FROM PlantsProperties pp
        LEFT JOIN plants p ON pp.plant_id = p.id
        LEFT JOIN properties pr ON pp.property_id = pr.id
        LEFT JOIN images i ON pp.id = i.plants_property_id
        WHERE pp.deleted_at IS NULL $searchQuery
        ORDER BY p.name ASC, pr.name ASC, pp.created_at DESC
    ";

    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        throw new Exception("Erro na preparação da consulta: " . mysqli_error($con));
    }

    if ($search) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $plant_id = $row['plant_id'];
        $property_id = $row['property_id'];
        $plants_property_id = $row['id']; // pp.id

        if (!isset($data[$plant_id])) {
            $data[$plant_id] = [
                'plant_name' => $row['plant_name'],
                'properties' => []
            ];
        }

        if (!isset($data[$plant_id]['properties'][$plants_property_id])) {
            $data[$plant_id]['properties'][$plants_property_id] = [
                'property_name' => $row['property_name'],
                'created_at' => $row['created_at'],
                'images' => []
            ];
        }

        if ($row['image_id']) {
            $data[$plant_id]['properties'][$plants_property_id]['images'][] = [
                'image_id' => $row['image_id'],
                'imagem' => $row['imagem'],
                'image_source' => $row['image_source'],
                'sort_order' => $row['sort_order']
            ];
        }
    }

    mysqli_stmt_close($stmt);
    return $data;
}

// Tratamento de operações POST
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Adicionar Propriedade
        if (isset($_POST['add_property'])) {
            $plant_id = intval($_POST['plant_id']);
            $property_id = intval($_POST['property_id']);
            $source = mysqli_real_escape_string($con, $_POST['source']);

            mysqli_begin_transaction($con);

            try {
                $sql = "INSERT INTO PlantsProperties (plant_id, property_id, created_at) VALUES (?, ?, NOW())";
                $stmt = mysqli_prepare($con, $sql);
                if (!$stmt) {
                    throw new Exception("Erro na preparação da consulta: " . mysqli_error($con));
                }
                mysqli_stmt_bind_param($stmt, 'ii', $plant_id, $property_id);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Erro ao adicionar propriedade da planta: " . mysqli_error($con));
                }
                $plants_property_id = mysqli_insert_id($con);

                // Upload de imagem
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image = upload_image($_FILES['image']);

                    $image_sql = "INSERT INTO images (imagem, source, plants_property_id) VALUES (?, ?, ?)";
                    $image_stmt = mysqli_prepare($con, $image_sql);
                    if (!$image_stmt) {
                        throw new Exception("Erro na preparação da consulta de imagem: " . mysqli_error($con));
                    }
                    mysqli_stmt_bind_param($image_stmt, 'ssi', $image, $source, $plants_property_id);
                    if (!mysqli_stmt_execute($image_stmt)) {
                        throw new Exception("Erro ao adicionar a imagem: " . mysqli_error($con));
                    }
                    $success = "Propriedade e imagem adicionadas com sucesso.";
                } else {
                    $success = "Propriedade adicionada com sucesso.";
                }

                // Registro de auditoria
                $plant_name = get_plant_name($con, $plant_id);
                $property_name = get_property_name($con, $property_id);
                $new_value = "Planta: $plant_name, Propriedade: $property_name";
                log_audit_action($con, 'PlantsProperties', 1, $_SESSION['id'], null, $new_value, $plant_id);

                mysqli_commit($con);
            } catch (Exception $e) {
                mysqli_rollback($con);
                $error = $e->getMessage();
            }
        }

        // Editar Propriedade
        if (isset($_POST['edit_property'])) {
            $id = intval($_POST['id']);
            $plant_id = intval($_POST['plant_id']);
            $property_id = intval($_POST['property_id']);
            $source = mysqli_real_escape_string($con, $_POST['source']);

            // Verificar existência da propriedade
            $current_query = mysqli_prepare($con, "SELECT plant_id, property_id FROM PlantsProperties WHERE id = ?");
            if (!$current_query) {
                throw new Exception("Erro na preparação da consulta: " . mysqli_error($con));
            }
            mysqli_stmt_bind_param($current_query, 'i', $id);
            mysqli_stmt_execute($current_query);
            mysqli_stmt_bind_result($current_query, $old_plant_id, $old_property_id);
            if (!mysqli_stmt_fetch($current_query)) {
                mysqli_stmt_close($current_query);
                throw new Exception("Propriedade não encontrada.");
            }
            mysqli_stmt_close($current_query);

            mysqli_begin_transaction($con);

            try {
                // Atualizar PlantsProperties
                $update_sql = "UPDATE PlantsProperties SET plant_id = ?, property_id = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($con, $update_sql);
                if (!$update_stmt) {
                    throw new Exception("Erro na preparação da consulta de atualização: " . mysqli_error($con));
                }
                mysqli_stmt_bind_param($update_stmt, 'iii', $plant_id, $property_id, $id);
                if (!mysqli_stmt_execute($update_stmt)) {
                    throw new Exception("Erro ao atualizar propriedade da planta: " . mysqli_error($con));
                }

                // Atualizar imagem, se fornecida
                $image_updated = false;
                $image_id = null;
                if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === UPLOAD_ERR_OK) {
                    $image = upload_image($_FILES['edit_image']);

                    $image_sql = "UPDATE images SET imagem = ?, source = ? WHERE plants_property_id = ?";
                    $image_stmt = mysqli_prepare($con, $image_sql);
                    if (!$image_stmt) {
                        throw new Exception("Erro na preparação da consulta de imagem: " . mysqli_error($con));
                    }
                    mysqli_stmt_bind_param($image_stmt, 'ssi', $image, $source, $id);
                    if (!mysqli_stmt_execute($image_stmt)) {
                        throw new Exception("Erro ao atualizar a imagem: " . mysqli_error($con));
                    }

                    // Verificar se a imagem foi atualizada
                    $image_select_sql = "SELECT id FROM images WHERE plants_property_id = ?";
                    $image_select_stmt = mysqli_prepare($con, $image_select_sql);
                    if ($image_select_stmt) {
                        mysqli_stmt_bind_param($image_select_stmt, 'i', $id);
                        mysqli_stmt_execute($image_select_stmt);
                        mysqli_stmt_bind_result($image_select_stmt, $image_id);
                        mysqli_stmt_fetch($image_select_stmt);
                        mysqli_stmt_close($image_select_stmt);
                        if ($image_id !== null) {
                            $image_updated = true;
                        }
                    }

                    if ($image_updated) {
                        $success = "Propriedade e imagem atualizadas com sucesso.";
                    } else {
                        throw new Exception("Imagem atualizada, mas não foi possível recuperar o ID da imagem.");
                    }
                } else {
                    $success = "Propriedade atualizada com sucesso.";
                }

                // Registro de auditoria
                $old_plant_name = get_plant_name($con, $old_plant_id);
                $old_property_name = get_property_name($con, $old_property_id);
                $new_plant_name = get_plant_name($con, $plant_id);
                $new_property_name = get_property_name($con, $property_id);

                $old_value = "Planta: $old_plant_name, Propriedade: $old_property_name";
                $new_value = "Planta: $new_plant_name, Propriedade: $new_property_name";
                if ($image_updated) {
                    $new_value .= ", Imagem ID: " . $image_id;
                }

                log_audit_action($con, 'PlantsProperties', 3, $_SESSION['id'], $old_value, $new_value, $plant_id);

                mysqli_commit($con);
            } catch (Exception $e) {
                mysqli_rollback($con);
                $error = $e->getMessage();
            }
        }

        // Deletar Propriedade
        if (isset($_POST['delete_property'])) {
            $id = intval($_POST['id']);

            // Obter dados atuais
            $current_query = mysqli_prepare($con, "SELECT plant_id, property_id FROM PlantsProperties WHERE id = ?");
            if (!$current_query) {
                throw new Exception("Erro na preparação da consulta: " . mysqli_error($con));
            }
            mysqli_stmt_bind_param($current_query, 'i', $id);
            mysqli_stmt_execute($current_query);
            mysqli_stmt_bind_result($current_query, $plant_id, $property_id);
            if (!mysqli_stmt_fetch($current_query)) {
                mysqli_stmt_close($current_query);
                throw new Exception("Propriedade não encontrada.");
            }
            mysqli_stmt_close($current_query);

            mysqli_begin_transaction($con);

            try {
                // Deletar imagens associadas
                $delete_images_sql = "DELETE FROM images WHERE plants_property_id = ?";
                $delete_images_stmt = mysqli_prepare($con, $delete_images_sql);
                if (!$delete_images_stmt) {
                    throw new Exception("Erro na preparação da consulta de exclusão de imagens: " . mysqli_error($con));
                }
                mysqli_stmt_bind_param($delete_images_stmt, 'i', $id);
                if (!mysqli_stmt_execute($delete_images_stmt)) {
                    throw new Exception("Erro ao excluir imagens da propriedade: " . mysqli_error($con));
                }

                // Deletar PlantsProperties
                $delete_sql = "DELETE FROM PlantsProperties WHERE id = ?";
                $delete_stmt = mysqli_prepare($con, $delete_sql);
                if (!$delete_stmt) {
                    throw new Exception("Erro na preparação da consulta de exclusão: " . mysqli_error($con));
                }
                mysqli_stmt_bind_param($delete_stmt, 'i', $id);
                if (!mysqli_stmt_execute($delete_stmt)) {
                    throw new Exception("Erro ao excluir propriedade da planta: " . mysqli_error($con));
                }

                // Registro de auditoria
                $plant_name = get_plant_name($con, $plant_id);
                $property_name = get_property_name($con, $property_id);
                $old_value = "Planta ID: $plant_id, Nome: $plant_name, Propriedade: $property_name";
                $new_value = null;

                log_audit_action($con, 'PlantsProperties', 2, $_SESSION['id'], $old_value, $new_value, $plant_id);

                mysqli_commit($con);
                $success = "Propriedade da planta excluída com sucesso.";
            } catch (Exception $e) {
                mysqli_rollback($con);
                $error = $e->getMessage();
            }
        }
    }
} catch (Exception $e) {
    $error = "Ocorreu um erro inesperado: " . $e->getMessage();
}

// Buscar propriedades e imagens
$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';
try {
    $plants_properties_images = fetch_all_plants_properties_images($con, $search);
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Propriedades das Plantas</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Imagens</h1>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Adicionar uma Nova Imagem</h5>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="plant_id" class="form-label">Planta</label>
                                    <select class="form-select" id="plant_id" name="plant_id" required>
                                        <option value="">Selecione uma planta</option>
                                        <?php
                                        $plants = mysqli_query($con, "SELECT id, name FROM plants ORDER BY name ASC");
                                        while ($plant = mysqli_fetch_assoc($plants)) {
                                            echo "<option value=\"" . htmlspecialchars($plant['id']) . "\">" . htmlspecialchars($plant['name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="property_id" class="form-label">Propriedade</label>
                                    <select class="form-select" id="property_id" name="property_id" required>
                                        <option value="">Selecione uma propriedade</option>
                                        <?php
                                        $properties = mysqli_query($con, "SELECT id, name FROM properties ORDER BY name ASC");
                                        while ($property = mysqli_fetch_assoc($properties)) {
                                            echo "<option value=\"" . htmlspecialchars($property['id']) . "\">" . htmlspecialchars($property['name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="source" class="form-label">Fonte da Imagem</label>
                                    <input type="text" class="form-control" id="source" name="source" required>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Imagem</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                </div>
                                <button type="submit" name="add_property" class="btn btn-primary">Adicionar Imagem</button>
                            </form>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Imagens Cadastradas</h5>
                                <form method="POST" action="" class="d-flex">
                                    <input type="text" class="form-control me-2" name="search" placeholder="Buscar" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" type="submit">Buscar</button>
                                    <?php if ($search) { ?>
                                        <a href="images.php" class="btn btn-secondary ms-2">Remover Filtro</a>
                                    <?php } ?>
                                </form>
                            </div>

                            <?php if (!empty($plants_properties_images)) { ?>
                                <?php foreach ($plants_properties_images as $plant_id => $plant) { ?>
                                    <div class="mb-4">
                                        <h4>Planta: <?php echo htmlspecialchars($plant['plant_name']); ?></h4>
                                        <div class="d-flex flex-wrap">
                                            <?php if (!empty($plant['properties'])) { ?>
                                                <?php foreach ($plant['properties'] as $plants_property_id => $property) { ?>
                                                    <div class="card me-3 mb-3" style="width: 18rem;">
                                                        <div class="card-header">
                                                            Propriedade: <?php echo htmlspecialchars($property['property_name']); ?>
                                                        </div>
                                                        <div class="card-body">
                                                            
                                                            
                                                            <?php if (!empty($property['images'])) { ?>
                                                                <div class="row">
                                                                    <?php foreach ($property['images'] as $image) { ?>
                                                                        <div class="col-md-12">
                                                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($image['imagem']); ?>" class="card-img-top" alt="Imagem" style="height: 200px; object-fit: cover;">
                                                                        </div>
                                                                    <?php } ?>
                                                                </div>
                                                            <?php } else { ?>
                                                                <p>Sem imagens associadas.</p>
                                                            <?php } ?>
                                                            <div class="d-flex justify-content-between mt-1">
                                                                <button type="button" class="btn btn-success btn-sm edit-button" 
                                                                    data-bs-toggle="modal" data-bs-target="#editPropertyModal"
                                                                    data-id="<?php echo htmlspecialchars($plants_property_id); ?>" 
                                                                    data-plant_id="<?php echo htmlspecialchars($plant_id); ?>"
                                                                    data-property_id="<?php echo htmlspecialchars($property_id); ?>"
                                                                    data-sort="<?php echo htmlspecialchars($image['sort_order']); ?>"
                                                                    data-source="<?php echo htmlspecialchars($image['image_source']); ?>">
                                                                    Editar
                                                                </button>
                                                                <button type="button" class="btn btn-danger btn-sm delete-button" 
                                                                    data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" 
                                                                    data-id="<?php echo htmlspecialchars($plants_property_id); ?>">
                                                                    Excluir
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <div class="text-center">Nenhuma imagem encontrada.</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>

                            <?php } else { ?>
                                <div class="text-center">Nenhuma imagem encontrada.</div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Modal de Exclusão -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        Você realmente deseja excluir esta propriedade e suas imagens associadas?
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_property" class="btn btn-danger">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editPropertyModal" tabindex="-1" aria-labelledby="editPropertyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPropertyModalLabel">Editar Propriedade da Planta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editPropertyId">
                        <div class="mb-3">
                            <label for="editPlantId" class="form-label">Planta</label>
                            <select class="form-select" id="editPlantId" name="plant_id" required>
                                <option value="">Selecione uma planta</option>
                                <?php
                                mysqli_data_seek($plants, 0); 
                                while ($plant = mysqli_fetch_assoc($plants)) {
                                    echo "<option value=\"" . htmlspecialchars($plant['id']) . "\">" . htmlspecialchars($plant['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editPropertyIdSelect" class="form-label">Propriedade</label>
                            <select class="form-select" id="editPropertyIdSelect" name="property_id" required>
                                <option value="">Selecione uma propriedade</option>
                                <?php
                                mysqli_data_seek($properties, 0); 
                                while ($property = mysqli_fetch_assoc($properties)) {
                                    echo "<option value=\"" . htmlspecialchars($property['id']) . "\">" . htmlspecialchars($property['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editSource" class="form-label">Fonte da Imagem</label>
                            <input type="text" class="form-control" id="editSource" name="source" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSort" class="form-label">Ordenação</label>
                            <input type="text" class="form-control" id="editSort" name="sort" required>
                        </div>
                        <div class="mb-3">
                            <label for="editImage" class="form-label">Imagem (deixe em branco para não alterar)</label>
                            <input type="file" class="form-control" id="editImage" name="edit_image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="edit_property" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Scripts para passar os dados aos modais
        document.addEventListener('DOMContentLoaded', function() {
           // Modal de Exclusão
        var deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#confirmDeleteModal"]');
        var deleteIdInput = document.getElementById('deleteId');

        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                deleteIdInput.value = id;
            });
        });

        // Modal de Edição
        var editButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#editPropertyModal"]');
        var editPropertyIdInput = document.getElementById('editPropertyId');
        var editPlantIdSelect = document.getElementById('editPlantId');
        var editPropertyIdSelect = document.getElementById('editPropertyIdSelect');
        var editSourceInput = document.getElementById('editSource');
        var editSortInput = document.getElementById('editSort');

        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                var plant_id = this.getAttribute('data-plant_id');
                var property_id = this.getAttribute('data-property_id');
                var source = this.getAttribute('data-source');
                var sort = this.getAttribute('data-sort');

                editPropertyIdInput.value = id;
                editPlantIdSelect.value = plant_id;
                editPropertyIdSelect.value = property_id;
                editSourceInput.value = source;
                editSortInput.value = sort;
            });
        });
        });
    </script>
</body>

</html>
