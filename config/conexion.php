<?php

$configLocal = __DIR__ . '/local.php';
if (!is_file($configLocal)) {
    throw new RuntimeException(
        'Falta config/local.php. Copie config/local.example.php y configure la conexión.'
    );
}

$dbConfig = require $configLocal;

$conexion = new PDO(
    sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $dbConfig['host'],
        $dbConfig['database']
    ),
    $dbConfig['username'],
    $dbConfig['password'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
