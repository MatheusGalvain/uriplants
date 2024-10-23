<?php
include_once('includes/config.php');
require_once('functions/audit.php');

check_user_session();

if (isset($_POST['add_order'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);

    $query = mysqli_query($con, "SELECT * FROM orders WHERE name='$name'");
    if (mysqli_num_rows($query) > 0) {
        $error = "Uma ordem com esse nome já existe.";
    } else {

        $sql = "INSERT INTO orders (name) VALUES ('$name')";
        if (mysqli_query($con, $sql)) {
            $success = "Ordem adicionada com sucesso.";

            $new_class_id = mysqli_insert_id($con);

            $table = 'Ordem';
            $action_id = 1; 
            $changed_by = $_SESSION['id'];
            $old_value = null; 
            $new_value = "ID: $new_class_id, Nome: $name";
            $plant_id = null;

            log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);

        } else {
            $error = "Erro ao adicionar ordem: " . mysqli_error($con);
        }
    }
}

if (isset($_POST['delete_order'])) {
    $id = intval($_POST['id']);

    $old_query = mysqli_query($con, "SELECT name FROM orders WHERE id = $id");
    $old_row = mysqli_fetch_assoc($old_query);
    $old_name = $old_row['name'];

    $sql = "UPDATE orders SET deleted_at = NOW() WHERE id = $id";
    if (mysqli_query($con, $sql)) {
        $success = "Ordem excluída com sucesso.";

        $table = 'Ordem';
        $action_id = 2; 
        $changed_by = $_SESSION['id'];
        $old_value = "Nome: $old_name";
        $new_value = null; 
        $plant_id = null; 

        log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);

    } else {
        $error = "Erro ao excluir ordem: " . mysqli_error($con);
    }
}

if (isset($_POST['edit_order'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);

    $old_query = mysqli_query($con, "SELECT name FROM orders WHERE id = $id");
    $old_row = mysqli_fetch_assoc($old_query);
    $old_name = $old_row['name'];


    $sql = "UPDATE orders SET name = '$name' WHERE id = $id";
    if (mysqli_query($con, $sql)) {
        $success = "Ordem atualizada com sucesso.";

        $table = 'Ordem';
        $action_id = 3; 
        $changed_by = $_SESSION['id'];
        $old_value = "$old_name";
        $new_value = "$name";
        $plant_id = null; 

        log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);
    } else {
        $error = "Erro ao atualizar ordem: " . mysqli_error($con);
    }
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';

$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) as total FROM orders WHERE deleted_at IS NULL $searchQuery";

$count_result = mysqli_query($con, $count_query);
$total_logs = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = ceil($total_logs / $limit);

$searchQuery = $search ? "AND name LIKE '%$search%'" : "";
$ordersQuery = mysqli_query($con, "SELECT * FROM orders WHERE deleted_at IS NULL $searchQuery LIMIT $limit OFFSET $offset");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Ordens</title>
    <link href="css/pagination.css" rel="stylesheet" /> 
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Ordens</h1>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Adicionar uma Nova Ordem</h5>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome da Ordem</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <button type="submit" name="add_order" class="btn btn-primary">Adicionar ordem</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Ordens Cadastradas</h5>
                                <form method="GET" action="" class="d-flex">
                                    <input type="text" class="form-control me-2" name="search" placeholder="Buscar ordens" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" type="submit">Buscar</button>
                                    <?php if ($search) { ?>
                                        <a href="orders.php" class="btn btn-secondary ms-2 w-100">Remover Filtro</a>
                                    <?php } ?>
                                </form>
                            </div>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nome da Ordem</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_array($ordersQuery)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editOrderModal" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                                    Editar
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                    Excluir
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php include('includes/pagination.php'); ?> 
                            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Você realmente deseja excluir esta ordem?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_order" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editOrderModalLabel">Editar Ordem</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Nome da Ordem</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <button type="submit" name="edit_order" class="btn btn-primary">Atualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
            var deleteIdInput = document.getElementById('deleteId');
            var deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#confirmDeleteModal"]');

            var editIdInput = document.getElementById('editId');
            var editNameInput = document.getElementById('editName');
            var editButtons = document.querySelectorAll('[data-bs-target="#editOrderModal"]');
            
            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var name = this.getAttribute('data-name');
                    editIdInput.value = id;
                    editNameInput.value = name;
                });
            });

            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    deleteIdInput.value = id;
                });
            });
        });
    </script>
</body>

</html>
