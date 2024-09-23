<?php
session_start();
include_once('includes/config.php');
require_once('includes/audit.php'); 

if (strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

if (isset($_POST['add_region'])) {

    $name = mysqli_real_escape_string($con, $_POST['name']); 
    $source = mysqli_real_escape_string($con, $_POST['source']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
        $image_hash = md5($image);
        

        $check_name_sql = "SELECT COUNT(*) FROM RegionMap WHERE name = ?";
        $stmt = mysqli_prepare($con, $check_name_sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $name);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $name_count);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
            
            if ($name_count > 0) {
                $error = "Erro: Já existe uma região com o nome '$name'.";

            }
        } else {
            $error = "Erro na preparação da consulta para verificação de nome: " . mysqli_error($con);
        }
        
        if (!isset($error)) {
            $sql = "INSERT INTO RegionMap (name, imagem, source, description) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $sql);
            
            if ($stmt) {

                mysqli_stmt_bind_param($stmt, 'sbss', $name, $null, $source, $description);
                
                mysqli_stmt_send_long_data($stmt, 1, $image);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Região adicionada com sucesso.";
                    
                    $new_region_id = mysqli_insert_id($con);
                    
                    $table = 'RegionMap';
                    $action_id = 1; 
                    $changed_by = $_SESSION['id'];
                    $old_value = null; 
                    $new_value = "ID: $new_region_id, Nome: $name, Fonte: $source, Descrição: $description";
                    $plant_id = null;
                    
                    log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);
                    
                } else {
                    $error = "Erro ao adicionar região: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = "Erro na preparação da consulta de inserção: " . mysqli_error($con);
            }
        }
    } else {
        $error = "Por favor, selecione uma imagem.";
    }
}


if (isset($_POST['edit_region'])) {

    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $source = mysqli_real_escape_string($con, $_POST['source']);
    $description = mysqli_real_escape_string($con, $_POST['description']);

    $query = mysqli_query($con, "SELECT * FROM RegionMap WHERE name='$name' AND id != $id");
    if (mysqli_num_rows($query) > 0) {
        $error = "Uma região com esse nome já existe.";
    } else {
        $old_query = mysqli_query($con, "SELECT name, source, description, imagem FROM RegionMap WHERE id = $id");
        $old_row = mysqli_fetch_assoc($old_query);
        $old_name = $old_row['name'];
        $old_source = $old_row['source'];
        $old_description = $old_row['description'];
        $old_image = $old_row['imagem'];


        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                $error = "Tipo de imagem não permitido. Apenas JPEG, PNG e GIF são aceitos.";
            } else {

                $new_image = file_get_contents($_FILES['image']['tmp_name']);
                
            }
        }

        $sql = "UPDATE RegionMap SET name = ?, imagem = ?, source = ?, description = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);


        if (empty($new_image)) {

            $sql = "UPDATE RegionMap SET name = ?, source = ?, description = ? WHERE id = ?";
            $stmt = mysqli_prepare($con, $sql);
            if ($stmt) {

                mysqli_stmt_bind_param($stmt, 'sssi', $name, $source, $description, $id);
            }
        } else {

            $sql = "UPDATE RegionMap SET name = ?, imagem = ?, source = ?, description = ? WHERE id = ?";
            $stmt = mysqli_prepare($con, $sql);
            
            if ($stmt) {

                mysqli_stmt_bind_param($stmt, 'sbssi', $name, $null, $source, $description, $id);
                
                mysqli_stmt_send_long_data($stmt, 1, $new_image); 
            }
        }

        if ($stmt) {
            if (mysqli_stmt_execute($stmt)) {
                $success = "Região atualizada com sucesso.";

                $table = 'RegionMap';
                $action_id = 3;
                $changed_by = $_SESSION['id'];
                $old_value = "Nome: $old_name, Fonte: $old_source, Descrição: $old_description";
                $new_value = "Nome: $name, Fonte: $source, Descrição: $description";
                if (!empty($new_image)) {
                    $new_value .= ", Nova imagem inserida na edição";
                }
                $plant_id = null;

                log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);
            } else {
                $error = "Erro ao atualizar região: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Erro na preparação da consulta: " . mysqli_error($con);
        }


    }
}

