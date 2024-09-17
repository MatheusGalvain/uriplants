<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once('includes/config.php');

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['recover'])) {
    $useremail = $_POST['uemail'];

    $stmt = $con->prepare("SELECT id, fname FROM users WHERE email = ?");
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $result = $stmt->get_result();
    $num = $result->fetch_assoc();

    if ($num) {
        $token = bin2hex(random_bytes(50));

        $user_id = $num['id'];
        $stmt = $con->prepare("INSERT INTO PasswordResets (user_id, token) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $token);
        $stmt->execute();

        //ALTERAR PARA URL CORRETA
        $resetLink = "http://localhost/uriplants/admin/password-reset.php?token=" . $token;

        $mail = new PHPMailer(true);

        try {
            // ALTERAR PARA O SERVER CORRETO DA URI
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'uriplantaspi3@gmail.com';   // endereço de e-mail 
            $mail->Password = 'xcpp nbia xrnl liyi';          // senha de app - FAVOR NÃO DEIXAR AQUI QUEM FOR BOTAR ISSO EM PROD
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port = 587; 

            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->setFrom('uriplantaspi3@gmail.com', 'URI Plantas'); // Altera o email
            $mail->addAddress($useremail, $num['fname']);

            // Conteúdo do e-mail - alterar conforme necessário
            $mail->isHTML(true); 
            $mail->Subject = 'Recuperação de Senha';
            $mail->Body    = "
                <p>Olá <strong>" . htmlspecialchars($num['fname']) . "</strong>,</p>
                <p>Clique no link abaixo para redefinir sua senha:</p>
                <p><a href='" . htmlspecialchars($resetLink) . "'>Redefinir Senha</a></p>
                <p>Se você não solicitou a alteração da senha, ignore este e-mail.</p>
                <p>Atenciosamente,<br>Equipe PI3</p>
            ";
            $mail->AltBody = "Olá " . $num['fname'] . ",\n\nClique no link abaixo para redefinir sua senha:\n" . $resetLink . "\n\nSe você não solicitou a alteração da senha, ignore este e-mail.\n\nAtenciosamente,\nSua Equipe";

            $mail->send();
            echo "<script>alert('Um e-mail de recuperação de senha foi enviado para o endereço fornecido.');</script>";
        } catch (Exception $e) {
            echo "<script>alert('Falha ao enviar o e-mail. Erro: {$mail->ErrorInfo}');</script>";
        }
    } else {
        echo "<script>alert('Endereço de e-mail não encontrado.');</script>";
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
    <title>Recuperação de Senha</title>

    <!-- Includes -->
    <link href="css/reset.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/index.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
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
                    <h1>Recuperação de Senha</h1>
                </div>
                <div id="content-btns">
                    <form class="custom-form" method="post">
                        <div class="form-group">
                            <label class="form-label" for="inputEmail">Insira o endereço de E-mail a ser recuperado:</label>
                            <input class="form-input" type="email" name="uemail" id="inputEmail" required />
                        </div>
                        <div class="form-actions">
                            <button class="submit-btn" name="recover" type="submit">Recuperar Senha</button>
                        </div>
                    </form>
                    <a class="inittext" href="index.php">Voltar</a>
                </div>
            </div>
        </section>
    </main>
</body>

</html>
