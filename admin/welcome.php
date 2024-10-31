<?php
include_once('includes/config.php');

check_user_session();

$userid = $_SESSION['id'];

$stmt = $con->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Dashboard</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="h1 mt-4">Bem-vindo de volta, <span class="fw-bold"><?php echo htmlspecialchars($user['fname']); ?> </span></h1>
                    <hr />
                    <div class="">
                        <?php if ($user): ?>
                            <div class="col-xl-2 col-md-6 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-decoration-none text-white stretched-link" href="profile.php">Ver Perfil</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-decoration-none text-white stretched-link" href="plants.php">Ver Plantas Cadastradas</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</body>

</html>