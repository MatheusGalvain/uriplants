<?php

function getConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "Arboreauri2k24#";
    $dbname = "uriplants";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

    return $conn;
}
?>
