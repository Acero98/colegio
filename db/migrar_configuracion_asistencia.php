<?php

if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) {
    exit("Uso: php db/migrar_configuracion_asistencia.php --confirmar\n");
}

require_once __DIR__ . '/../config/conexion.php';

if (!$conexion->query("SHOW COLUMNS FROM jornadas LIKE 'es_predeterminada'")->fetch()) {
    $conexion->exec("ALTER TABLE jornadas ADD COLUMN es_predeterminada BOOLEAN NOT NULL DEFAULT FALSE AFTER requiere_salida");
    echo "Indicador de jornada predeterminada agregado.\n";
}

$predeterminada = (int)$conexion->query("SELECT COUNT(*) FROM jornadas WHERE aplica_a='ALUMNO' AND estado='ACTIVO' AND es_predeterminada=TRUE")->fetchColumn();
if ($predeterminada === 0) {
    $id = (int)$conexion->query("SELECT id_jornada FROM jornadas WHERE aplica_a='ALUMNO' AND estado='ACTIVO' ORDER BY id_jornada LIMIT 1")->fetchColumn();
    if ($id) {
        $stmt = $conexion->prepare('UPDATE jornadas SET es_predeterminada=TRUE,requiere_salida=FALSE WHERE id_jornada=?');
        $stmt->execute([$id]);
        echo "Jornada predeterminada de alumnos asignada.\n";
    }
}

$conexion->exec("UPDATE jornadas SET requiere_salida=FALSE WHERE aplica_a='ALUMNO'");
echo "Configuracion de jornadas actualizada.\n";
