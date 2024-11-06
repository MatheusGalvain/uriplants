<?php
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        echo "Arquivo .env não encontrado em: $filePath";
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        $value = trim($value, '"\'');

        if (!array_key_exists($name, $_ENV) && !array_key_exists($name, $_SERVER)) {
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv("$name=$value");
        }
    }
}

$envPath = realpath(__DIR__ . '/../env');
loadEnv($envPath);

function getConnection() {
    $servername = getenv('DBSERVERNAME');
    $username = getenv('DBUSERNAME');
    $password = getenv('DBPW');
    $dbname = getenv('DBNAME');

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

    return $conn;
}
?>
