<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PlanillaPreviewModel.php';
class PlanillaPreviewController
{
    function index(): void
    {
        autorizar('planilla.calcular');
        global $conexion;
        try {
            extract((new PlanillaPreviewModel($conexion))->generar((int)($_GET['id'] ?? 0)));
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
            header('Location:index.php?url=planilla');
            exit;
        }
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/planilla/preview.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
