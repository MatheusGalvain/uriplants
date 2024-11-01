<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once('includes/config.php');

if (isset($_POST['login'])) {
    $password = $_POST['password'];
    $useremail = $_POST['uemail'];

    $ret = mysqli_query($con, "SELECT id, fname, password FROM Users WHERE email='$useremail'");
    $num = mysqli_fetch_array($ret);

    if ($num > 0) {
        if (password_verify($password, $num['password'])) {
            $_SESSION['id'] = $num['id'];
            $_SESSION['name'] = $num['fname'];
            header("location:welcome.php");
        } else {
            echo "<script>alert('Endereço de E-mail/Senha inválida');</script>";
        }
    } else {
        echo "<script>alert('Endereço de E-mail/Senha inválida');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Admin | Lobby</title>
    <link href="css/reset.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/index.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>

<body>
    <main>
        <section id="main-admin">
            <div class="container">
                <article class="title-admin">
                    <a class="returnbtn" href="./../index.php" alt="Link do Site">
                        <i class="fas fa-angle-left"></i>
                        Voltar ao site</a>
                    <div class="container-return">
                        <h1>Bem-vindo de volta ao admin</h1>
                        <h2>URI Plants | Login</h2>
                    </div>
                </article>
                <form class="custom-form" method="post">
                    <div class="form-group">
                        <input class="form-input" placeholder="E-mail" name="uemail" id="inputEmail" required />
                    </div>
                    <div class="form-group">
                        <input class="form-input" placeholder="Senha" name="password" type="password" id="inputPassword" required />
                    </div>
                    <div class="form-actions">
                        <button class="submit-btn" name="login" type="submit">Login</button>
                        <a class="forgot-password" href="password-recovery.php">Esqueceu a Senha?</a>
                    </div>
                </form>
                <div class="logo-wrapp">
                    <div class="logocontainer">
                        <img class="logoadmin" src="images/logouri.png" alt="Logo da URI">
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>

</html>