<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
};
include_once('includes/config.php');

// Verificar se a sessão está ativa
if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
    header('Location: logout.php');
    exit;
}

// Verificar se o formulário foi enviado
if (isset($_POST['update'])) {
    $oldpassword = md5($_POST['currentpassword']);
    $newpassword = md5($_POST['newpassword']);

    // Escapar as entradas para prevenir SQL Injection
    $oldpassword = mysqli_real_escape_string($con, $oldpassword);
    $newpassword = mysqli_real_escape_string($con, $newpassword);
    $userid = $_SESSION['id'];

    // Buscar a senha atual do usuário
    $sql = mysqli_query($con, "SELECT password FROM users WHERE id='$userid'");
    $num = mysqli_fetch_array($sql);

    if ($num && password_verify($oldpassword, $num['password'])) {
        // Atualizar a senha (armazenar hashes de senha)
        $hashed_password = password_hash($newpassword, PASSWORD_DEFAULT);
        $ret = mysqli_query($con, "UPDATE users SET password='$hashed_password' WHERE id='$userid'");

        echo "<script>alert('Senha alterada com sucesso!');</script>";
        echo "<script type='text/javascript'>document.location = 'change-password.php';</script>";
    } else {
        echo "<script>alert('A senha atual não corresponde!');</script>";
        echo "<script type='text/javascript'>document.location = 'change-password.php';</script>";
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
                alert("A senha e a confirmação de senha não coincidem!");
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
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item">Você está em: </li>
                        <li class="breadcrumb-item"><a href="profile.php">meu perfil</a></li>
                        <li class="breadcrumb-item active">alterar minha senha</li>
                    </ol>
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
                                        <td colspan="2" style="text-align:center;"><button type="submit" class="btn btn-primary btn-block" name="update">Mudar Senha</button></td>
                                    </tr>
                                </table>
                        </form>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</body>

</html>
