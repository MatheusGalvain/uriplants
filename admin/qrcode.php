<?php
include_once('includes/config.php');
require_once('functions/audit.php');

check_user_session();

if (strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

if (isset($_POST['edit_url'])) {
    $id = intval($_POST['id']);
    $url = mysqli_real_escape_string($con, $_POST['url']);

    $query = mysqli_query($con, "SELECT url FROM qrcode_url WHERE id = 1");

    if (mysqli_num_rows($query) > 0) {

        $old_row = mysqli_fetch_assoc($query);
        $old_url = $old_row['url'];

        $sql = "UPDATE qrcode_url SET url = '$url' WHERE id = 1";
        if (mysqli_query($con, $sql)) {
            $success = "URL atualizado com sucesso.";

            $table = 'QR Code URL';
            $action_id = 2;
            $changed_by = $_SESSION['id'];
            $old_value = $old_url;
            $new_value = $url;
            $plant_id = null;

            log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);
        } else {
            $error = "Erro ao atualizar a URL: " . mysqli_error($con);
        }
    } else {

        $sql = "INSERT INTO qrcode_url (id, url) VALUES (1, '$url')";
        if (mysqli_query($con, $sql)) {
            $success = "URL inserida com sucesso.";

            $table = 'QR Code URL';
            $action_id = 1;
            $changed_by = $_SESSION['id'];
            $old_value = null;
            $new_value = $url;
            $plant_id = null;

            log_audit($con, $table, $action_id, $changed_by, $old_value, $new_value, $plant_id);
        } else {
            $error = "Erro ao inserir a URL: " . mysqli_error($con);
        }
    }
}

$urlQuery = mysqli_query($con, "SELECT url FROM qrcode_url WHERE id = 1");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | QRCode URL</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar URL</h1>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Editar URL</h5>
                            <form method="POST" action="">

                                <input type="hidden" name="id" value="1">

                                <div class="mb-3">
                                    <label for="url" class="form-label">Novo endereço</label>
                                    <input type="text" class="form-control" id="url" name="url" required>
                                </div>
                                <button type="submit" name="edit_url" class="btn btn-primary">Salvar</button>
                            </form>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>URL Cadastrada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_array($urlQuery)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['url']); ?></td>

                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
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
            var editButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#editClassModal"]');
            var editIdInput = document.getElementById('editId');
            var editNameInput = document.getElementById('editName');

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