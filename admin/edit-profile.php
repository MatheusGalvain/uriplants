<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    };
    include_once('includes/config.php');

    // Verifica se o usuário está autenticado
    if (strlen($_SESSION['id']) == 0) {
        header('location:logout.php');
        exit;
    }

    // Código para atualização do perfil
    if (isset($_POST['update'])) {
        $fname = $_POST['fname'];
        $email = $_POST['email'];
        $userid = $_SESSION['id'];

        // Prepara a consulta de atualização com o e-mail
        $updateQuery = "UPDATE users SET fname='$fname', email='$email' WHERE id='$userid'";
        $msg = mysqli_query($con, $updateQuery);

        if ($msg) {
            echo "<script>alert('Perfil atualizado com sucesso');</script>";
            echo "<script type='text/javascript'>document.location = 'profile.php';</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Editar Perfil</title>
</head>
<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
            <div class="container-fluid px-4">
                <a href="profile.php" class="btn btn-outline-primary mt-4"> Voltar</a>
                <div class="container-fluid px-4">
                    <?php 
                    $userid = $_SESSION['id'];
                    $query = mysqli_query($con, "SELECT * FROM users WHERE id='$userid'");
                    
                    if ($result = mysqli_fetch_array($query)) { ?>
                        <h1 class="mt-4 mb-4">Editar meu perfil</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item">Você está em: </li>
                            <li class="breadcrumb-item"><a href="welcome.php">dashboard</a></li>
                            <li class="breadcrumb-item active">editar meu perfil</li>
                        </ol>
                        <div class="card mb-4">
                            <form method="post">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Nome:</th>
                                            <td>
                                                <input class="form-control" id="fname" name="fname" type="text" value="<?php echo htmlspecialchars($result['fname']); ?>" required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td colspan="3">
                                                <input class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($result['email']); ?>" required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" style="text-align:center;">
                                                <button type="submit" class="btn btn-primary btn-block" name="update">Atualizar</button>
                                            </td>
                                        </tr>
                                    </table>
                            </form>
                        </div>
                    <?php } ?>
                </div>
                </div>
            </main>
            
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</body>
</html>
