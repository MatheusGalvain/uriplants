<?php 
include_once('includes/config.php');

check_user_session();

$message = '';

if (isset($_POST['update'])) {
    $fname = $_POST['fname'];
    $email = $_POST['email'];
    $userid = $_SESSION['id'];

    if ($stmt = $con->prepare("UPDATE users SET fname = ?, email = ? WHERE id = ?")) {
        $stmt->bind_param("ssi", $fname, $email, $userid);
        if ($stmt->execute()) {
            $message = 'Perfil atualizado com sucesso!';
        } else {
            $message = 'Erro ao atualizar o perfil. Tente novamente.';
        }
        $stmt->close();
    } else {
        $message = 'Erro na preparação da consulta.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Editar Perfil</title>

</head>
<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <?php 
                    $userid = $_SESSION['id'];

                    if ($stmt = $con->prepare("SELECT fname, email FROM users WHERE id = ?")) {
                        $stmt->bind_param("i", $userid);
                        $stmt->execute();
                        $stmt->bind_result($fname_db, $email_db);
                        $stmt->fetch();
                        $stmt->close();
                    }
                    ?>
                    <h1 class="mt-4 mb-4">Editar meu perfil</h1>

                    <div class="card mb-4">
                        <form method="post">
                            <table class="table">
                                <tr>
                                    <th style="width: 20%;">Nome:</th>
                                    <td>
                                        <input class="form-control" id="fname" name="fname" type="text" value="<?php echo htmlspecialchars($fname_db); ?>" required />
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td >
                                        <input class="form-control" id="email" name="email" type="email" value="<?php echo htmlspecialchars($email_db); ?>" required />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div>
                                            <button type="submit" class="btn btn-primary mt-4" name="update">Atualizar</button>
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

    <!-- Modal de Mensagem -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notificação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body" id="messageModalBody">

                </div>
                <div class="modal-footer">
                    <?php if ($message === 'Perfil atualizado com sucesso!'): ?>
                        <a href="profile.php" class="btn btn-primary">Ir para Perfil</a>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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
