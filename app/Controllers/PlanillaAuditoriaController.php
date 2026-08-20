<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PlanillaAuditoriaModel.php';

class PlanillaAuditoriaController
{
    private function autorizarConsulta(): void
    {
        requiereLogin();
        if (!tienePermiso('auditoria.ver') && !tienePermiso('planilla.cerrar') && !tienePermiso('planilla.calcular')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errores/403.php';
            exit;
        }
    }

    public function index(): void
    {
        $this->autorizarConsulta();
        $anio = (int)($_GET['anio'] ?? 0);
        $accion = trim($_GET['accion'] ?? '');
        global $conexion;
        extract((new PlanillaAuditoriaModel($conexion))->datos($anio, $accion));
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/planilla/auditoria.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
