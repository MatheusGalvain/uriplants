<?php

function getConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "uri_plants";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }
    echo "Conexão bem-sucedida!"; // Depuração
    return $conn;
}
?>
