<?php
include_once('includes/session.php');

start_session_if_none();

require_once __DIR__ . '/../../config/database.php';

$con = getConnection();

if ($con->connect_error) {
    die("Falha na conexão: " . $con->connect_error);
}
?>
