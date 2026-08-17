<?php
$config = require __DIR__ . '/../config/local.php';
$db = new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['database'] . ';charset=utf8mb4', $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$columna = $db->query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='personas' AND COLUMN_NAME='codigo_trabajador'")->fetchColumn();
if (!$columna) $db->exec("ALTER TABLE personas ADD COLUMN codigo_trabajador VARCHAR(20) NULL AFTER fotografia, ADD UNIQUE KEY uq_persona_codigo_trabajador (codigo_trabajador)");
$trabajadores = $db->query("SELECT p.id_persona,MIN(v.fecha_inicio) fecha_inicio FROM personas p INNER JOIN vinculos_laborales v ON v.id_persona=p.id_persona WHERE p.codigo_trabajador IS NULL GROUP BY p.id_persona ORDER BY fecha_inicio,p.id_persona")->fetchAll(PDO::FETCH_ASSOC);
$buscar = $db->prepare("SELECT MAX(CAST(RIGHT(codigo_trabajador,4) AS UNSIGNED)) FROM personas WHERE codigo_trabajador LIKE ?");
$actualizar = $db->prepare('UPDATE personas SET codigo_trabajador=? WHERE id_persona=? AND codigo_trabajador IS NULL');
foreach ($trabajadores as $trabajador) {
    $anio = substr($trabajador['fecha_inicio'], 0, 4);
    $buscar->execute(['CS-' . $anio . '-%']);
    $codigo = 'CS-' . $anio . '-' . str_pad((string) ((int) $buscar->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);
    $actualizar->execute([$codigo, $trabajador['id_persona']]);
}
echo 'Migración completada: ' . count($trabajadores) . " código(s) generado(s).\n";
