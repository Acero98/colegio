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
        $promocion = null;
        if (!empty($_GET['promocion_origen']) && !empty($_GET['promocion_destino'])) {
            $filtroPromocion = $_GET['promocion_filtro'] ?? 'TODO';
            $idGradoPromocion = $filtroPromocion === 'GRADO' ? (int) ($_GET['promocion_grado'] ?? 0) : 0;
            $idSeccionPromocion = $filtroPromocion === 'SECCION' ? (int) ($_GET['promocion_seccion'] ?? 0) : 0;
            $promocion = $model->candidatosPromocion((int) $_GET['promocion_origen'], (int) $_GET['promocion_destino'], $idGradoPromocion, $idSeccionPromocion);
        }
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

    public function actualizar(): void
    {
        autorizar('matriculas.crear');
        validarCsrf();
        global $conexion;
        try {
            $id = (new MatriculaModel($conexion))->actualizar($_POST);
            (new AuditoriaService($conexion))->registrar('EDITAR', 'matriculas', $id, null, $_POST);
            mensaje('success', 'Matrícula actualizada correctamente.');
        } catch (DomainException $e) {
            mensaje('warning', $e->getMessage());
        } catch (Throwable $e) {
            mensaje('error', 'No se pudo actualizar la matrícula. Verifique los datos ingresados.');
        }
        header('Location: index.php?url=matriculas');
        exit;
    }

    public function promover(): void
    {
        autorizar('matriculas.crear');
        validarCsrf();
        global $conexion;
        try {
            $resultado = (new MatriculaModel($conexion))->promover($_POST);
            mensaje('success', "Promoción procesada: {$resultado['promovidos']} promovidos, {$resultado['repitentes']} repitentes, {$resultado['retirados']} retirados y {$resultado['egresados']} egresados.");
        } catch (DomainException $e) {
            mensaje('warning', $e->getMessage());
        } catch (Throwable $e) {
            mensaje('error', 'No se pudo procesar la promoción. Revise los cupos y la selección.');
        }
        header('Location: index.php?url=matriculas');
        exit;
    }
}
