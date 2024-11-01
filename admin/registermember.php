<?php
require_once('includes/config.php');

check_user_session();

$message = '';

if (isset($_POST['submit'])) {
    $fname = $_POST['fname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmpassword'];
    $is_administrator = $_POST['is_administrator'];

    if ($password !== $confirm_password) {
        $message = 'A senha e a confirmação de senha não coincidem.';
    } elseif (strlen($password) < 6) {
        $message = 'A senha deve ter mais de 6 dígitos.';
    } else {

        $sql = mysqli_query($con, "SELECT id FROM Users WHERE email='$email'");
        $row = mysqli_num_rows($sql);

        if ($row > 0) {
            $message = 'Email já cadastrado em outra conta. Por favor, tente com outro email.';
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $msg = mysqli_query($con, "INSERT INTO Users (fname, email, password, is_administrator) VALUES ('$fname', '$email', '$hashed_password', '$is_administrator')");
            if ($msg) {
                $message = 'Registrado com sucesso';
            } else {
                $message = 'Erro ao registrar.';
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Novo Usuário</title>

    <script type="text/javascript">
        function checkpass() {
            var password = document.signup.password.value;
            var confirm_password = document.signup.confirmpassword.value;

            if (password !== confirm_password) {
                alert('A senha e a confirmação de senha não coincidem.');
                document.signup.confirmpassword.focus();
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

                    <h1 class="mt-4 mb-4">Cadastrar Novo Usuário</h1>

                    <div class="card-body">
                        <form method="post" name="signup" onsubmit="return checkpass();">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nome:</th>
                                    <td><input class="form-control" id="fname" name="fname" type="text" placeholder="Insira seu nome" required /></td>
                                </tr>
                                <tr>
                                    <th>Seu E-mail:</th>
                                    <td><input class="form-control" id="email" name="email" type="email" placeholder="exemplo@gmail.com" required /></td>
                                </tr>
                                <tr>
                                    <th>
                                        <div class="d-flex flex-column">
                                            <div>Senha:</div>
                                            <small class="fw-normal">*A senha deve possuir no mínimo 6 digitos</small>
                                        </div>
                                    </th>
                                    <td><input class="form-control" id="password" name="password" type="password" placeholder="Senha" pattern="[a-zA-Z0-9]{6,}" title="A senha deve ter pelo menos 6 caracteres, incluindo letras e números." required /></td>
                                </tr>
                                <tr>
                                    <th>Confirmar Senha:</th>
                                    <td><input class="form-control" id="confirmpassword" name="confirmpassword" type="password" placeholder="Confirmar Senha" pattern="[a-zA-Z0-9]{6,}" title="A confirmação da senha deve ter pelo menos 6 caracteres, incluindo letras e números." required /></td>
                                </tr>
                                <tr>
                                    <!-- Isso tá aqui pra não fazer nada mesmo, só pra mostrar que todos podem add plantas -->
                                    <th>Pode cadastrar plantas</th>
                                    <td>
                                        <select class="form-control" id="is_administrator" name="is_administrator">
                                            <option value="1">Sim</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Pode cadastrar mais usuários</th>
                                    <td>
                                        <select class="form-control" id="is_administrator" name="is_administrator">
                                            <option value="0">Não</option>
                                            <option value="1">Sim</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <div class="d-flex justify-content-center gap-3 ">
                                <button type="submit" class="btn btn-primary mb-3" name="submit">Criar Conta</button>
                                <a href="manage-users.php" class="btn btn-secondary mb-3">Voltar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo usuário</h5>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" defer></script>
    <script>
        window.addEventListener('DOMContentLoaded', event => {
            const datatablesSimple = document.getElementById('datatablesSimple');
            if (datatablesSimple) {
                new simpleDatatables.DataTable(datatablesSimple, {
                    labels: {
                        placeholder: "Buscar...",
                        perPage: "por página",
                        noRows: "Nenhum registro encontrado",
                        info: "Mostrando {start} a {end} de {rows} entradas",
                        noResults: "Nenhum resultado correspondente",
                        perPageSelect: "entradas"
                    }
                });
            }
        });

        function showMessageModal(message) {
            const modalBody = document.getElementById('messageModalBody');
            modalBody.textContent = message;
            const modalElement = document.getElementById('messageModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    </script>

    <?php if (!empty($message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showMessageModal('<?php echo addslashes($message); ?>');
            });
        </script>
    <?php endif; ?>

</body>

</html>