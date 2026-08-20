<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PlanillaModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class VinculoLaboralController
{
    public function index(): void
    {
        autorizar('planilla.calcular');
        global $conexion;
        $modelo = new PlanillaModel($conexion);
        $personas = $modelo->personasParaVinculo();
        $vinculos = $modelo->datos()['vinculos'];
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/vinculos_laborales/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function guardar(): void
    {
        autorizar('planilla.calcular');
        validarCsrf();
        global $conexion;
        try {
            (new PlanillaModel($conexion))->vinculo($_POST);
            (new AuditoriaService($conexion))->registrar('TRANSICION_LABORAL', 'vinculos_laborales', null, null, $_POST);
            mensaje('success', 'Vínculo laboral actualizado y código de trabajador asignado.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=vinculos_laborales');
        exit;
    }

    public function versionar(): void
    {
        autorizar('planilla.calcular');
        validarCsrf();
        global $conexion;
        try {
            $modelo = new PlanillaModel($conexion);
            $idAnterior = (int)($_POST['id_vinculo'] ?? 0);
            $antes = $modelo->buscarVinculo($idAnterior);
            $idNuevo = $modelo->versionarVinculo($_POST, (int)($_SESSION['usuario']['id_usuario'] ?? 0));
            $despues = $modelo->buscarVinculo($idNuevo);
            (new AuditoriaService($conexion))->registrar('VERSIONAR_VINCULO', 'vinculos_laborales', $idNuevo, $antes, $despues);
            mensaje('success', $idNuevo === $idAnterior ? 'Vínculo corregido con auditoría.' : 'Nueva versión del vínculo laboral creada.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=vinculos_laborales');
        exit;
    }
}
