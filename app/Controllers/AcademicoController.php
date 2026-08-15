<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/AcademicoModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class AcademicoController
{
    public function index(): void
    {
        autorizar('academico.ver');
        global $conexion;
        extract((new AcademicoModel($conexion))->datos());
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/academico/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function guardar(): void
    {
        autorizar('academico.gestionar');
        validarCsrf();
        $entidad = $_POST['entidad'] ?? '';
        global $conexion;
        try {
            (new AcademicoModel($conexion))->crear($entidad, $_POST);
            (new AuditoriaService($conexion))->registrar('CREAR', 'academico_' . $entidad, null, null, $_POST);
            mensaje('success', 'Registro académico guardado.');
        } catch (Throwable $e) {
            mensaje('error', 'No se pudo guardar. Verifique que no esté duplicado y que la jerarquía sea válida.');
        }
        header('Location: index.php?url=academico');
        exit;
    }
}
