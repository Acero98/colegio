<?php
// Ejecutar una sola vez: php db/migrar_planilla_automatica.php --confirmar
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) exit("Uso: php db/migrar_planilla_automatica.php --confirmar\n");
require_once __DIR__ . '/../config/conexion.php';

if (!$conexion->query("SHOW COLUMNS FROM vinculos_laborales LIKE 'monto_mensual'")->fetch()) {
    $conexion->exec("ALTER TABLE vinculos_laborales
        ADD COLUMN monto_mensual DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER monto_base,
        ADD COLUMN monto_hora DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER monto_mensual,
        ADD COLUMN monto_sesion DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER monto_hora,
        ADD COLUMN dias_laborables VARCHAR(20) NOT NULL DEFAULT '1,2,3,4,5' AFTER monto_sesion");
    $conexion->exec("UPDATE vinculos_laborales SET
        monto_mensual=CASE WHEN modalidad_pago='MENSUAL' THEN monto_base ELSE 0 END,
        monto_hora=CASE WHEN modalidad_pago='HORA' THEN monto_base ELSE 0 END,
        monto_sesion=CASE WHEN modalidad_pago='SESION' THEN monto_base ELSE 0 END");
    echo "Componentes y dias laborables de planilla agregados.\n";
}

$modalidad = $conexion->query("SHOW COLUMNS FROM vinculos_laborales LIKE 'modalidad_pago'")->fetch();
if ($modalidad && strpos($modalidad['Type'], "'MIXTO'") === false) {
    $conexion->exec("ALTER TABLE vinculos_laborales MODIFY modalidad_pago ENUM('MENSUAL','HORA','SESION','MIXTO') NOT NULL");
    echo "Modalidad mixta habilitada.\n";
}

$conexion->exec("UPDATE vinculos_laborales SET modalidad_pago='MIXTO' WHERE monto_mensual>0 AND (monto_hora>0 OR monto_sesion>0) AND modalidad_pago<>'MIXTO'");
