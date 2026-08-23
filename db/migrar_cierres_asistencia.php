<?php

if (PHP_SAPI !== 'cli' || !in_array('--confirmar', $argv, true)) {
    exit("Uso: php db/migrar_cierres_asistencia.php --confirmar\n");
}

require_once __DIR__ . '/../config/conexion.php';

if (!$conexion->query("SHOW TABLES LIKE 'cierres_asistencia'")->fetch()) {
    $conexion->exec("CREATE TABLE cierres_asistencia (
        id_cierre BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fecha DATE NOT NULL,
        alcance ENUM('ALUMNOS','PERSONAL') NOT NULL,
        tipo ENUM('MANUAL','AUTOMATICO') NOT NULL DEFAULT 'MANUAL',
        ejecutado_por BIGINT UNSIGNED NULL,
        proceso VARCHAR(80) NULL,
        faltas_generadas INT UNSIGNED NOT NULL DEFAULT 0,
        incompletas_generadas INT UNSIGNED NOT NULL DEFAULT 0,
        detalle JSON NULL,
        ejecutado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_cierre_asistencia_fecha_alcance(fecha,alcance),
        KEY ix_cierre_asistencia_ejecucion(ejecutado_en),
        CONSTRAINT fk_cierre_asistencia_usuario FOREIGN KEY(ejecutado_por) REFERENCES usuarios(id_usuario)
    ) ENGINE=InnoDB");
    echo "Tabla cierres_asistencia creada.\n";
} else {
    echo "La tabla cierres_asistencia ya existe.\n";
}
