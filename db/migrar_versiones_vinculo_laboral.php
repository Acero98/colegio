<?php
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) {
    exit("Uso: php db/migrar_versiones_vinculo_laboral.php --confirmar\n");
}

require_once __DIR__ . '/../config/conexion.php';

$columnas = [];
if (!$conexion->query("SHOW COLUMNS FROM vinculos_laborales LIKE 'id_vinculo_anterior'")->fetch()) {
    $columnas[] = 'ADD COLUMN id_vinculo_anterior BIGINT UNSIGNED NULL AFTER id_persona';
}
if (!$conexion->query("SHOW COLUMNS FROM vinculos_laborales LIKE 'motivo_cambio'")->fetch()) {
    $columnas[] = 'ADD COLUMN motivo_cambio VARCHAR(250) NULL AFTER estado';
}
if (!$conexion->query("SHOW COLUMNS FROM vinculos_laborales LIKE 'registrado_por'")->fetch()) {
    $columnas[] = 'ADD COLUMN registrado_por BIGINT UNSIGNED NULL AFTER motivo_cambio';
}
if (!$conexion->query("SHOW COLUMNS FROM vinculos_laborales LIKE 'registrado_en'")->fetch()) {
    $columnas[] = 'ADD COLUMN registrado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER registrado_por';
}
if ($columnas) {
    $conexion->exec('ALTER TABLE vinculos_laborales ' . implode(', ', $columnas));
}

if (!$conexion->query("SHOW INDEX FROM vinculos_laborales WHERE Key_name='ix_vinculo_anterior'")->fetch()) {
    $conexion->exec('ALTER TABLE vinculos_laborales ADD KEY ix_vinculo_anterior(id_vinculo_anterior)');
}

echo "Versionado de vínculos laborales habilitado.\n";
