<?php session_start();
    include_once('includes/config.php');
    if (isset($_POST['login'])) {
        $password = $_POST['password'];
        $dec_password = $password;
        $useremail = $_POST['uemail'];
        $ret = mysqli_query($con, "SELECT id,fname FROM users WHERE email='$useremail' and password='$dec_password'");
        $num = mysqli_fetch_array($ret);
        if ($num > 0) {
            $_SESSION['id'] = $num['id'];
            $_SESSION['name'] = $num['fname'];
            header("location:welcome.php");

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
    <title>Admin | Login</title>

    <!-- Includes -->
    <link href="css/reset.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/index.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</head>

<body>
    <main>
        <section id="main-admin">
            <div class="main-admin-left">
                <?php
                $nomeImagem = 'loginhome.jpg';
                $caminhoPasta = 'images/';
                $caminhoImagem = $caminhoPasta . $nomeImagem;
                if (file_exists($caminhoImagem)) {
                    echo '<img class="loginImg" src="' . htmlspecialchars($caminhoImagem) . '" alt="Imagem">';
                } else {
                    echo 'Imagem não encontrada.';
                }
                ?>
            </div>
            <div class="main-admin-right">
                <div class="right-content"> 
                    <h1>Coloque seus dados nos campos abaixos!</h1>
                    <h2><strong>Dica:</strong> Jamais compartilhe a senha com ninguém.</h2>
                </div>
                <div id="content-btns">
                <form class="custom-form" method="post">
                        <div class="form-group">
                            <label class="form-label" for="inputEmail">Endereço de E-mail:</label>
                            <input class="form-input" name="uemail" type="email" id="inputEmail" required />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="inputPassword">Senha:</label>
                            <input class="form-input" name="password" type="password" id="inputPassword"  required />
                        </div>
                        <div class="form-actions">
                            <a class="forgot-password" href="password-recovery.php">Perdeu a Senha?</a>
                            <button class="submit-btn" name="login" type="submit">Login</button>
                        </div>
                    </form>
                    <a class="inittext" href="index.php">Volte para o ínicio</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>