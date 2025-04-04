<?php

define('BASE_PATH', dirname(__DIR__));


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'POST' => [
        '/api/quotes' => __DIR__ . '/../routes/quote.php',
    ],
    'GET' => [
        '/api/metrics' => __DIR__ . '/../routes/metrics.php',
    ],
];

if (isset($routes[$method][$uri])) {
    require $routes[$method][$uri];
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Rota não encontrada']);
}
