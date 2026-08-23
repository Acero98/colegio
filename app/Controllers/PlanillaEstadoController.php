<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PlanillaEstadoModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class PlanillaEstadoController
{
    private function modelo(): PlanillaEstadoModel
    {
        global $conexion;
        return new PlanillaEstadoModel($conexion);
    }

    private function auditar(string $accion, int $id, array $antes, array $despues): void
    {
        global $conexion;
        (new AuditoriaService($conexion))->registrar($accion, 'periodos_pago', $id, $antes, $despues);
    }

    public function aprobar(): void
    {
        autorizar('planilla.calcular');
        validarCsrf();
        $id = (int) ($_POST['id_periodo_pago'] ?? 0);
        try {
            $this->modelo()->aprobar($id, (int) $_SESSION['usuario']['id_usuario']);
            $this->auditar('APROBAR_REVISION_PLANILLA', $id, ['estado' => 'CALCULADO'], ['estado' => 'REVISADO']);
            mensaje('success', 'Revisión aprobada. La planilla quedó protegida.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=planilla_revision&id=' . $id);
        exit;
    }

    public function cerrar(): void
    {
        autorizar('planilla.cerrar');
        validarCsrf();
        $id = (int) ($_POST['id_periodo_pago'] ?? 0);
        try {
            $this->modelo()->cerrar($id, (int) $_SESSION['usuario']['id_usuario']);
            $this->auditar('CERRAR_CALCULO_PLANILLA', $id, ['estado' => 'REVISADO'], ['estado' => 'CERRADO']);
            mensaje('success', 'Cálculo de planilla cerrado. Ya puede registrar los pagos.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=planilla_revision&id=' . $id);
        exit;
    }

    public function reabrir(): void
    {
        autorizar('planilla.cerrar');
        validarCsrf();
        $id = (int) ($_POST['id_periodo_pago'] ?? 0);
        $motivo = trim((string) ($_POST['motivo_reapertura'] ?? ''));
        try {
            $this->modelo()->reabrir($id, $motivo, (int) $_SESSION['usuario']['id_usuario']);
            $this->auditar('REABRIR_CALCULO_PLANILLA', $id, ['estado' => $_POST['estado_anterior'] ?? 'CERRADO'], ['estado' => 'CALCULADO', 'motivo' => $motivo]);
            mensaje('success', 'Cálculo reabierto. Puede corregirlo, recalcularlo y aprobarlo nuevamente.');
        } catch (Throwable $e) {
            mensaje('error', $e->getMessage());
        }
        header('Location:index.php?url=planilla_revision&id=' . $id);
        exit;
    }
}
