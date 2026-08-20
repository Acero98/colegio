<?php
// Ejecutar una sola vez: php db/migrar_estado_tarifas.php --confirmar
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) exit("Uso: php db/migrar_estado_tarifas.php --confirmar\n");
require_once __DIR__ . '/../config/conexion.php';
$existe = $conexion->query("SHOW COLUMNS FROM tarifas_cobro LIKE 'estado'")->fetch();
if (!$existe) {
    $conexion->exec("ALTER TABLE tarifas_cobro ADD COLUMN estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO' AFTER vigencia_fin");
    echo "Columna estado agregada a tarifas_cobro.\n";
} else echo "La columna estado ya existe.\n";
