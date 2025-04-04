<?php

require_once __DIR__ . '/../controllers/MetricsController.php';

$controller = new MetricsController();
$controller->handleMetricsRequest();
