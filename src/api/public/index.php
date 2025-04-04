<?php

require __DIR__ . '/../vendor/autoload.php'; // Se usar autoload
require __DIR__ . '/../../db/database.php';
$config = require __DIR__ . '/../../db/config/config.php';

header('Content-Type: application/json');

require __DIR__ . '/router.php';
