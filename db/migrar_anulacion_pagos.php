<?php
// Ejecutar una sola vez: php db/migrar_anulacion_pagos.php --confirmar
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) exit("Uso: php db/migrar_anulacion_pagos.php --confirmar\n");
require_once __DIR__ . '/../config/conexion.php';
$columnas = ['motivo_anulacion'=>'VARCHAR(250) NULL','anulado_por'=>'BIGINT UNSIGNED NULL','anulado_en'=>'DATETIME NULL'];
foreach ($columnas as $nombre => $definicion) {
    if (!$conexion->query("SHOW COLUMNS FROM pagos_alumno LIKE '".$nombre."'")->fetch()) {
        $conexion->exec("ALTER TABLE pagos_alumno ADD COLUMN ".$nombre." ".$definicion);
        echo "Columna ".$nombre." agregada.\n";
    }
}
