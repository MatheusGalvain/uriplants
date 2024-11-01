<?php
include_once('includes/config.php');
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['recover'])) {
    $useremail = $_POST['uemail'];

    $stmt = $con->prepare("SELECT id, fname FROM Users WHERE email = ?");
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

        // TODO: ALTERAR PARA URL CORRETA
        $resetLink = "http://localhost/uriplants/admin/password-reset.php?token=" . $token;

        $mail = new PHPMailer(true);

        try {
            // TODO: CONFIGURAÇÕES DO SERVIDOR DE E-MAIL
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'uriplantaspi3@gmail.com';   // TODO: Endereço de e-mail 
            $mail->Password = 'xcpp nbia xrnl liyi';        // TODO: Senha de app - FAVOR NÃO DEIXAR AQUI QUEM FOR BOTAR ISSO EM PROD
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->setFrom('uriplantaspi3@gmail.com', 'URI Plantas'); // TODO: Altera o remetente
            $mail->addAddress($useremail, $num['fname']);

            // TODO: Conteúdo do e-mail - personalizar conforme necessário
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
            $_SESSION['message'] = 'Um e-mail de recuperação de senha foi enviado para o endereço fornecido.';
            header('Location: admin.php');
        } catch (Exception $e) {
            echo "<script>alert('Falha ao enviar o e-mail. Erro: {$mail->ErrorInfo}');</script>";
        }
    } else {
        echo "<script>alert('Endereço de e-mail não encontrado.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Recuperação de Senha</title>
    <link href="css/reset.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/index.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <style>
        .sending-btn {
            width: 368px;
            display: none;
            cursor: not-allowed;
            background-color: #6c757d;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <main>
        <section id="main-admin">
            <div class="container">
                <article class="title-admin">
                    <!-- TODO: Ajustar href -->
                    <a class="returnbtn" href="admin.php" alt="Link do Site">
                        <i class="fas fa-angle-left"></i>
                        Voltar para o login
                    </a>
                    <div class="container-return">
                        <h2>Recuperação de Senha</h2>
                        <h1>Perdeu a senha? Não se preocupe! Estamos prontos para ajudar. Informe seu e-mail abaixo.</h1>
                    </div>
                </article>
                <form class="custom-form" method="post">
                    <div class="form-group">
                        <input class="form-input" placeholder="Informe seu E-mail" type="email" name="uemail" id="inputEmail" required />
                    </div>
                    <div class="form-actions">
                        <button class="submit-btn" name="recover" type="submit">Enviar E-mail</button>
                        <button class="sending-btn" type="button" disabled>Enviando...</button>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.custom-form');
            const submitBtn = form.querySelector('.submit-btn');
            const sendingBtn = form.querySelector('.sending-btn');

            form.addEventListener('submit', function() {
                submitBtn.style.display = 'none';
                sendingBtn.style.display = 'inline-block';
            });
        });
    </script>
</body>

</html>