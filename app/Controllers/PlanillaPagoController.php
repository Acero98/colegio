<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PlanillaPagoModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class PlanillaPagoController
{
    private function modelo(): PlanillaPagoModel
    {
        global $conexion;
        return new PlanillaPagoModel($conexion);
    }

    public function index(): void
    {
        autorizar('planilla.cerrar');
        try {
            extract($this->modelo()->datos((int) ($_GET['id'] ?? 0)));
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
            header('Location:index.php?url=planilla');
            exit;
        }
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/planilla/pagos.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function registrar(): void
    {
        autorizar('planilla.cerrar');
        validarCsrf();
        $id = (int) ($_POST['id_periodo_pago'] ?? 0);
        try {
            $id = $this->modelo()->registrar($_POST, (int) $_SESSION['usuario']['id_usuario']);
            mensaje('success', 'Pago de planilla registrado.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=planilla_pagos&id=' . $id);
        exit;
    }

    public function anular(): void
    {
        autorizar('planilla.cerrar');
        validarCsrf();
        $id = (int) ($_POST['id_periodo_pago'] ?? 0);
        try {
            $id = $this->modelo()->anular((int) ($_POST['id_pago_planilla'] ?? 0), $_POST['motivo_anulacion'] ?? '', (int) $_SESSION['usuario']['id_usuario']);
            mensaje('success', 'Pago anulado y saldo restaurado.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=planilla_pagos&id=' . $id);
        exit;
    }

    public function decision(): void
    {
        autorizar('planilla.cerrar');
        validarCsrf();
        $idPeriodo = (int) ($_POST['id_periodo_pago'] ?? 0);
        $idDetalle = (int) ($_POST['id_planilla_detalle'] ?? 0);
        try {
            $resultado = $this->modelo()->decidir($idDetalle, $_POST['decision_pago'] ?? '', $_POST['decision_motivo'] ?? '', (int) $_SESSION['usuario']['id_usuario']);
            $idPeriodo = $resultado['id_periodo_pago'];
            global $conexion;
            (new AuditoriaService($conexion))->registrar('DECISION_PAGO_PLANILLA', 'planilla_detalles', $idDetalle, ['decision' => $resultado['anterior']], ['decision' => $resultado['decision'], 'motivo' => $resultado['motivo']]);
            $mensajes = ['RETENIDO' => 'Pago retenido. El saldo permanece pendiente.', 'EXCLUIDO' => 'Saldo excluido del pago con trazabilidad.', 'PENDIENTE' => 'Pago reactivado correctamente.'];
            mensaje('success', $mensajes[$resultado['decision']] ?? 'Decisión actualizada.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=planilla_pagos&id=' . $idPeriodo);
        exit;
    }
}
