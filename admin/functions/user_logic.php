<?php
include_once('includes/config.php');

check_user_session();

$userId = $_SESSION['id'];
$query = mysqli_query($con, "SELECT * FROM users WHERE id='$userId'");
$result = mysqli_fetch_array($query);

$isAdmin = $result['is_administrator'] == 1;
