<?php

if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) {
    exit("Uso: php db/migrar_datos_institucionales.php --confirmar\n");
}

require_once __DIR__ . '/../config/conexion.php';

$columnas = [
    'nombre_corto' => "VARCHAR(80) NULL AFTER nombre",
    'razon_social' => "VARCHAR(180) NULL AFTER nombre_corto",
    'sitio_web' => "VARCHAR(180) NULL AFTER correo",
    'lema' => "VARCHAR(180) NULL AFTER sitio_web",
    'codigo_modular' => "VARCHAR(30) NULL AFTER lema",
    'ugel' => "VARCHAR(120) NULL AFTER codigo_modular",
    'representante_nombre' => "VARCHAR(180) NULL AFTER ugel",
    'representante_cargo' => "VARCHAR(100) NULL AFTER representante_nombre",
    'actualizado_por' => "BIGINT UNSIGNED NULL AFTER creado_en",
    'actualizado_en' => "DATETIME NULL AFTER actualizado_por",
];

$agregar = [];
foreach ($columnas as $nombre => $definicion) {
    $consulta = $conexion->query("SHOW COLUMNS FROM institucion LIKE " . $conexion->quote($nombre));
    if (!$consulta->fetch()) $agregar[] = "ADD COLUMN {$nombre} {$definicion}";
}

if ($agregar) {
    $conexion->exec('ALTER TABLE institucion ' . implode(', ', $agregar));
    echo "Estructura institucional actualizada.\n";
} else {
    echo "La estructura institucional ya estaba actualizada.\n";
}

$conexion->exec("UPDATE institucion SET nombre_corto=COALESCE(NULLIF(nombre_corto,''),nombre), razon_social=COALESCE(NULLIF(razon_social,''),nombre) WHERE id_institucion=1");
echo "Datos actuales preservados y centralizados.\n";
