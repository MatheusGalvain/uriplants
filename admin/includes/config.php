<?php

include_once('functions/session.php');
require_once('functions/audit.php');
require_once __DIR__ . '/../../config/database.php';

start_session_if_none();

$con = getConnection();

if ($con->connect_error) {
    die("Falha na conexão: " . $con->connect_error);
}
