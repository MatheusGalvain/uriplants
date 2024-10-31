<?php
include_once('includes/config.php');

check_user_session();

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $con->prepare("SELECT user_id FROM password_resets WHERE token = ?");
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

                $stmt = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();

                $stmt = $con->prepare("DELETE FROM password_resets WHERE token = ?");
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
</head>

<body>
    <main>
        <section id="main-admin">
            <div class="main-admin-right">
                <div class="right-content">
                    <h1>Redefinir Senha</h1>
                    <p>Por favor, insira sua nova senha abaixo.</p>
                </div>
                <div id="content-btns">
                    <?php
                    if (isset($error_message)) {
                        echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
                    }
                    ?>
                    <form class="custom-form" method="post">
                        <div class="form-group">
                            <label class="form-label" for="password">Nova Senha:</label>
                            <input class="form-input" type="password" name="password" id="password" required />
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirmar Nova Senha:</label>
                            <input class="form-input" type="password" name="confirm_password" id="confirm_password" required />
                        </div>
                        <div class="form-actions">
                            <button class="submit-btn" name="reset_password" type="submit">Redefinir Senha</button>
                        </div>
                    </form>
                    <a class="inittext" href="index.php">Voltar para o início</a>
                </div>
            </div>
        </section>
    </main>
</body>

</html>