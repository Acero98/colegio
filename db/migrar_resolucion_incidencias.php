<?php
require_once __DIR__ . '/../config/conexion.php';

if (PHP_SAPI !== 'cli') exit("Esta migración solo se ejecuta por consola.\n");

$columna = $conexion->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='incidencias_asistencia' AND COLUMN_NAME='resolucion_observacion'")->fetchColumn();
if (!(int)$columna) {
    $conexion->exec("ALTER TABLE incidencias_asistencia ADD COLUMN resolucion_observacion VARCHAR(500) NULL AFTER resuelto_por");
    echo "Columna resolucion_observacion agregada.\n";
} else {
    echo "La columna resolucion_observacion ya existe.\n";
}

$permiso = $conexion->prepare("INSERT IGNORE INTO rol_permiso(id_rol,id_permiso) SELECT r.id_rol,p.id_permiso FROM roles r CROSS JOIN permisos p WHERE r.nombre='Director' AND p.clave='incidencias.gestionar'");
$permiso->execute();
$conexion->exec("UPDATE incidencias_asistencia SET resolucion_observacion=CASE estado WHEN 'APROBADA' THEN 'Resolución aprobada antes de habilitar el detalle.' WHEN 'RECHAZADA' THEN 'Resolución rechazada antes de habilitar el detalle.' ELSE resolucion_observacion END WHERE estado<>'PENDIENTE' AND resolucion_observacion IS NULL");

echo "Permiso de resolución para Dirección y datos existentes actualizados.\n";
