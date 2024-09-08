<?php

$servername = "localhost";  
$username = "root";         
$password = "";            
$dbname = "test"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$name = "Rosa";
$division_id = 1;
$class_id = 2;
$order_id = 3;
$family_id = 4;
$genus_id = 5;
$species = "Rosa rubiginosa";
$biological_form = "Arbusto";
$region_id = 1;
$applications = "Paisagismo, medicinal";


$sql = "INSERT INTO Plants (name, division_id, class_id, order_id, family_id, genus_id, species, biological_form, region_id, applications) 
VALUES ('$name', $division_id, $class_id, $order_id, $family_id, $genus_id, '$species', '$biological_form', $region_id, '$applications')";

if ($conn->query($sql) === TRUE) {
    echo "Novo registro inserido com sucesso!";
} else {
    echo "Erro: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
