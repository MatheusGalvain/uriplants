<?php
session_start();
include_once('includes/config.php');
require_once('includes/audit.php'); 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (strlen($_SESSION['id']) == 0) {
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

if (isset($_POST['add_property'])) {
    $plant_id = intval($_POST['plant_id']);
    $property_id = intval($_POST['property_id']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $source = mysqli_real_escape_string($con, $_POST['source']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $error = "Tipo de imagem inválido. Apenas JPEG, PNG e GIF são permitidos.";
        }
    }

    if (!isset($error)) {
        mysqli_begin_transaction($con);
        try {

            $sql = "INSERT INTO PlantsProperties (plant_id, property_id, description, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = mysqli_prepare($con, $sql);
            if ($stmt === false) {
                throw new Exception("Erro na preparação da consulta: " . mysqli_error($con));
            }
            mysqli_stmt_bind_param($stmt, 'iis', $plant_id, $property_id, $description);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erro ao adicionar propriedade da planta: " . mysqli_error($con));
            }

            $plants_property_id = mysqli_insert_id($con);

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = file_get_contents($_FILES['image']['tmp_name']);

                $image_sql = "INSERT INTO images (imagem, source, plants_property_id) VALUES (?, ?, ?)";
                $image_stmt = mysqli_prepare($con, $image_sql);
                if ($image_stmt === false) {
                    throw new Exception("Erro na preparação da consulta de imagem: " . mysqli_error($con));
                }

                mysqli_stmt_bind_param($image_stmt, 'ssi', $image, $source, $plants_property_id);

                if (!mysqli_stmt_execute($image_stmt)) {
                    throw new Exception("Erro ao adicionar a imagem: " . mysqli_error($con));
                }

                $success = "Imagem adicionada com sucesso.";
            } else {
                $success = "Propriedade adicionada com sucesso.";
            }

            $plant_name = get_plant_name($con, $plant_id);
            $property_name = get_property_name($con, $property_id);

            $table = 'PlantsProperties';
            $action_id = 1; // Adição
            $changed_by = $_SESSION['id'];
            $old_value = null;
            $new_value = "Planta: $plant_name, Propriedade: $property_name, Descrição: $description";
            log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);

            mysqli_commit($con);
        } catch (Exception $e) {
            mysqli_rollback($con);
            $error = $e->getMessage();
        }
    }
}

if (isset($_POST['edit_property'])) {
    $id = intval($_POST['id']);
    $plant_id = intval($_POST['plant_id']);
    $property_id = intval($_POST['property_id']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $source = mysqli_real_escape_string($con, $_POST['source']);

    $current_query = mysqli_query($con, "SELECT plant_id, property_id, description FROM PlantsProperties WHERE id = $id");
    if (mysqli_num_rows($current_query) == 0) {
        $error = "Propriedade não encontrada.";
    } else {
        $current_data = mysqli_fetch_assoc($current_query);
        $old_plant_id = $current_data['plant_id'];
        $old_property_id = $current_data['property_id'];
        $old_description = $current_data['description'];

        $old_plant_name = get_plant_name($con, $old_plant_id);
        $old_property_name = get_property_name($con, $old_property_id);

        mysqli_begin_transaction($con);
        try {

            $update_sql = "UPDATE PlantsProperties SET plant_id = ?, property_id = ?, description = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($con, $update_sql);
            if ($update_stmt === false) {
                throw new Exception("Erro na preparação da consulta de atualização: " . mysqli_error($con));
            }
            mysqli_stmt_bind_param($update_stmt, 'iisi', $plant_id, $property_id, $description, $id);

            if (!mysqli_stmt_execute($update_stmt)) {
                throw new Exception("Erro ao atualizar propriedade da planta: " . mysqli_error($con));
            }

            if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($_FILES['edit_image']['type'], $allowed_types)) {
                    throw new Exception("Tipo de imagem inválido. Apenas JPEG, PNG e GIF são permitidos.");
                }

                $image = file_get_contents($_FILES['edit_image']['tmp_name']);

                $image_sql = "UPDATE images SET imagem = ?, source = ? WHERE plants_property_id = ?";
                $image_stmt = mysqli_prepare($con, $image_sql);
                if ($image_stmt === false) {
                    throw new Exception("Erro na preparação da consulta de imagem: " . mysqli_error($con));
                }

                mysqli_stmt_bind_param($image_stmt, 'ssi', $image, $source, $id);

                if (!mysqli_stmt_execute($image_stmt)) {
                    throw new Exception("Erro ao atualizar a imagem: " . mysqli_error($con));
                }

                $success = "Propriedade e imagem atualizadas com sucesso.";
            } else {
                $success = "Propriedade atualizada com sucesso.";
            }

            $new_plant_name = get_plant_name($con, $plant_id);
            $new_property_name = get_property_name($con, $property_id);

            $old_value = "Planta: $old_plant_name, Propriedade: $old_property_name, Descrição: $old_description";
            $new_value = "Planta: $new_plant_name, Propriedade: $new_property_name, Descrição: $description";

            if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == 0) {
                $new_value .= ", Imagem atualizada";
            }

            $table = 'PlantsProperties';
            $action_id = 3; // Edição
            $changed_by = $_SESSION['id'];
            log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);

            mysqli_commit($con);
        } catch (Exception $e) {
            mysqli_rollback($con);
            $error = $e->getMessage();
        }
    }
}

