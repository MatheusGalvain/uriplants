<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    include_once('includes/config.php');

    if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
        header('Location: logout.php');
        exit();
    }

    $userId = $_SESSION['id'];

    $query = mysqli_query($con, "SELECT * FROM users WHERE id='$userId'");
    $result = mysqli_fetch_array($query);

    $isAdmin = $result['is_administrator'] == 1;

    if (!$isAdmin) {
        http_response_code(403); // Forbidden
        echo json_encode(['error' => 'Acesso negado.']);
        exit();
    }

    $sql = "SELECT * FROM AuditLogs ORDER BY change_time DESC";
    $result = mysqli_query($con, $sql);

    $logs = [];
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
    }

    echo json_encode($logs);

    mysqli_close($con);
?>
