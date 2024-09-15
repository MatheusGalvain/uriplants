<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once('includes/config.php');

// Verifica se a sessão do usuário está ativa
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    header('location:logout.php');
    exit();
} else {
    $userid = $_SESSION['id'];
    // Consulta SQL segura com prepared statements
    $stmt = $con->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
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
                    <h1 class="mt-4">Bem-vindo de volta, <?php echo htmlspecialchars($user['fname']); ?></h1>
                    <hr />
                    <div class="row">
                        <?php if ($user): ?>
                            <div class="col-xl-6 col-md-12 mb-4">
                                <div class="card bg-primary text-white h-100">
                                    <div class="card-body">Bem-vindo de volta <?php echo htmlspecialchars($user['fname']); ?></div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="profile.php">Ver Perfil</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-12 mb-4">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body">Total de plantas cadastradas:</div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="profile.php">Ver Plantas Cadastradas</a>
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
