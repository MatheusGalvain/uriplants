<?php
include_once('functions/session.php');
require_once('functions/audit.php');

start_session_if_none();

require_once __DIR__ . '/../../config/database.php';

$con = getConnection();

if ($con->connect_error) {
    die("Falha na conexão: " . $con->connect_error);
}
