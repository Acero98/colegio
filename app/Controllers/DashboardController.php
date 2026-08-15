<?php

require_once __DIR__ . '/../../config/conexion.php';

class DashboardController
{
    public function index(): void
    {
        global $conexion;
        $kpis = [];
        foreach ([
            'alumnos' => 'ALUMNO',
            'docentes' => 'DOCENTE',
            'administrativos' => 'ADMINISTRATIVO',
            'apoderados' => 'APODERADO',
        ] as $clave => $tipo) {
            $stmt = $conexion->prepare(
                'SELECT COUNT(*) FROM persona_perfiles WHERE tipo = :tipo AND estado = "ACTIVO"'
            );
            $stmt->execute([':tipo' => $tipo]);
            $kpis[$clave] = (int) $stmt->fetchColumn();
        }
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/dashboard/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
