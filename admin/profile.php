<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    };
    include_once('includes/config.php');

    if (strlen($_SESSION['id']) == 0) {
        header('location:logout.php');
        exit();
    }

    $userId = $_SESSION['id'];
    $query = mysqli_query($con, "SELECT * FROM users WHERE id='$userId'");
    $result = mysqli_fetch_array($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Perfil</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4"><?php echo htmlspecialchars($result['fname']); ?>'s perfil</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item">Você está em: </li>
                        <li class="breadcrumb-item"><a href="welcome.php">dashboard</a></li>
                        <li class="breadcrumb-item active">meu perfil</li>
                    </ol>
                    <div class="card mb-4">
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nome do usuário:</th>
                                    <td><?php echo htmlspecialchars($result['fname']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td colspan="3"><?php echo htmlspecialchars($result['email']); ?></td>
                                </tr>
                            </table>
                            <a class="btn btn-primary btn-block" href="edit-profile.php">Editar perfil</a>
                            <a class="btn btn-primary btn-block" href="change-password.php">Mudar a senha</a>
                        </div>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</body>

</html>
