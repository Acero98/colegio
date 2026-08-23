<?php
if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) {
    exit("Uso: php db/migrar_movimientos_financieros.php --confirmar\n");
}
require_once __DIR__ . '/../config/conexion.php';

$conexion->exec("CREATE TABLE IF NOT EXISTS categorias_movimiento_financiero (
    id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    tipo ENUM('INGRESO','EGRESO') NOT NULL,
    clasificacion ENUM('OPERATIVO','FINANCIAMIENTO','CAPITAL') NOT NULL DEFAULT 'OPERATIVO',
    estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    UNIQUE KEY uq_categoria_movimiento(nombre,tipo)
) ENGINE=InnoDB");

$conexion->exec("CREATE TABLE IF NOT EXISTS movimientos_financieros (
    id_movimiento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL,
    tipo ENUM('INGRESO','EGRESO') NOT NULL,
    id_categoria INT UNSIGNED NOT NULL,
    fecha_movimiento DATETIME NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    medio ENUM('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO') NOT NULL,
    tercero VARCHAR(160) NULL,
    documento VARCHAR(80) NULL,
    referencia VARCHAR(120) NULL,
    descripcion VARCHAR(500) NOT NULL,
    evidencia_archivo VARCHAR(180) NULL,
    id_apertura BIGINT UNSIGNED NULL,
    estado ENUM('REGISTRADO','ANULADO') NOT NULL DEFAULT 'REGISTRADO',
    registrado_por BIGINT UNSIGNED NULL,
    registrado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    anulado_por BIGINT UNSIGNED NULL,
    anulado_en DATETIME NULL,
    motivo_anulacion VARCHAR(250) NULL,
    UNIQUE KEY uq_movimiento_codigo(codigo),
    KEY ix_movimiento_fecha(fecha_movimiento,estado),
    KEY ix_movimiento_tipo(tipo,id_categoria),
    KEY ix_movimiento_apertura(id_apertura),
    CONSTRAINT fk_movimiento_categoria FOREIGN KEY(id_categoria) REFERENCES categorias_movimiento_financiero(id_categoria),
    CONSTRAINT fk_movimiento_apertura FOREIGN KEY(id_apertura) REFERENCES aperturas_caja(id_apertura)
) ENGINE=InnoDB");

$conexion->exec("CREATE TABLE IF NOT EXISTS cierres_caja_medios (
    id_cierre_medio BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cierre BIGINT UNSIGNED NOT NULL,
    medio ENUM('EFECTIVO','TRANSFERENCIA','YAPE','PLIN','TARJETA','DEPOSITO','OTRO') NOT NULL,
    importe_sistema DECIMAL(12,2) NOT NULL DEFAULT 0,
    importe_declarado DECIMAL(12,2) NOT NULL DEFAULT 0,
    diferencia DECIMAL(12,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_cierre_caja_medio(id_cierre,medio),
    CONSTRAINT fk_cierre_medio_cierre FOREIGN KEY(id_cierre) REFERENCES cierres_caja(id_cierre)
) ENGINE=InnoDB");

$categorias = [
    ['Aporte o inyección de capital','INGRESO','CAPITAL'],
    ['Préstamo recibido','INGRESO','FINANCIAMIENTO'],
    ['Donación','INGRESO','OPERATIVO'],
    ['Venta de bienes o servicios','INGRESO','OPERATIVO'],
    ['Otros ingresos','INGRESO','OPERATIVO'],
    ['Compras','EGRESO','OPERATIVO'],
    ['Alquiler','EGRESO','OPERATIVO'],
    ['Servicios básicos','EGRESO','OPERATIVO'],
    ['Mantenimiento','EGRESO','OPERATIVO'],
    ['Impuestos y tasas','EGRESO','OPERATIVO'],
    ['Pago de préstamo','EGRESO','FINANCIAMIENTO'],
    ['Devolución de capital','EGRESO','CAPITAL'],
    ['Otros egresos','EGRESO','OPERATIVO'],
];
$insertar = $conexion->prepare("INSERT INTO categorias_movimiento_financiero(nombre,tipo,clasificacion,estado) VALUES(?,?,?,'ACTIVO') ON DUPLICATE KEY UPDATE clasificacion=VALUES(clasificacion)");
foreach ($categorias as $categoria) $insertar->execute($categoria);
echo "Movimientos financieros y categorías habilitados.\n";
