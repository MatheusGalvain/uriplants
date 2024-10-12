<?php
include_once('includes/config.php');

check_user_session();

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
                        <h1 class="mt-4 mb-4 h1">Perfil de <span class="fw-bold"> <?php echo htmlspecialchars($result['fname']); ?></span></h1>
                        <div class="card mb-4">
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="fw-normal">Nome do usuário:</th>
                                        <td class="fw-bold"><?php echo htmlspecialchars($result['fname']); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="fw-normal">Email:</th>
                                        <td colspan="3" class="fw-bold"><?php echo htmlspecialchars($result['email']); ?></td>
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
