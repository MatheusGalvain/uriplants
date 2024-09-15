<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
};
require_once('includes/config.php');

// Código para Registro
if (isset($_POST['submit'])) {
    $fname = $_POST['fname'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirmpassword']);
    $is_administrator = $_POST['is_administrator'];

    // Verifica se as senhas coincidem
    if ($password !== $confirm_password) {
        echo "<script>alert('A senha e a confirmação de senha não coincidem.');</script>";
    } elseif (strlen($password) < 6) {
        echo "<script>alert('A senha deve ter mais de 6 dígitos.');</script>";
    } else {
        // Verifica se o email já existe
        $sql = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
        $row = mysqli_num_rows($sql);

        if ($row > 0) {
            echo "<script>alert('Email já cadastrado em outra conta. Por favor, tente com outro email.');</script>";
        } else {
            // Insere o novo usuário
            $msg = mysqli_query($con, "INSERT INTO users (fname, email, password, is_administrator) VALUES ('$fname', '$email', '$password', '$is_administrator')");
            if ($msg) {
                echo "<script>alert('Registrado com sucesso');</script>";
                echo "<script type='text/javascript'>document.location = 'manage-users.php';</script>";
            } else {
                echo "<script>alert('Erro ao registrar.');</script>";
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
            if (password.length !== 6) {
                alert('A senha deve ter exatamente 6 dígitos.');
                document.signup.password.focus();
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
                <a href="manage-users.php" class="btn btn-outline-primary mt-4"> Voltar</a>
                    <h1 class="mt-4 mb-4">Cadastrar Novo Usuário</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item">Você está em: </li>
                        <li class="breadcrumb-item"><a href="welcome.php">dashboard</a></li>
                        <li class="breadcrumb-item active">cadastro de usuário</li>
                    </ol>
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
                                        <th>Senha:</th>
                                        <td><input class="form-control" id="password" name="password" type="password" placeholder="Senha" pattern="\d{6}" title="A senha deve ter exatamente 6 dígitos." required /></td>
                                    </tr>
                                    <tr>
                                        <th>Confirmar Senha:</th>
                                        <td><input class="form-control" id="confirmpassword" name="confirmpassword" type="password" placeholder="Confirmar Senha" pattern="\d{6}" title="A confirmação da senha deve ter exatamente 6 dígitos." required /></td>
                                    </tr>
                                    <tr>
                                        <th>É Administrador?</th>
                                        <td>
                                            <select class="form-control" id="is_administrator" name="is_administrator">
                                                <option value="0">Não</option>
                                                <option value="1">Sim</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                                <div class="d-flex justify-content-center">
                                    <button type="submit" class="btn btn-dark mb-3" name="submit">Criar Conta</button>
                                </div>
                            </form>
                        </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</body>

</html>
