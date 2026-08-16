<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/CobroModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class CobroController
{
    public function index(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $model = new CobroModel($conexion);
        $mes = preg_match('/^\d{4}-\d{2}$/', $_GET['mes'] ?? '') ? $_GET['mes'] : date('Y-m');
        $opciones = $model->opciones();
        $grados = $model->grados();
        $cuentas = $model->listarCuentas($mes);
        $resumen = $model->resumen($mes);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function generar(): void
    {
        autorizar('cobros.gestionar'); validarCsrf(); global $conexion;
        try {
            $total = (new CobroModel($conexion))->generarMensualidades($_POST);
            (new AuditoriaService($conexion))->registrar('GENERAR', 'cuentas_cobrar', null, null, $_POST);
            mensaje('success', $total . ' cuenta(s) generada(s). Las cuentas existentes no se duplicaron.');
        } catch (Throwable $e) { mensaje('error', 'No se pudieron generar las cuentas. Verifique que exista una tarifa vigente.'); }
        header('Location:index.php?url=cobros&mes=' . urlencode($_POST['mes'])); exit;
    }

    public function pagar(): void
    {
        autorizar('cobros.gestionar'); validarCsrf(); global $conexion;
        try {
            $recibo = (new CobroModel($conexion))->registrarPago($_POST, (int) $_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('COBRAR', 'pagos_alumno', null, null, $_POST);
            mensaje('success', 'Pago registrado. Recibo interno: ' . $recibo);
        } catch (Throwable $e) { mensaje('error', $e->getMessage()); }
        header('Location:index.php?url=cobros&mes=' . urlencode($_POST['mes'])); exit;
    }

    public function configurar(): void
    {
        autorizar('cobros.gestionar'); validarCsrf(); global $conexion;
        try {
            (new CobroModel($conexion))->guardarConfiguracion($_POST);
            mensaje('success', ($_POST['accion'] ?? '') === 'concepto' ? 'Concepto creado.' : 'Tarifa registrada.');
        } catch (Throwable $e) { mensaje('error', 'No se pudo guardar. Revise los datos de la tarifa.'); }
        header('Location:index.php?url=cobros'); exit;
    }
}
