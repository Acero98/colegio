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
        $mes = ($_GET['mes'] ?? date('Y-m')) === 'todos' ? '' : (preg_match('/^\d{4}-\d{2}$/', $_GET['mes'] ?? '') ? $_GET['mes'] : date('Y-m'));
        $estado = $_GET['estado'] ?? '';
        $buscar = trim($_GET['buscar'] ?? '');
        $idAlumno = (int) ($_GET['alumno'] ?? 0);
        $abrirConfiguracion = ($_GET['abrir'] ?? '') === 'tarifas';
        $opciones = $model->opciones();
        $grados = $model->grados();
        $cuentas = $model->listarCuentas($mes, $estado, $buscar);
        $resumen = $model->resumen($mes);
        $alertas = $model->alertasCobro();
        $estadoCuenta = $idAlumno ? $model->estadoCuentaAlumno($idAlumno) : [];
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function generar(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            $total = (new CobroModel($conexion))->generarMensualidades($_POST);
            (new AuditoriaService($conexion))->registrar('GENERAR', 'cuentas_cobrar', null, null, $_POST);
            mensaje('success', $total . ' cuenta(s) generada(s). Las cuentas existentes no se duplicaron.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros&mes=' . urlencode($_POST['mes']));
        exit;
    }

    public function pagar(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            $recibo = (new CobroModel($conexion))->registrarPago($_POST, (int) $_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('COBRAR', 'pagos_alumno', null, null, $_POST);
            mensaje('success', 'Pago registrado. Recibo interno: ' . $recibo);
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros&mes=' . urlencode($_POST['mes']));
        exit;
    }

    public function configurar(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->guardarConfiguracion($_POST);
            mensaje('success', ($_POST['accion'] ?? '') === 'concepto' ? 'Concepto creado.' : 'Tarifa registrada.');
        } catch (Throwable $e) {
            mensaje('error', 'No se pudo guardar. Revise los datos de la tarifa.');
        }
        header('Location:index.php?url=cobros_tarifas');
        exit;
    }

    public function tarifas(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $modelo = new CobroModel($conexion);
        $tarifas = $modelo->tarifas();
        $conceptos = $modelo->conceptos();
        $opciones = $modelo->opciones();
        $grados = $modelo->grados();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/tarifas.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function cambiarEstadoTarifa(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->cambiarEstadoTarifa((int) ($_POST['id_tarifa'] ?? 0), $_POST['estado'] ?? '');
            (new AuditoriaService($conexion))->registrar('CAMBIAR_ESTADO', 'tarifas_cobro', (int) ($_POST['id_tarifa'] ?? 0), null, $_POST);
            mensaje('success', ($_POST['estado'] ?? '') === 'ACTIVO' ? 'Tarifa activada.' : 'Tarifa desactivada. No se usará en nuevas generaciones.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros_tarifas');
        exit;
    }

    public function cambiarEstadoConcepto(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->cambiarEstadoConcepto((int) ($_POST['id_concepto_cobro'] ?? 0), $_POST['estado'] ?? '');
            (new AuditoriaService($conexion))->registrar('CAMBIAR_ESTADO', 'conceptos_cobro', (int) ($_POST['id_concepto_cobro'] ?? 0), null, $_POST);
            mensaje('success', ($_POST['estado'] ?? '') === 'ACTIVO' ? 'Concepto activado.' : 'Concepto desactivado. No estará disponible para nuevas generaciones.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros_tarifas');
        exit;
    }

    public function pagos(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $pagos = (new CobroModel($conexion))->pagos();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/pagos.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function anularPago(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->anularPago((int)($_POST['id_pago'] ?? 0), $_POST['motivo'] ?? '', (int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('ANULAR', 'pagos_alumno', (int)($_POST['id_pago'] ?? 0), null, $_POST);
            mensaje('success', 'Recibo anulado y saldos restaurados.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros_pagos');
        exit;
    }

    public function ajustarCuenta(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->ajustarCuenta((int)($_POST['id_cuenta'] ?? 0), $_POST['tipo'] ?? '', (float)($_POST['importe'] ?? 0), $_POST['motivo'] ?? '', (int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('AJUSTAR', 'cuentas_cobrar', (int)($_POST['id_cuenta'] ?? 0), null, $_POST);
            mensaje('success', 'Ajuste registrado y saldo recalculado.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros&mes=' . urlencode($_POST['mes'] ?? date('Y-m')));
        exit;
    }

    public function reportes(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $desde = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['desde'] ?? '') ? $_GET['desde'] : date('Y-m-01');
        $hasta = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['hasta'] ?? '') ? $_GET['hasta'] : date('Y-m-d');
        if ($desde > $hasta) {
            $temporal = $desde;
            $desde = $hasta;
            $hasta = $temporal;
        }
        $reportes = (new CobroModel($conexion))->reportesFinancieros($desde, $hasta);
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/reportes.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function estadoCuenta(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        try {
            $estadoCuenta = (new CobroModel($conexion))->estadoCuentaImprimible((int)($_GET['alumno'] ?? 0));
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
            header('Location:index.php?url=cobros');
            exit;
        }
        require __DIR__ . '/../Views/cobros/estado_cuenta.php';
    }

    public function cierreCaja(): void
    {
        autorizar('cobros.gestionar');
        global $conexion;
        $fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['fecha'] ?? '') ? $_GET['fecha'] : date('Y-m-d');
        $modelo = new CobroModel($conexion);
        $estadoCaja = $modelo->estadoCaja($fecha);
        $resumenCaja = $modelo->resumenCaja($fecha, (int)($estadoCaja['id_apertura'] ?? 0));
        $cajas = $modelo->cajasDiarias();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/cobros/cierre_caja.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function guardarCierreCaja(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->cerrarCaja($_POST, (int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('CERRAR_CAJA', 'cierres_caja', null, null, $_POST);
            mensaje('success', 'Caja cerrada correctamente.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros_cierre_caja&fecha=' . urlencode($_POST['fecha'] ?? date('Y-m-d')));
        exit;
    }

    public function guardarAperturaCaja(): void
    {
        autorizar('cobros.gestionar');
        validarCsrf();
        global $conexion;
        try {
            (new CobroModel($conexion))->abrirCaja($_POST, (int)$_SESSION['usuario']['id_usuario']);
            (new AuditoriaService($conexion))->registrar('ABRIR_CAJA', 'aperturas_caja', null, null, $_POST);
            mensaje('success', 'Caja abierta correctamente.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=cobros_cierre_caja&fecha=' . urlencode($_POST['fecha'] ?? date('Y-m-d')));
        exit;
    }
}
