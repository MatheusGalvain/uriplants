<?php
    function loadEnv($filePath) {
        if (!file_exists($filePath)) {
            return;
        }
    
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
    
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
    
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }

loadEnv(__DIR__ . '/../.env'); 

function getConnection() {
    $servername =  $_ENV['DBSERVERNAME'];
    $username = $_ENV['DBUSERNAME'];
    $password = $_ENV['DBPW'];
    $dbname = $_ENV['DBNAME'];

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

    return $conn;
}

?>
