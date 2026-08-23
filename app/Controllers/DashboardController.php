<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/DashboardModel.php';

class DashboardController
{
    public function index(): void
    {
        global $conexion;
        $model = new DashboardModel($conexion);

        $puedeAsistencia = tienePermiso('asistencia.ver');
        $puedeIncidencias = tienePermiso('incidencias.ver');
        $puedeHorarios = tienePermiso('horarios.ver');
        $puedeMatriculas = tienePermiso('matriculas.ver');
        $puedeFinanzas = tienePermiso('cobros.gestionar') || tienePermiso('caja.rectificar');
        $puedePlanilla = tienePermiso('planilla.calcular');

        $periodoActivo = $model->periodoActivo();
        $idPeriodoActivo = $periodoActivo ? (int) $periodoActivo['id_periodo_academico'] : null;
        $poblacion = $model->poblacion($idPeriodoActivo);
        $asistencia = $puedeAsistencia ? $model->asistenciaHoy() : [];
        $tendencia = $puedeAsistencia ? $model->tendenciaAlumnos() : [];
        $cierres = $puedeAsistencia ? $model->cierresAsistenciaHoy() : [];
        $incidenciasPendientes = $puedeIncidencias ? $model->incidenciasPendientes() : 0;
        $sinProgramacion = $puedeHorarios ? $model->personalSinProgramacion() : 0;
        $matriculas = $puedeMatriculas ? $model->matriculas($idPeriodoActivo) : [];
        $finanzas = $puedeFinanzas ? $model->finanzasMes() : [];
        $caja = $puedeFinanzas ? $model->cajaActual() : null;
        $planilla = $puedePlanilla ? $model->planillaActual() : null;

        $alertas = [];
        if ($puedeAsistencia && empty($cierres['ALUMNOS'])) {
            $alertas[] = ['nivel' => 'warning', 'icono' => 'bi-calendar-x', 'titulo' => 'Asistencia de alumnos sin cerrar', 'detalle' => 'El cierre de hoy todavía está pendiente.', 'url' => 'asistencia_hoy'];
        }
        if ($puedeAsistencia && empty($cierres['PERSONAL'])) {
            $alertas[] = ['nivel' => 'warning', 'icono' => 'bi-person-exclamation', 'titulo' => 'Asistencia de personal sin cerrar', 'detalle' => 'Revise las marcaciones antes del cierre.', 'url' => 'asistencia_personal'];
        }
        if ($puedeIncidencias && $incidenciasPendientes > 0) {
            $alertas[] = ['nivel' => 'info', 'icono' => 'bi-chat-left-text', 'titulo' => $incidenciasPendientes . ' incidencia' . ($incidenciasPendientes === 1 ? '' : 's') . ' pendiente' . ($incidenciasPendientes === 1 ? '' : 's'), 'detalle' => 'Requieren aprobación o rechazo.', 'url' => 'incidencias'];
        }
        if ($puedeHorarios && $sinProgramacion > 0) {
            $alertas[] = ['nivel' => 'danger', 'icono' => 'bi-calendar2-minus', 'titulo' => $sinProgramacion . ' trabajador' . ($sinProgramacion === 1 ? '' : 'es') . ' sin programación', 'detalle' => 'No tienen jornada ni sesiones activas.', 'url' => 'horarios'];
        }
        if ($puedeMatriculas && ($matriculas['sin_apoderado'] ?? 0) > 0) {
            $cantidad = (int) $matriculas['sin_apoderado'];
            $alertas[] = ['nivel' => 'danger', 'icono' => 'bi-people', 'titulo' => $cantidad . ' alumno' . ($cantidad === 1 ? '' : 's') . ' sin apoderado', 'detalle' => 'Complete el vínculo familiar pendiente.', 'url' => 'alumnos'];
        }
        if ($puedeFinanzas && ($finanzas['cuentas_vencidas'] ?? 0) > 0) {
            $alertas[] = ['nivel' => 'danger', 'icono' => 'bi-exclamation-circle', 'titulo' => $finanzas['cuentas_vencidas'] . ' cuenta' . ($finanzas['cuentas_vencidas'] === 1 ? '' : 's') . ' vencida' . ($finanzas['cuentas_vencidas'] === 1 ? '' : 's'), 'detalle' => 'Deuda acumulada: S/ ' . number_format($finanzas['deuda_vencida'], 2), 'url' => 'cobros'];
        }
        if ($puedeFinanzas && !$caja) {
            $alertas[] = ['nivel' => 'neutral', 'icono' => 'bi-lock', 'titulo' => 'No hay un turno de caja abierto', 'detalle' => 'Abra la caja antes de registrar operaciones.', 'url' => 'cobros_cierre_caja'];
        } elseif ($puedeFinanzas && $caja['fecha'] !== date('Y-m-d')) {
            $alertas[] = ['nivel' => 'danger', 'icono' => 'bi-lock', 'titulo' => 'Existe una caja anterior abierta', 'detalle' => 'Debe cerrar el turno del ' . date('d/m/Y', strtotime($caja['fecha'])) . '.', 'url' => 'cobros_cierre_caja'];
        }
        if ($puedePlanilla && !$planilla) {
            $alertas[] = ['nivel' => 'neutral', 'icono' => 'bi-wallet2', 'titulo' => 'Planilla del mes sin crear', 'detalle' => 'Aún no existe un período de pago para este mes.', 'url' => 'planilla'];
        } elseif ($puedePlanilla && $planilla['estado'] === 'CERRADO' && (float) $planilla['saldo_pendiente'] > 0) {
            $urlPlanilla = tienePermiso('planilla.cerrar') ? 'planilla_pagos&id=' . (int) $planilla['id_periodo_pago'] : 'planilla';
            $alertas[] = ['nivel' => 'warning', 'icono' => 'bi-wallet2', 'titulo' => 'Planilla cerrada con pagos pendientes', 'detalle' => 'Saldo por pagar: S/ ' . number_format($planilla['saldo_pendiente'], 2) . '.', 'url' => $urlPlanilla];
        } elseif ($puedePlanilla && $planilla['estado'] === 'FINALIZADO' && (float) $planilla['monto_excluido'] > 0) {
            $alertas[] = ['nivel' => 'info', 'icono' => 'bi-person-dash', 'titulo' => 'Planilla finalizada con exclusiones', 'detalle' => 'Importe no pagable documentado: S/ ' . number_format($planilla['monto_excluido'], 2) . '.', 'url' => 'planilla'];
        } elseif ($puedePlanilla && !in_array($planilla['estado'], ['CERRADO', 'PAGADO', 'FINALIZADO'], true)) {
            $alertas[] = ['nivel' => 'info', 'icono' => 'bi-wallet2', 'titulo' => 'Planilla ' . strtolower($planilla['estado']), 'detalle' => 'Continúe la revisión del período ' . $planilla['nombre'] . '.', 'url' => 'planilla'];
        }

        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/dashboard/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }
}
