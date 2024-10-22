<?php

function check_user_session() {
    if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
        header('Location: logout.php');
        exit();
    }
}

function start_session_if_none() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}


