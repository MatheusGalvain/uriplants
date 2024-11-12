<?php
include_once('includes/config.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $con->prepare("SELECT user_id FROM PasswordResets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset = $result->fetch_assoc();

    if ($reset) {
        if (isset($_POST['reset_password'])) {

            $new_password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password && strlen($new_password) >= 6) {

                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $user_id = $reset['user_id'];

                $stmt = $con->prepare("UPDATE Users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();

                $stmt = $con->prepare("DELETE FROM PasswordResets WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();

                echo "<script>alert('Senha redefinida com sucesso.'); window.location.href='login.php';</script>";
                exit();
            } else {
                $error_message = "As senhas não coincidem ou são muito curtas (mínimo de 6 caracteres).";
            }
        }
    } else {
        echo "<script>alert('Link de redefinição inválido ou expirado.'); window.location.href='password-recovery.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Token não fornecido.'); window.location.href='password-recovery.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Redefinir Senha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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
                    <a class="returnbtn" href="admin.php" alt="Link do Site">
                        <i class="fas fa-angle-left"></i>
                        Voltar para o login
                    </a>
                    <div class="container-return">
                        <h2>Redefinir Senha</h1>
                        <h1>Por favor, insira sua nova senha abaixo.</p>
                    </div>
                    <div id="content-btns">
                        <?php
                        if (isset($error_message)) {
                            echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
                        }
                        ?>
                    </div>
                </article>
                <form class="custom-form" method="post">
                    <div class="form-group">
                        <input class="form-input" placeholder="Nova senha" type="password" name="password" id="password" required />
                    </div>
                    <div class="form-group">
                        <input class="form-input" placeholder="Confirme a nova senha" type="password" name="confirm_password" id="confirm_password" required />
                    </div>
                    <div class="form-actions">
                        <button class="submit-btn" name="reset_password" type="submit">Redefinir Senha</button>
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