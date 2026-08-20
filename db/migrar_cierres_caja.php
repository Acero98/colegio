<?php
// Ejecutar una sola vez: php db/migrar_cierres_caja.php --confirmar
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) exit("Uso: php db/migrar_cierres_caja.php --confirmar\n");
require_once __DIR__ . '/../config/conexion.php';
if (!$conexion->query("SHOW TABLES LIKE 'cierres_caja'")->fetch()) {
    $conexion->exec("CREATE TABLE cierres_caja (id_cierre BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,fecha DATE NOT NULL,efectivo_sistema DECIMAL(12,2) NOT NULL DEFAULT 0,efectivo_declarado DECIMAL(12,2) NOT NULL DEFAULT 0,otros_sistema DECIMAL(12,2) NOT NULL DEFAULT 0,otros_declarado DECIMAL(12,2) NOT NULL DEFAULT 0,diferencia DECIMAL(12,2) NOT NULL DEFAULT 0,observacion VARCHAR(250) NULL,cerrado_por BIGINT UNSIGNED NULL,cerrado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY uq_cierre_caja_fecha(fecha)) ENGINE=InnoDB");
    echo "Tabla cierres_caja creada.\n";
}
if (!$conexion->query("SHOW TABLES LIKE 'aperturas_caja'")->fetch()) {
    $conexion->exec("CREATE TABLE aperturas_caja (id_apertura BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,fecha DATE NOT NULL,monto_apertura DECIMAL(12,2) NOT NULL DEFAULT 0,observacion VARCHAR(250) NULL,abierto_por BIGINT UNSIGNED NULL,abierto_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,estado ENUM('ABIERTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',UNIQUE KEY uq_apertura_caja_fecha(fecha)) ENGINE=InnoDB");
    echo "Tabla aperturas_caja creada.\n";
}
if ($conexion->query("SHOW INDEX FROM aperturas_caja WHERE Key_name='uq_apertura_caja_fecha'")->fetch()) {
    $conexion->exec("ALTER TABLE aperturas_caja DROP INDEX uq_apertura_caja_fecha, ADD KEY ix_apertura_caja_fecha(fecha,estado)");
    echo "Aperturas múltiples por fecha habilitadas.\n";
}
if (!$conexion->query("SHOW COLUMNS FROM cierres_caja LIKE 'id_apertura'")->fetch()) {
    $conexion->exec("ALTER TABLE cierres_caja ADD COLUMN id_apertura BIGINT UNSIGNED NULL, ADD UNIQUE KEY uq_cierre_apertura(id_apertura)");
    echo "Vínculo de cierre por turno agregado.\n";
}
if ($conexion->query("SHOW INDEX FROM cierres_caja WHERE Key_name='uq_cierre_caja_fecha'")->fetch()) {
    $conexion->exec("ALTER TABLE cierres_caja DROP INDEX uq_cierre_caja_fecha, ADD KEY ix_cierre_caja_fecha(fecha)");
    echo "Cierres multiples por fecha habilitados.\n";
}
if (!$conexion->query("SHOW COLUMNS FROM pagos_alumno LIKE 'id_apertura'")->fetch()) {
    $conexion->exec("ALTER TABLE pagos_alumno ADD COLUMN id_apertura BIGINT UNSIGNED NULL, ADD KEY ix_pago_apertura(id_apertura)");
    echo "Vínculo de pago por turno agregado.\n";
}
$asignarCierres = $conexion->exec("UPDATE cierres_caja c INNER JOIN (SELECT fecha,MIN(id_apertura) AS id_apertura FROM aperturas_caja GROUP BY fecha HAVING COUNT(*)=1) a ON a.fecha=c.fecha SET c.id_apertura=a.id_apertura WHERE c.id_apertura IS NULL");
if ($asignarCierres) echo $asignarCierres." cierres historicos asociados al turno unico de su fecha.\n";
$asignar = $conexion->exec("UPDATE pagos_alumno p INNER JOIN (SELECT fecha,MIN(id_apertura) AS id_apertura FROM aperturas_caja GROUP BY fecha HAVING COUNT(*)=1) a ON a.fecha=DATE(p.fecha_pago) SET p.id_apertura=a.id_apertura WHERE p.id_apertura IS NULL");
if ($asignar) echo $asignar." pago(s) histórico(s) asociado(s) al turno único de su fecha.\n";