if (isset($_POST['delete_property'])) {
    $id = intval($_POST['id']);

    $current_query = mysqli_query($con, "SELECT plant_id, property_id, description FROM PlantsProperties WHERE id = $id");
    if (mysqli_num_rows($current_query) == 0) {
        $error = "Propriedade não encontrada.";
    } else {
        $current_data = mysqli_fetch_assoc($current_query);
        $plant_id = $current_data['plant_id'];
        $property_id = $current_data['property_id'];
        $description = $current_data['description'];


        $plant_name = get_plant_name($con, $plant_id);
        $property_name = get_property_name($con, $property_id);

        mysqli_begin_transaction($con);
        try {

            $delete_images_sql = "DELETE FROM images WHERE plants_property_id = ?";
            $delete_images_stmt = mysqli_prepare($con, $delete_images_sql);
            if ($delete_images_stmt === false) {
                throw new Exception("Erro na preparação da consulta de exclusão de imagens: " . mysqli_error($con));
            }
            mysqli_stmt_bind_param($delete_images_stmt, 'i', $id);
            if (!mysqli_stmt_execute($delete_images_stmt)) {
                throw new Exception("Erro ao excluir imagens da propriedade: " . mysqli_error($con));
            }

            $sql = "DELETE FROM PlantsProperties WHERE id = ?";
            $stmt = mysqli_prepare($con, $sql);
            if ($stmt === false) {
                throw new Exception("Erro na preparação da consulta de exclusão: " . mysqli_error($con));
            }
            mysqli_stmt_bind_param($stmt, 'i', $id);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erro ao excluir propriedade da planta: " . mysqli_error($con));
            }

            $old_value = "Planta: $plant_name, Propriedade: $property_name, Descrição: $description";
            $new_value = null;

            $table = 'PlantsProperties';
            $action_id = 2; 
            $changed_by = $_SESSION['id'];
            log_audit_action($con, $table, $action_id, $changed_by, $old_value, $new_value);

            mysqli_commit($con);
            $success = "Propriedade da planta excluída com sucesso.";
        } catch (Exception $e) {
            mysqli_rollback($con);
            $error = $e->getMessage();
        }
    }
}

$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';

$searchQuery = $search ? "AND (p.name LIKE '%$search%' OR pr.name LIKE '%$search%' OR i.source LIKE '%$search%')" : "";
$propertiesQuery = mysqli_query($con, "
    SELECT pp.*, p.name as plant_name, pr.name as property_name, i.imagem, i.source as image_source
    FROM PlantsProperties pp
    LEFT JOIN plants p ON pp.plant_id = p.id
    LEFT JOIN properties pr ON pp.property_id = pr.id
    LEFT JOIN images i ON pp.id = i.plants_property_id
    WHERE pp.deleted_at IS NULL $searchQuery
    ORDER BY pp.created_at DESC
");
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
                                    <label for="description" class="form-label">Descrição</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
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

                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Planta</th>
                                        <th>Propriedade</th>
                                        <th>Descrição</th>
                                        <th>Imagem</th>
                                        <th>Fonte da Imagem</th>
                                        <th>Criado em</th>
                                        <th>Deletado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($propertiesQuery) > 0) {
                                        while ($row = mysqli_fetch_assoc($propertiesQuery)) {

                                            $created_at = date("d/m/Y H:i", strtotime($row['created_at']));
                                            $deleted_at = $row['deleted_at'] ? date("d/m/Y H:i", strtotime($row['deleted_at'])) : 'Ativo';
                                    ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['plant_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['property_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                <td>
                                                    <?php if ($row['imagem']) { ?>
                                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagem']); ?>" alt="Imagem" style="width: 100px; height: auto;">
                                                    <?php } else { ?>
                                                        Sem Imagem
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['image_source']); ?></td>
                                                <td><?php echo htmlspecialchars($created_at); ?></td>
                                                <td><?php echo htmlspecialchars($deleted_at); ?></td>
                                                <td>

                                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editPropertyModal"
                                                        data-id="<?php echo htmlspecialchars($row['id']); ?>"
                                                        data-plant_id="<?php echo htmlspecialchars($row['plant_id']); ?>"
                                                        data-property_id="<?php echo htmlspecialchars($row['property_id']); ?>"
                                                        data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                                        data-source="<?php echo htmlspecialchars($row['image_source']); ?>">
                                                        Editar
                                                    </button>

                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                        Excluir
                                                    </button>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center'>Nenhuma imagem encontrada.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Você realmente deseja excluir esta imagem?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_property" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                            <label for="editDescription" class="form-label">Descrição</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editSource" class="form-label">Fonte da Imagem</label>
                            <input type="text" class="form-control" id="editSource" name="source" required>
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

        document.addEventListener('DOMContentLoaded', function() {
            var deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#confirmDeleteModal"]');
            var deleteIdInput = document.getElementById('deleteId');

            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    deleteIdInput.value = id;
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var editButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#editPropertyModal"]');
            var editPropertyIdInput = document.getElementById('editPropertyId');
            var editPlantIdSelect = document.getElementById('editPlantId');
            var editPropertyIdSelect = document.getElementById('editPropertyIdSelect');
            var editDescriptionTextarea = document.getElementById('editDescription');
            var editSourceInput = document.getElementById('editSource');

            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var plant_id = this.getAttribute('data-plant_id');
                    var property_id = this.getAttribute('data-property_id');
                    var description = this.getAttribute('data-description');
                    var source = this.getAttribute('data-source');

                    editPropertyIdInput.value = id;
                    editPlantIdSelect.value = plant_id;
                    editPropertyIdSelect.value = property_id;
                    editDescriptionTextarea.value = description;
                    editSourceInput.value = source;
                });
            });
        });
    </script>
</body>

</html>
