<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../controllers/PlantController.php';
require_once __DIR__ . '/../controllers/QuizzController.php';

function getRequestPath() {
    $requestUri = $_SERVER['REQUEST_URI'];
    $basePath = '/uriplants/public';
    if (strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
    return parse_url($requestUri, PHP_URL_PATH);
}

$requestPath = getRequestPath();
$method = $_SERVER['REQUEST_METHOD'];

$plantController = new PlantController();
$quizzController = new QuizzController();

if (preg_match('#^/plants/(\d+)$#', $requestPath, $matches)) {
    $id = intval($matches[1]);
    if ($method === 'GET') {
        $_GET['id'] = $id; 
        $plantController->get();
        exit;
    }
}

if ($requestPath === '/plants') {
    if ($method === 'POST') {
        $plantController->insert();
    } elseif ($method === 'GET') {
        $plantController->get(); 
    } else {
        header("HTTP/1.1 405 Method Not Allowed");
        echo json_encode(["message" => "Método não permitido"]);
    }
    exit;
}

if (preg_match('#^/quizz/(\d+)$#', $requestPath, $matches)) {
    $id = intval($matches[1]);
    if ($method === 'GET') {
        $_GET['id'] = $id; 
        $quizzController->get();
        exit;
    }
}

if ($requestPath === '/quizz') {
    if ($method === 'POST') {
        $quizzController->insert();
    } elseif ($method === 'GET') {
        $quizzController->get(); 
    } else {
        header("HTTP/1.1 405 Method Not Allowed");
        echo json_encode(["message" => "Método não permitido"]);
    }
    exit;
}

header("HTTP/1.1 404 Not Found");
echo json_encode(["message" => "Rota não encontrada"]);
?>
