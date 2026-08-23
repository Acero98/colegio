<?php
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) {
    exit("Uso: php db/migrar_pagos_mixtos.php --confirmar\n");
}

require_once __DIR__ . '/../config/conexion.php';

$columna = $conexion->query("SHOW COLUMNS FROM pagos_alumno LIKE 'medio_pago'")->fetch();
if ($columna && (strpos($columna['Type'], "'MIXTO'") === false || strpos($columna['Type'], "'TARJETA'") === false)) {
    $conexion->exec("ALTER TABLE pagos_alumno MODIFY medio_pago ENUM('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO','MIXTO') NOT NULL");
    echo "Medios mixtos habilitados en pagos.\n";
}

$conexion->exec("CREATE TABLE IF NOT EXISTS pagos_alumno_medios (
    id_pago_medio BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pago BIGINT UNSIGNED NOT NULL,
    medio ENUM('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO') NOT NULL,
    importe_aplicado DECIMAL(12,2) NOT NULL,
    importe_recibido DECIMAL(12,2) NOT NULL,
    vuelto DECIMAL(12,2) NOT NULL DEFAULT 0,
    referencia VARCHAR(120) NULL,
    evidencia_archivo VARCHAR(180) NULL,
    KEY ix_pago_medio_pago(id_pago),
    KEY ix_pago_medio_tipo(medio),
    CONSTRAINT fk_pago_medio_pago FOREIGN KEY(id_pago) REFERENCES pagos_alumno(id_pago)
) ENGINE=InnoDB");

if (!$conexion->query("SHOW COLUMNS FROM pagos_alumno_medios LIKE 'evidencia_archivo'")->fetch()) {
    $conexion->exec("ALTER TABLE pagos_alumno_medios ADD COLUMN evidencia_archivo VARCHAR(180) NULL AFTER referencia");
    echo "Evidencias fotográficas habilitadas para los medios de pago.\n";
}

$conexion->exec("INSERT INTO pagos_alumno_medios(id_pago,medio,importe_aplicado,importe_recibido,vuelto,referencia)
    SELECT p.id_pago,
           CASE WHEN p.medio_pago='MIXTO' THEN 'OTRO' ELSE p.medio_pago END,
           p.importe_total,p.importe_total,0,
           CASE WHEN p.medio_pago='MIXTO' THEN 'Pago anterior a la migracion' ELSE NULL END
    FROM pagos_alumno p
    WHERE NOT EXISTS (SELECT 1 FROM pagos_alumno_medios pm WHERE pm.id_pago=p.id_pago)");

echo "Tabla de medios de pago creada y pagos anteriores conservados.\n";