if (isset($_POST['delete_region'])) {
    $id = intval($_POST['id']);

    $old_query = mysqli_query($con, "SELECT name, source, description FROM RegionMap WHERE id = $id");
    $old_row = mysqli_fetch_assoc($old_query);
    $old_name = $old_row['name'];
    $old_source = $old_row['source'];
    $old_description = $old_row['description'];

    $sql = "DELETE FROM RegionMap WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        $success = "Região excluída com sucesso.";

        $table = 'RegionMap';
        $action_id = 2; 
        $changed_by = $_SESSION['id'];
        $old_value = "Nome: $old_name, Fonte: $old_source, Descrição: $old_description, Imagem deletada";
        $new_value = null;
        $plant_id = null;

        log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);

    } else {
        $error = "Erro ao excluir região: " . mysqli_error($con);
    }
    mysqli_stmt_close($stmt);
}


$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';


$searchQuery = $search ? "AND source LIKE '%$search%'" : "";
$regionsQuery = mysqli_query($con, "SELECT * FROM RegionMap WHERE 1=1 $searchQuery");
?>

<!DOCTYPE html>
<html lang="pt-BR"> 
<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Regiões</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Regiões</h1>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Adicionar uma Nova Região</h5>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="source" class="form-label">Fonte</label>
                                    <input type="text" class="form-control" id="source" name="source" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Descrição</label>
                                    <input type="text" class="form-control" id="description" name="description" required>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Imagem</label>
                                    <input type="file" class="form-control" id="image" name="image" required>
                                </div>
                                <button type="submit" name="add_region" class="btn btn-primary">Adicionar região</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Regiões Cadastradas</h5>
                                <form method="POST" action="" class="d-flex">
                                    <input type="text" class="form-control me-2" name="search" placeholder="Buscar regiões" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" type="submit">Buscar</button>
                                    <?php if ($search) { ?>
                                        <a href="regions.php" class="btn btn-secondary ms-2">Remover Filtro</a>
                                    <?php } ?>
                                </form>
                            </div>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Imagem</th>
                                        <th>Fonte</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($regionsQuery) > 0): ?>
                                        <?php while ($row = mysqli_fetch_array($regionsQuery)) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                <td>
                                                    <?php if ($row['imagem']) { ?>
                                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagem']); ?>" alt="Imagem" style="width: 100px; height: auto;">
                                                    <?php } else { ?>
                                                        N/A
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['source']); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editRegionModal"
                                                        data-id="<?php echo htmlspecialchars($row['id']); ?>"
                                                        data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                        data-source="<?php echo htmlspecialchars($row['source']); ?>"
                                                        data-description="<?php echo htmlspecialchars($row['description']); ?>">
                                                        Editar
                                                    </button>

                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal"
                                                        data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                        Excluir
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhuma região encontrada.</td>
                                        </tr>
                                    <?php endif; ?>
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
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Você realmente deseja excluir esta região?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_region" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editRegionModal" tabindex="-1" aria-labelledby="editRegionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRegionModalLabel">Editar Região</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editRegionId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSource" class="form-label">Fonte</label>
                            <input type="text" class="form-control" id="editSource" name="source" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Descrição</label>
                            <input type="text" class="form-control" id="editDescription" name="description" required>
                        </div>
                        <div class="mb-3">
                            <label for="editImage" class="form-label">Imagem (deixe em branco para não alterar)</label>
                            <input type="file" class="form-control" id="editImage" name="image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="edit_region" class="btn btn-primary">Salvar Alterações</button>
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
            var editButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#editRegionModal"]');
            var editRegionIdInput = document.getElementById('editRegionId');
            var editNameInput = document.getElementById('editName');
            var editSourceInput = document.getElementById('editSource');
            var editDescriptionInput = document.getElementById('editDescription');

            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var name = this.getAttribute('data-name');
                    var source = this.getAttribute('data-source');
                    var description = this.getAttribute('data-description');

                    editRegionIdInput.value = id;
                    editNameInput.value = name;
                    editSourceInput.value = source;
                    editDescriptionInput.value = description;
                });
            });
        });
    </script>
</body>

</html>
