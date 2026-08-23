<?php

if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) {
    exit("Uso: php db/migrar_decisiones_pago_planilla.php --confirmar\n");
}

require_once __DIR__ . '/../config/conexion.php';

$columnas = [];
if (!$conexion->query("SHOW COLUMNS FROM planilla_detalles LIKE 'decision_pago'")->fetch()) {
    $columnas[] = "ADD COLUMN decision_pago ENUM('PENDIENTE','RETENIDO','EXCLUIDO') NOT NULL DEFAULT 'PENDIENTE' AFTER saldo_pendiente";
}
if (!$conexion->query("SHOW COLUMNS FROM planilla_detalles LIKE 'monto_excluido'")->fetch()) {
    $columnas[] = "ADD COLUMN monto_excluido DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER decision_pago";
}
if (!$conexion->query("SHOW COLUMNS FROM planilla_detalles LIKE 'decision_motivo'")->fetch()) {
    $columnas[] = "ADD COLUMN decision_motivo VARCHAR(500) NULL AFTER monto_excluido";
}
if (!$conexion->query("SHOW COLUMNS FROM planilla_detalles LIKE 'decision_por'")->fetch()) {
    $columnas[] = "ADD COLUMN decision_por BIGINT UNSIGNED NULL AFTER decision_motivo";
}
if (!$conexion->query("SHOW COLUMNS FROM planilla_detalles LIKE 'decision_en'")->fetch()) {
    $columnas[] = "ADD COLUMN decision_en DATETIME NULL AFTER decision_por";
}
if ($columnas) {
    $conexion->exec('ALTER TABLE planilla_detalles ' . implode(',', $columnas));
    echo "Decisiones de pago agregadas a planilla_detalles.\n";
}

$estadoPeriodo = $conexion->query("SHOW COLUMNS FROM periodos_pago LIKE 'estado'")->fetch();
if ($estadoPeriodo && strpos($estadoPeriodo['Type'], "'FINALIZADO'") === false) {
    $conexion->exec("ALTER TABLE periodos_pago MODIFY estado ENUM('ABIERTO','CALCULADO','REVISADO','CERRADO','PAGADO','FINALIZADO') NOT NULL DEFAULT 'ABIERTO'");
    echo "Estado FINALIZADO habilitado para períodos con exclusiones.\n";
}

echo "Migración de decisiones de pago completada.\n";
