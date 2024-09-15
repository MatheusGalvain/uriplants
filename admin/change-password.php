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
    $oldpassword = $_POST['currentpassword'];
    $newpassword = $_POST['newpassword'];

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
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Admin | Mudar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
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
                    <h1 class="mt-4">Mudar sua senha</h1>
                    <div class="card mb-4">
                        <form method="post" name="changepassword" onsubmit="return valid();">
                            <div class="card-body">
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
