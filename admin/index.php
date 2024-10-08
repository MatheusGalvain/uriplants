<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once('includes/config.php');

if (isset($_POST['login'])) {
    $password = $_POST['password'];
    $useremail = $_POST['uemail'];

    // Consulta o banco de dados para obter as informações do usuário
    $ret = mysqli_query($con, "SELECT id, fname, password FROM users WHERE email='$useremail'");
    $num = mysqli_fetch_array($ret);

    if ($num > 0) {
        // Verifica se a senha inserida corresponde ao hash armazenado
        if (password_verify($password, $num['password'])) {
            // Se a senha estiver correta, inicia a sessão
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
    <!-- Includes -->
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
                    <!-- Ajustar href -->
                    <a class="returnbtn" href="#" alt="Link do Site"> 
                    <i class="fas fa-angle-left"></i>
                    Voltar ao site</a>
                    <div class="container-return">
                        <h1>Bem Vindo de volta ao admin</h1>
                        <h2>URI Plants | Login</h2>
                    </div>
                </article>
                <form class="custom-form" method="post">
                    <div class="form-group">
                        <input class="form-input" placeholder="E-mail" name="uemail" id="inputEmail" required/>
                        <!-- <label class="form-label" for="inputEmail">Endereço de E-mail:</label> -->
                    </div>
                    <div class="form-group">
                        <!-- <label class="form-label" for="inputPassword">Senha:</label> -->
                        <input class="form-input" placeholder="Senha" name="password" type="password" id="inputPassword" required />
                    </div>
                    <div class="form-actions">
                        <button class="submit-btn" name="login" type="submit">Login</button>
                        <a class="forgot-password" href="password-recovery.php">Esqueceu a Senha?</a>
                    </div>
                </form>
                <div class="logo-wrapp">
                    <div class="logocontainer">
                        <!-- Ajustar href para ir pro site-->
                        <img href="#" class="logoadmin" src="images/logouri.png" alt="Logo da URI">
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>