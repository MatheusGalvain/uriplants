<?php
include_once('includes/config.php');

check_user_session();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['update'])) {

        $currentPassword = $_POST['currentpassword'];
        $newPassword = $_POST['newpassword'];
        $confirmPassword = $_POST['confirmpassword'];

        if ($newPassword !== $confirmPassword) {
            $message = 'A nova senha e a confirmação não coincidem.';
        } else {

            $userid = $_SESSION['id'];

            if ($stmt = $con->prepare("SELECT password FROM users WHERE id = ?")) {
                $stmt->bind_param("i", $userid);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($hashedPasswordFromDB);
                    $stmt->fetch();

                    if (password_verify($currentPassword, $hashedPasswordFromDB)) {

                        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                        if ($updateStmt = $con->prepare("UPDATE users SET password = ? WHERE id = ?")) {
                            $updateStmt->bind_param("si", $newHashedPassword, $userid);
                            if ($updateStmt->execute()) {
                                $message = 'Senha alterada com sucesso!';
                            } else {
                                $message = 'Erro ao atualizar a senha. Tente novamente.';
                            }
                            $updateStmt->close();
                        } else {
                            $message = 'Erro na preparação da atualização da senha.';
                        }
                    } else {
                        $message = 'A senha atual não corresponde!';
                    }
                } else {
                    $message = 'Usuário não encontrado.';
                }
                $stmt->close();
            } else {
                $message = 'Erro na preparação da consulta.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Admin | Mudar Senha</title>
    <?php include_once("includes/head.php"); ?>
    <script type="text/javascript">
        function valid() {
            if (document.changepassword.newpassword.value != document.changepassword.confirmpassword.value) {
                alert("A nova senha e a confirmação de senha são diferentes!");
                document.changepassword.confirmpassword.focus();
                return false;
            }
            return true;
        }
    </script>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Mudar sua senha</h1>
                    <div class="card mb-4">
                        <form method="post" name="changepassword" onsubmit="return valid();">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Senha Atual</th>
                                    <td><input class="form-control" id="currentpassword" name="currentpassword" type="password" value="" required /></td>
                                </tr>
                                <tr>
                                    <th>Nova Senha</th>
                                    <td><input class="form-control" id="newpassword" name="newpassword" type="password" value="" required /></td>
                                </tr>
                                <tr>
                                    <th>Confirme sua senha</th>
                                    <td><input class="form-control" id="confirmpassword" name="confirmpassword" type="password" required /></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class= "d-flex gap-2">
                                            <button type="submit" class="btn btn-primary mt-4" name="update">Mudar Senha</button>
                                            <a href="profile.php" class="btn btn-secondary mt-4"> Voltar</a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                    
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notificação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="messageModalBody">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($message)): ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {

            var message = <?php echo json_encode($message); ?>;
            document.getElementById('messageModalBody').textContent = message;

            var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
        });
    </script>
    <?php endif; ?>

</body>

</html>
