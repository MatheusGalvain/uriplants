<?php
require_once __DIR__ . '/../../controllers/PlantController.php';

$plantController = new PlantController();
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$query = isset($_GET['query']) ? $_GET['query'] : '';

$plantsData = $plantController->getPlants($limit, $page, $query);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($plantsData, JSON_UNESCAPED_UNICODE);
?>
