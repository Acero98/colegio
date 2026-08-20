<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PlanillaReglaModel.php';
class PlanillaReglaController
{
    private function m()
    {
        global $conexion;
        return new PlanillaReglaModel($conexion);
    }
    function index(): void
    {
        autorizar('planilla.calcular');
        $reglas = $this->m()->listar();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/planilla/reglas.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
    function guardar(): void
    {
        autorizar('planilla.calcular');
        validarCsrf();
        try {
            $this->m()->guardar($_POST);
            mensaje('success', 'Regla guardada correctamente.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=planilla_reglas');
        exit;
    }
    function estado(): void
    {
        autorizar('planilla.calcular');
        validarCsrf();
        try {
            $this->m()->estado((int)($_POST['id_regla'] ?? 0), $_POST['estado'] ?? '');
            mensaje('success', 'Estado actualizado.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=planilla_reglas');
        exit;
    }
}
