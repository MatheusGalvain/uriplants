<?php
require_once __DIR__ . '/../controllers/PlantController.php';

function getRequestPath() {
    $requestUri = str_replace('/uriplants/public', '', $_SERVER['REQUEST_URI']);
    return $requestUri;
}

$requestPath = getRequestPath();

if ($requestPath === '/plants' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new PlantController();
    $controller->insert();
}

if ($requestPath === '/plants' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new PlantController();
    $controller->get();
}
?>
