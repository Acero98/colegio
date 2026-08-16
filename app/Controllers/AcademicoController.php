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

    public function actualizar(): void
    {
        autorizar('academico.gestionar'); validarCsrf();
        $entidad = $_POST['entidad'] ?? '';
        global $conexion;
        try {
            (new AcademicoModel($conexion))->actualizar($entidad, $_POST);
            (new AuditoriaService($conexion))->registrar('EDITAR', 'academico_' . $entidad, (int) ($_POST['id'] ?? 0), null, $_POST);
            mensaje('success', 'Registro académico actualizado.');
        } catch (Throwable $e) {
            mensaje('error', 'No se pudo actualizar. Verifique los datos y que no estén duplicados.');
        }
        header('Location: index.php?url=academico'); exit;
    }

    public function cambiarEstado(): void
    {
        autorizar('academico.gestionar'); validarCsrf();
        $entidad = $_POST['entidad'] ?? '';
        $estado = $_POST['estado'] ?? '';
        global $conexion;
        try {
            (new AcademicoModel($conexion))->cambiarEstado($entidad, (int) ($_POST['id'] ?? 0), $estado);
            (new AuditoriaService($conexion))->registrar($estado === 'ACTIVO' ? 'ACTIVAR' : 'DESACTIVAR', 'academico_' . $entidad, (int) ($_POST['id'] ?? 0));
            mensaje('success', $estado === 'ACTIVO' ? 'Registro activado.' : 'Registro desactivado.');
        } catch (Throwable $e) {
            mensaje('error', 'No se pudo cambiar el estado del registro.');
        }
        header('Location: index.php?url=academico'); exit;
    }
}
