<?php
// Habilitar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../controllers/PlantController.php';

function getRequestPath() {
    $requestUri = $_SERVER['REQUEST_URI'];
    // Remove a parte inicial da URL que corresponde ao seu diretório público
    $basePath = '/uriplants/public';
    if (strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
    return parse_url($requestUri, PHP_URL_PATH);
}

$requestPath = getRequestPath();
$method = $_SERVER['REQUEST_METHOD'];

$controller = new PlantController();

// Expressão regular para capturar /plants/{id}
if (preg_match('#^/plants/(\d+)$#', $requestPath, $matches)) {
    $id = intval($matches[1]);
    if ($method === 'GET') {
        $_GET['id'] = $id; // Define o id para o método get
        $controller->get();
        exit;
    }
    // Você pode adicionar outros métodos HTTP (PUT, DELETE) aqui se necessário
}

// Rota para /plants com métodos POST e GET (sem id)
if ($requestPath === '/plants') {
    if ($method === 'POST') {
        $controller->insert();
    } elseif ($method === 'GET') {
        $controller->get(); // Pode retornar todas as plantas ou aplicar filtros
    } else {
        // Método HTTP não suportado
        header("HTTP/1.1 405 Method Not Allowed");
        echo json_encode(["message" => "Método não permitido"]);
    }
} else {
    // Rota não encontrada
    header("HTTP/1.1 404 Not Found");
    echo json_encode(["message" => "Rota não encontrada"]);
}
?>
