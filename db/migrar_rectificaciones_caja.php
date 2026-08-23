<?php
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) {
    exit("Uso: php db/migrar_rectificaciones_caja.php --confirmar\n");
}
require_once __DIR__ . '/../config/conexion.php';

$columnas = $conexion->query("SHOW COLUMNS FROM cierres_caja LIKE 'estado_revision'")->fetch();
if (!$columnas) {
    $conexion->exec("ALTER TABLE cierres_caja ADD estado_revision ENUM('SIN_DIFERENCIA','PENDIENTE','RECTIFICADO','JUSTIFICADO','REGULARIZADO') NOT NULL DEFAULT 'SIN_DIFERENCIA' AFTER diferencia");
    $conexion->exec("UPDATE cierres_caja SET estado_revision=IF(ABS(diferencia)>0.009,'PENDIENTE','SIN_DIFERENCIA')");
}

$conexion->exec("CREATE TABLE IF NOT EXISTS rectificaciones_caja (
    id_rectificacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cierre BIGINT UNSIGNED NOT NULL,
    accion ENUM('RECTIFICACION','JUSTIFICACION','REGULARIZACION') NOT NULL,
    motivo_tipo ENUM('ERROR_CONTEO','ERROR_DIGITACION','MOVIMIENTO_OMITIDO','DIFERENCIA_REAL','OTRO') NOT NULL,
    motivo VARCHAR(500) NOT NULL,
    referencia_regularizacion VARCHAR(120) NULL,
    evidencia_archivo VARCHAR(180) NULL,
    diferencia_anterior DECIMAL(12,2) NOT NULL,
    diferencia_nueva DECIMAL(12,2) NOT NULL,
    registrado_por BIGINT UNSIGNED NULL,
    registrado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY ix_rectificacion_cierre(id_cierre,registrado_en),
    CONSTRAINT fk_rectificacion_cierre FOREIGN KEY(id_cierre) REFERENCES cierres_caja(id_cierre),
    CONSTRAINT fk_rectificacion_usuario FOREIGN KEY(registrado_por) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB");

$conexion->exec("CREATE TABLE IF NOT EXISTS rectificaciones_caja_medios (
    id_rectificacion_medio BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rectificacion BIGINT UNSIGNED NOT NULL,
    medio ENUM('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO') NOT NULL,
    importe_sistema DECIMAL(12,2) NOT NULL,
    importe_anterior DECIMAL(12,2) NOT NULL,
    importe_nuevo DECIMAL(12,2) NOT NULL,
    diferencia_anterior DECIMAL(12,2) NOT NULL,
    diferencia_nueva DECIMAL(12,2) NOT NULL,
    UNIQUE KEY uq_rectificacion_medio(id_rectificacion,medio),
    CONSTRAINT fk_rectificacion_medio FOREIGN KEY(id_rectificacion) REFERENCES rectificaciones_caja(id_rectificacion)
) ENGINE=InnoDB");

$conexion->exec("INSERT IGNORE INTO cierres_caja_medios(id_cierre,medio,importe_sistema,importe_declarado,diferencia) SELECT id_cierre,'EFECTIVO',efectivo_sistema,efectivo_declarado,efectivo_declarado-efectivo_sistema FROM cierres_caja");
$conexion->exec("INSERT IGNORE INTO cierres_caja_medios(id_cierre,medio,importe_sistema,importe_declarado,diferencia) SELECT id_cierre,'OTRO',otros_sistema,otros_declarado,otros_declarado-otros_sistema FROM cierres_caja WHERE ABS(otros_sistema)>0.009 OR ABS(otros_declarado)>0.009");
$conexion->exec("UPDATE cierres_caja SET estado_revision='PENDIENTE' WHERE estado_revision='SIN_DIFERENCIA' AND ABS(diferencia)>0.009");

$permiso = $conexion->prepare("INSERT INTO permisos(clave,descripcion,estado) VALUES('caja.rectificar','Rectificar y resolver diferencias de caja','ACTIVO') ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion),estado='ACTIVO'");
$permiso->execute();
$idPermiso = (int)$conexion->query("SELECT id_permiso FROM permisos WHERE clave='caja.rectificar'")->fetchColumn();
$asignar = $conexion->prepare("INSERT IGNORE INTO rol_permiso(id_rol,id_permiso) SELECT id_rol,:permiso FROM roles WHERE nombre IN ('Super Administrador','Administrador','Director')");
$asignar->execute([':permiso'=>$idPermiso]);

echo "Rectificaciones de caja y permiso caja.rectificar habilitados.\n";
