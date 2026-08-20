<?php
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) {
    exit("Uso: php db/migrar_calendario_institucional.php --confirmar\n");
}

require_once __DIR__ . '/../config/conexion.php';

$columnas = [];
if (!$conexion->query("SHOW COLUMNS FROM calendario_institucional LIKE 'estado'")->fetch()) {
    $columnas[] = "ADD COLUMN estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO' AFTER descripcion";
}
if (!$conexion->query("SHOW COLUMNS FROM calendario_institucional LIKE 'creado_por'")->fetch()) {
    $columnas[] = 'ADD COLUMN creado_por BIGINT UNSIGNED NULL AFTER estado';
}
if (!$conexion->query("SHOW COLUMNS FROM calendario_institucional LIKE 'creado_en'")->fetch()) {
    $columnas[] = 'ADD COLUMN creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER creado_por';
}
if (!$conexion->query("SHOW COLUMNS FROM calendario_institucional LIKE 'actualizado_por'")->fetch()) {
    $columnas[] = 'ADD COLUMN actualizado_por BIGINT UNSIGNED NULL AFTER creado_en';
}
if (!$conexion->query("SHOW COLUMNS FROM calendario_institucional LIKE 'actualizado_en'")->fetch()) {
    $columnas[] = 'ADD COLUMN actualizado_en DATETIME NULL AFTER actualizado_por';
}

if ($columnas) {
    $conexion->exec('ALTER TABLE calendario_institucional ' . implode(', ', $columnas));
    echo "Calendario institucional actualizado.\n";
} else {
    echo "El calendario institucional ya estaba actualizado.\n";
}
