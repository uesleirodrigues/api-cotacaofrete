<?php

require_once __DIR__ . '/../controllers/QuoteController.php';

$controller = new QuoteController();
$controller->handleQuoteRequest();
