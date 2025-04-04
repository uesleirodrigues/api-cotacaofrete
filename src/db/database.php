<?php

function getConnection() {
    $config = require dirname(__DIR__) . '/db/config/config.php';

    $host = $config['host'];
    $port = $config['port'];
    $database = $config['database'];
    $username = $config['username'];
    $password = $config['password'];
    $charset = $config['charset'];
    $collation = $config['collation'];

    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Em um ambiente de produção, você não exibiria a mensagem de erro completa
        die("Erro na conexão com o banco de dados: " . $e->getMessage());
    }
}