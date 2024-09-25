<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../controllers/PlantController.php';

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

$controller = new PlantController();

if (preg_match('#^/plants/(\d+)$#', $requestPath, $matches)) {
    $id = intval($matches[1]);
    if ($method === 'GET') {
        $_GET['id'] = $id; 
        $controller->get();
        exit;
    }
}

if ($requestPath === '/plants') {
    if ($method === 'POST') {
        $controller->insert();
    } elseif ($method === 'GET') {
        $controller->get(); 
    } else {
        header("HTTP/1.1 405 Method Not Allowed");
        echo json_encode(["message" => "Método não permitido"]);
    }
} else {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(["message" => "Rota não encontrada"]);
}
?>
