<?php
// Ejecutar una sola vez: php db/migrar_ajustes_cobro.php --confirmar
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) exit("Uso: php db/migrar_ajustes_cobro.php --confirmar\n");
require_once __DIR__ . '/../config/conexion.php';
if (!$conexion->query("SHOW COLUMNS FROM cuentas_cobrar LIKE 'recargo'")->fetch()) {
    $conexion->exec("ALTER TABLE cuentas_cobrar ADD COLUMN recargo DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER descuento");
    echo "Columna recargo agregada.\n";
}
if (!$conexion->query("SHOW TABLES LIKE 'ajustes_cobro'")->fetch()) {
    $conexion->exec("CREATE TABLE ajustes_cobro (id_ajuste BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,id_cuenta BIGINT UNSIGNED NOT NULL,tipo ENUM('DESCUENTO','BECA','EXONERACION','RECARGO') NOT NULL,importe DECIMAL(12,2) NOT NULL,motivo VARCHAR(250) NOT NULL,registrado_por BIGINT UNSIGNED NULL,creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,CONSTRAINT fk_ajuste_cuenta FOREIGN KEY(id_cuenta) REFERENCES cuentas_cobrar(id_cuenta)) ENGINE=InnoDB");
    echo "Tabla ajustes_cobro creada.\n";
}
