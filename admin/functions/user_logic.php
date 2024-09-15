<?php
    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include_once('includes/config.php');

    // Verifica se o usuário está autenticado
    if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
        header('Location: logout.php');
        exit();
    }

    $userId = $_SESSION['id'];
    $query = mysqli_query($con, "SELECT * FROM users WHERE id='$userId'");
    $result = mysqli_fetch_array($query);

    $isAdmin = $result['is_administrator'] == 1;
?>
