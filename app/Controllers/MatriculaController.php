<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PersonaModel.php';
require_once __DIR__ . '/../Models/MatriculaModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class MatriculaController
{
    public function index(): void
    {
        autorizar('matriculas.ver');
        global $conexion;
        $model = new MatriculaModel($conexion);
        $opciones = $model->opciones();
        $matriculas = $model->listar();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/matriculas/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function guardar(): void
    {
        autorizar('matriculas.crear');
        validarCsrf();
        $requeridos = ['id_alumno', 'id_periodo', 'id_seccion', 'fecha_matricula'];
        foreach ($requeridos as $campo) {
            if (empty($_POST[$campo])) {
                mensaje('warning', 'Complete los datos obligatorios de la matrícula.');
                header('Location: index.php?url=matriculas');
                exit;
            }
        }
        global $conexion;
        try {
            $id = (new MatriculaModel($conexion))->crear($_POST);
            (new AuditoriaService($conexion))->registrar('CREAR', 'matriculas', $id, null, $_POST);
            mensaje('success', 'Matrícula registrada correctamente.');
        } catch (DomainException $e) {
            mensaje('warning', $e->getMessage());
        } catch (Throwable $e) {
            mensaje('error', 'No se pudo registrar la matrícula. El alumno podría ya estar matriculado en este periodo.');
        }
        header('Location: index.php?url=matriculas');
        exit;
    }
}
