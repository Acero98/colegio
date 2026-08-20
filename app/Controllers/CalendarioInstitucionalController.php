<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/CalendarioInstitucionalModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class CalendarioInstitucionalController
{
    private function autorizarGestion(): void
    {
        requiereLogin();
        $permitido = tienePermiso('configuracion.gestionar')
            || tienePermiso('asistencia.configurar')
            || tienePermiso('planilla.calcular');
        if (!$permitido) {
            http_response_code(403);
            require __DIR__ . '/../Views/errores/403.php';
            exit;
        }
    }

    private function modelo(): CalendarioInstitucionalModel
    {
        global $conexion;
        return new CalendarioInstitucionalModel($conexion);
    }

    private function anioRedireccion(array $datos): int
    {
        $fecha = $datos['fecha'] ?? '';
        return preg_match('/^(\d{4})-/', $fecha, $coincidencia) ? (int) $coincidencia[1] : (int) date('Y');
    }

    public function index(): void
    {
        $this->autorizarGestion();
        $anio = (int) ($_GET['anio'] ?? date('Y'));
        if ($anio < 2000 || $anio > 2100) {
            $anio = (int) date('Y');
        }
        extract($this->modelo()->datos($anio));
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/calendario_institucional/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function guardar(): void
    {
        $this->autorizarGestion();
        validarCsrf();
        $anio = $this->anioRedireccion($_POST);
        try {
            $modelo = $this->modelo();
            $id = $modelo->guardar($_POST, (int) ($_SESSION['usuario']['id_usuario'] ?? 0));
            global $conexion;
            (new AuditoriaService($conexion))->registrar('CREAR', 'calendario_institucional', $id, null, $modelo->buscar($id));
            mensaje('success', 'Fecha institucional registrada correctamente.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=calendario_institucional&anio=' . $anio);
        exit;
    }

    public function actualizar(): void
    {
        $this->autorizarGestion();
        validarCsrf();
        $id = (int) ($_POST['id_calendario'] ?? 0);
        $anio = $this->anioRedireccion($_POST);
        try {
            $modelo = $this->modelo();
            $antes = $modelo->buscar($id);
            $modelo->actualizar($id, $_POST, (int) ($_SESSION['usuario']['id_usuario'] ?? 0));
            global $conexion;
            (new AuditoriaService($conexion))->registrar('ACTUALIZAR', 'calendario_institucional', $id, $antes, $modelo->buscar($id));
            mensaje('success', 'Fecha institucional actualizada correctamente.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=calendario_institucional&anio=' . $anio);
        exit;
    }

    public function estado(): void
    {
        $this->autorizarGestion();
        validarCsrf();
        $id = (int) ($_POST['id_calendario'] ?? 0);
        $estado = $_POST['estado'] ?? '';
        $anio = (int) ($_POST['anio'] ?? date('Y'));
        try {
            $modelo = $this->modelo();
            $antes = $modelo->buscar($id);
            $modelo->cambiarEstado($id, $estado, (int) ($_SESSION['usuario']['id_usuario'] ?? 0));
            global $conexion;
            (new AuditoriaService($conexion))->registrar('CAMBIAR_ESTADO', 'calendario_institucional', $id, $antes, $modelo->buscar($id));
            mensaje('success', $estado === 'ACTIVO' ? 'Fecha institucional activada.' : 'Fecha institucional desactivada.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=calendario_institucional&anio=' . $anio);
        exit;
    }
}
