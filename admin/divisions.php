<?php
include_once('includes/config.php');

check_user_session();

if (isset($_POST['add_division'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);

    $query = mysqli_query($con, "SELECT * FROM Divisions WHERE name='$name'");
    if (mysqli_num_rows($query) > 0) {
        $error = "Uma divisão com esse nome já existe.";
    } else {

        $sql = "INSERT INTO Divisions (name) VALUES ('$name')";
        if (mysqli_query($con, $sql)) {
            $success = "Divisão adicionada com sucesso.";

            $new_class_id = mysqli_insert_id($con);

            $table = 'divisions';
            $action_id = 1; 
            $changed_by = $_SESSION['id'];
            $old_value = null; 
            $new_value = "ID: $new_class_id, Nome: $name";
            $plant_id = null;

            log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);

        } else {
            $error = "Erro ao adicionar divisão: " . mysqli_error($con);
        }
    }
}

if (isset($_POST['delete_division'])) {
    $id = intval($_POST['id']);

    $old_query = mysqli_query($con, "SELECT name FROM divisions WHERE id = $id");
    $old_row = mysqli_fetch_assoc($old_query);
    $old_name = $old_row['name'];

    $sql = "UPDATE Divisions SET deleted_at = NOW() WHERE id = $id";
    if (mysqli_query($con, $sql)) {
        $success = "Divisão excluída com sucesso.";

        $table = 'divisions';
        $action_id = 2; 
        $changed_by = $_SESSION['id'];
        $old_value = "Nome: $old_name";
        $new_value = null; 
        $plant_id = null; 

        log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);

    } else {
        $error = "Erro ao excluir divisão: " . mysqli_error($con);
    }
}

if (isset($_POST['edit_division'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);

    $query = mysqli_query($con, "SELECT * FROM Divisions WHERE name='$name' AND id != $id");
    if (mysqli_num_rows($query) > 0) {
        $error = "Uma divisão com esse nome já existe.";
    } else {
        $old_query = mysqli_query($con, "SELECT name FROM divisions WHERE id = $id");
        $old_row = mysqli_fetch_assoc($old_query);
        $old_name = $old_row['name'];

        $sql = "UPDATE Divisions SET name='$name' WHERE id=$id";
        if (mysqli_query($con, $sql)) {
            $success = "Divisão editada com sucesso.";

        $table = 'divisions';
        $action_id = 3; 
        $changed_by = $_SESSION['id'];
        $old_value = "$old_name";
        $new_value = "$name";
        $plant_id = null; 

        log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);
        } else {
            $error = "Erro ao editar divisão: " . mysqli_error($con);
        }
    }
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';

$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) AS total FROM divisions WHERE deleted_at IS NULL $searchQuery";

$count_result = mysqli_query($con, $count_query);
$total_logs = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;

$total_pages = ceil($total_logs / $limit);

$searchQuery = $search ? "AND name LIKE '%$search%'" : "";
$divisionsQuery = mysqli_query($con, "SELECT * FROM Divisions WHERE deleted_at IS NULL $searchQuery LIMIT $limit OFFSET $offset");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Divisões</title>
    <link href="css/pagination.css" rel="stylesheet" />
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Divisões</h1>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Adicionar uma Nova Divisão</h5>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome da Divisão</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <button type="submit" name="add_division" class="btn btn-primary">Adicionar divisão</button>
                            </form>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Divisões Cadastradas</h5>
                            <form method="GET" action="" class="d-flex">
                                <input type="text" class="form-control me-2" name="search" placeholder="Buscar divisões" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                                <?php if ($search) { ?>
                                    <a href="divisions.php" class="btn btn-secondary ms-2 w-100">Remover Filtro</a>
                                <?php } ?>
                            </form>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nome da divisão</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($divisionsQuery)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editDivisionModal" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">
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
                <?php include('includes/pagination.php'); ?> 
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
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
                    Você realmente deseja excluir esta divisão?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_division" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editDivisionModal" tabindex="-1" aria-labelledby="editDivisionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDivisionModalLabel">Editar Divisão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Nome da Divisão</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <button type="submit" name="edit_division" class="btn btn-success">Salvar Alterações</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
            var deleteIdInput = document.getElementById('deleteId');

            var editIdInput = document.getElementById('editId');
            var editNameInput = document.getElementById('editName');
            var editButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
            
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    deleteIdInput.value = id;
                });
            });

            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var name = this.getAttribute('data-name');
                    editIdInput.value = id;
                    editNameInput.value = name;
                });
            });
        });
    </script>
</body>

</html>
