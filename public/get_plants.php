<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}


header('Content-Type: application/json');


$sql = "SELECT * FROM Plants";
$result = $conn->query($sql);


if ($result->num_rows > 0) {
    $plants = array(); 

    while($row = $result->fetch_assoc()) {
        $plants[] = $row; 
    }


    echo json_encode($plants);
} else {

    echo json_encode(array());
}


$conn->close();
?>
