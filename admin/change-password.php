<?php
include_once('includes/config.php');

check_user_session();

if (isset($_POST['update'])) {
    $oldpassword = md5($_POST['currentpassword']);
    $newpassword = md5($_POST['newpassword']);

    $oldpassword = mysqli_real_escape_string($con, $oldpassword);
    $newpassword = mysqli_real_escape_string($con, $newpassword);
    $userid = $_SESSION['id'];

    $sql = mysqli_query($con, "SELECT password FROM users WHERE id='$userid'");
    $num = mysqli_fetch_array($sql);

    if ($num && password_verify($oldpassword, $num['password'])) {

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
                    <a href="profile.php" class="btn btn-outline-primary mt-4"> Voltar</a>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</body>

</html>
