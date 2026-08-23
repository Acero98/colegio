<?php
$nombreUsuario = trim((string) ($_SESSION['usuario']['nombre'] ?? '')) ?: ($_SESSION['usuario']['rol'] ?? 'Usuario');
$alumnosPresentes = (int) ($asistencia['alumnos_presentes'] ?? 0);
$alumnosTotal = (int) ($poblacion['alumnos'] ?? 0);
$porcentajeAsistencia = $alumnosTotal > 0 ? min(100, round(($alumnosPresentes / $alumnosTotal) * 100)) : 0;
$personalPresentes = (int) ($asistencia['personal_presentes'] ?? 0);
$personalTotal = (int) ($poblacion['personal'] ?? 0);
$alertaEstilos = [
    'danger' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'icon' => 'bg-rose-100 text-rose-700', 'title' => 'text-rose-900'],
    'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => 'bg-amber-100 text-amber-700', 'title' => 'text-amber-900'],
    'info' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => 'bg-blue-100 text-blue-700', 'title' => 'text-blue-900'],
    'neutral' => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'icon' => 'bg-slate-200 text-slate-700', 'title' => 'text-slate-900'],
];
$accionesRapidas = [];
if (tienePermiso('asistencia.escanear')) $accionesRapidas[] = ['asistencia_escanear', 'bi-qr-code-scan', 'Escanear QR', 'bg-blue-50 text-blue-700'];
if (tienePermiso('asistencia.ver')) $accionesRapidas[] = ['asistencia_manual', 'bi-clipboard2-check', 'Registro manual', 'bg-violet-50 text-violet-700'];
if (tienePermiso('matriculas.crear')) $accionesRapidas[] = ['matriculas', 'bi-journal-plus', 'Nueva matrícula', 'bg-cyan-50 text-cyan-700'];
if (tienePermiso('cobros.gestionar')) $accionesRapidas[] = ['cobros', 'bi-cash-coin', 'Registrar cobro', 'bg-emerald-50 text-emerald-700'];
if (tienePermiso('cobros.gestionar')) $accionesRapidas[] = ['finanzas_movimientos', 'bi-arrow-left-right', 'Ingreso o egreso', 'bg-amber-50 text-amber-700'];
if (tienePermiso('cobros.gestionar') || tienePermiso('caja.rectificar')) $accionesRapidas[] = ['cobros_cierre_caja', 'bi-safe2', 'Gestionar caja', 'bg-rose-50 text-rose-700'];
if (tienePermiso('planilla.calcular')) $accionesRapidas[] = ['planilla', 'bi-wallet2', 'Gestionar planilla', 'bg-indigo-50 text-indigo-700'];
?>

<header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold text-blue-600">Resumen institucional</p>
        <h1 class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900">Bienvenido, <?= htmlspecialchars($nombreUsuario) ?></h1>
        <p class="mt-1 text-sm text-slate-500">Este es el estado operativo del colegio para hoy.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <?php if ($periodoActivo): ?>
            <span class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-800">
                <i class="bi bi-mortarboard"></i><?= htmlspecialchars($periodoActivo['nombre']) ?>
            </span>
        <?php else: ?>
            <span class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700"><i class="bi bi-exclamation-triangle"></i> Sin período activo</span>
        <?php endif; ?>
        <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm ring-1 ring-slate-200"><i class="bi bi-calendar3 text-blue-600"></i><?= date('d/m/Y') ?></span>
    </div>
</header>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3"><div><p class="text-sm font-semibold text-blue-800">Alumnos matriculados</p><strong class="mt-2 block text-3xl text-slate-950"><?= number_format($alumnosTotal) ?></strong></div><span class="grid h-11 w-11 place-items-center rounded-xl bg-blue-100 text-xl text-blue-700"><i class="bi bi-backpack2"></i></span></div>
        <p class="mt-3 text-xs text-slate-500"><?= $periodoActivo ? 'Período ' . htmlspecialchars($periodoActivo['nombre']) : 'Requiere un período académico activo' ?></p>
    </article>

    <?php if ($puedeAsistencia): ?>
        <article class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3"><div><p class="text-sm font-semibold text-emerald-800">Alumnos presentes hoy</p><strong class="mt-2 block text-3xl text-slate-950"><?= $alumnosPresentes ?> <small class="text-base font-semibold text-slate-400">/ <?= $alumnosTotal ?></small></strong></div><span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-100 text-xl text-emerald-700"><i class="bi bi-calendar-check"></i></span></div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-emerald-100"><div class="h-full rounded-full bg-emerald-500" style="width:<?= $porcentajeAsistencia ?>%"></div></div>
            <p class="mt-2 text-xs font-medium text-emerald-700"><?= $porcentajeAsistencia ?>% ha registrado entrada</p>
        </article>

        <article class="rounded-2xl border border-violet-200 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3"><div><p class="text-sm font-semibold text-violet-800">Personal presente hoy</p><strong class="mt-2 block text-3xl text-slate-950"><?= $personalPresentes ?> <small class="text-base font-semibold text-slate-400">/ <?= $personalTotal ?></small></strong></div><span class="grid h-11 w-11 place-items-center rounded-xl bg-violet-100 text-xl text-violet-700"><i class="bi bi-person-workspace"></i></span></div>
            <p class="mt-3 text-xs text-slate-500"><?= number_format($poblacion['docentes']) ?> docentes · <?= number_format($poblacion['administrativos']) ?> administrativos activos</p>
        </article>
    <?php else: ?>
        <article class="rounded-2xl border border-violet-200 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm"><div class="flex items-start justify-between"><div><p class="text-sm font-semibold text-violet-800">Docentes activos</p><strong class="mt-2 block text-3xl text-slate-950"><?= number_format($poblacion['docentes']) ?></strong></div><span class="grid h-11 w-11 place-items-center rounded-xl bg-violet-100 text-xl text-violet-700"><i class="bi bi-person-workspace"></i></span></div></article>
        <article class="rounded-2xl border border-cyan-200 bg-gradient-to-br from-cyan-50 to-white p-5 shadow-sm"><div class="flex items-start justify-between"><div><p class="text-sm font-semibold text-cyan-800">Administrativos activos</p><strong class="mt-2 block text-3xl text-slate-950"><?= number_format($poblacion['administrativos']) ?></strong></div><span class="grid h-11 w-11 place-items-center rounded-xl bg-cyan-100 text-xl text-cyan-700"><i class="bi bi-briefcase"></i></span></div></article>
    <?php endif; ?>

    <article class="rounded-2xl border <?= count($alertas) ? 'border-amber-200 bg-gradient-to-br from-amber-50 to-white' : 'border-emerald-200 bg-gradient-to-br from-emerald-50 to-white' ?> p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3"><div><p class="text-sm font-semibold <?= count($alertas) ? 'text-amber-800' : 'text-emerald-800' ?>">Pendientes operativos</p><strong class="mt-2 block text-3xl text-slate-950"><?= count($alertas) ?></strong></div><span class="grid h-11 w-11 place-items-center rounded-xl <?= count($alertas) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?> text-xl"><i class="bi <?= count($alertas) ? 'bi-bell' : 'bi-check2-circle' ?>"></i></span></div>
        <p class="mt-3 text-xs text-slate-500"><?= count($alertas) ? 'Requieren revisión o seguimiento' : 'No existen alertas para atender' ?></p>
    </article>
</section>

<section class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(340px,.65fr)]">
    <?php if ($puedeAsistencia): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div><h2 class="font-bold text-slate-900">Asistencia de alumnos</h2><p class="mt-1 text-sm text-slate-500">Resultados de hoy y comportamiento de los últimos siete días.</p></div>
                <a href="index.php?url=asistencia_historial" class="rounded-lg bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">Ver historial</a>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl bg-emerald-50 p-3"><span class="text-xs font-semibold text-emerald-700">Puntuales</span><strong class="mt-1 block text-xl text-emerald-900"><?= (int) ($asistencia['alumnos_puntuales'] ?? 0) ?></strong></div>
                <div class="rounded-xl bg-amber-50 p-3"><span class="text-xs font-semibold text-amber-700">Tardanzas</span><strong class="mt-1 block text-xl text-amber-900"><?= (int) ($asistencia['alumnos_tardanzas'] ?? 0) ?></strong></div>
                <div class="rounded-xl bg-rose-50 p-3"><span class="text-xs font-semibold text-rose-700">Faltas</span><strong class="mt-1 block text-xl text-rose-900"><?= (int) ($asistencia['alumnos_faltas'] ?? 0) ?></strong></div>
                <div class="rounded-xl bg-violet-50 p-3"><span class="text-xs font-semibold text-violet-700">Justificados</span><strong class="mt-1 block text-xl text-violet-900"><?= (int) ($asistencia['alumnos_justificados'] ?? 0) ?></strong></div>
            </div>
            <div class="mt-5 space-y-3">
                <?php $maxTendencia = max(1, ...array_map(fn($fila) => max((int) $fila['presentes'], (int) $fila['faltas']), $tendencia ?: [['presentes' => 0, 'faltas' => 0]])); ?>
                <?php foreach ($tendencia as $dia): ?>
                    <div class="grid grid-cols-[52px_minmax(0,1fr)_95px] items-center gap-3 text-sm">
                        <span class="font-medium text-slate-600"><?= date('d/m', strtotime($dia['fecha'])) ?></span>
                        <div class="space-y-1.5"><div class="h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-500" style="width:<?= round(((int) $dia['presentes'] / $maxTendencia) * 100) ?>%"></div></div><div class="flex gap-3 text-[11px] text-slate-500"><span class="text-emerald-700"><?= (int) $dia['puntuales'] ?> punt.</span><span class="text-amber-700"><?= (int) $dia['tardanzas'] ?> tard.</span><span class="text-rose-700"><?= (int) $dia['faltas'] ?> falt.</span></div></div>
                        <span class="text-right font-semibold text-slate-700"><?= (int) $dia['presentes'] ?> presentes</span>
                    </div>
                <?php endforeach; ?>
                <?php if (!$tendencia): ?><p class="py-8 text-center text-sm text-slate-500">Sin registros de alumnos en los últimos siete días.</p><?php endif; ?>
            </div>
            <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4 text-xs font-semibold">
                <span class="rounded-full px-3 py-1.5 <?= !empty($cierres['ALUMNOS']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>"><i class="bi <?= !empty($cierres['ALUMNOS']) ? 'bi-check-circle' : 'bi-hourglass-split' ?>"></i> Alumnos: <?= !empty($cierres['ALUMNOS']) ? 'cerrado' : 'pendiente' ?></span>
                <span class="rounded-full px-3 py-1.5 <?= !empty($cierres['PERSONAL']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>"><i class="bi <?= !empty($cierres['PERSONAL']) ? 'bi-check-circle' : 'bi-hourglass-split' ?>"></i> Personal: <?= !empty($cierres['PERSONAL']) ? 'cerrado' : 'pendiente' ?></span>
                <?php if (($asistencia['personal_incompletos'] ?? 0) > 0): ?><span class="rounded-full bg-rose-100 px-3 py-1.5 text-rose-800"><i class="bi bi-clock"></i> <?= (int) $asistencia['personal_incompletos'] ?> sin salida</span><?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm <?= !$puedeAsistencia ? 'xl:col-span-2' : '' ?>">
        <div class="flex items-center justify-between gap-3"><div><h2 class="font-bold text-slate-900">Alertas y pendientes</h2><p class="mt-1 text-sm text-slate-500">Situaciones que requieren atención.</p></div><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600"><?= count($alertas) ?></span></div>
        <div class="mt-5 space-y-3">
            <?php foreach ($alertas as $alerta): $estilo = $alertaEstilos[$alerta['nivel']] ?? $alertaEstilos['neutral']; ?>
                <a href="index.php?url=<?= htmlspecialchars($alerta['url']) ?>" class="flex items-start gap-3 rounded-xl border <?= $estilo['border'] ?> <?= $estilo['bg'] ?> p-3.5 transition hover:-translate-y-0.5 hover:shadow-sm">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg <?= $estilo['icon'] ?>"><i class="bi <?= htmlspecialchars($alerta['icono']) ?>"></i></span>
                    <span class="min-w-0"><strong class="block text-sm <?= $estilo['title'] ?>"><?= htmlspecialchars($alerta['titulo']) ?></strong><small class="mt-0.5 block leading-5 text-slate-600"><?= htmlspecialchars($alerta['detalle']) ?></small></span>
                    <i class="bi bi-chevron-right ml-auto mt-2 text-xs text-slate-400"></i>
                </a>
            <?php endforeach; ?>
            <?php if (!$alertas): ?><div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-8 text-center"><i class="bi bi-check2-circle text-3xl text-emerald-600"></i><strong class="mt-2 block text-sm text-emerald-900">Todo está al día</strong><p class="mt-1 text-xs text-emerald-700">No existen pendientes para su perfil.</p></div><?php endif; ?>
        </div>
    </article>
</section>

<?php if ($puedeFinanzas || $puedeMatriculas): ?>
<section class="mt-6 grid gap-5 xl:grid-cols-2">
    <?php if ($puedeFinanzas): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="font-bold text-slate-900">Resumen financiero del mes</h2><p class="mt-1 text-sm text-slate-500">Cobros, movimientos adicionales y resultado neto.</p></div><?php if (tienePermiso('reportes.ver')): ?><a href="index.php?url=reportes_financieros" class="text-sm font-semibold text-blue-600">Ver reportes <i class="bi bi-arrow-right"></i></a><?php endif; ?></div>
            <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-xl bg-emerald-50 p-3.5"><p class="text-xs font-semibold text-emerald-700">Cobros</p><strong class="mt-1 block text-lg text-emerald-900">S/ <?= number_format($finanzas['cobros_mes'], 2) ?></strong></div>
                <div class="rounded-xl bg-blue-50 p-3.5"><p class="text-xs font-semibold text-blue-700">Otros ingresos</p><strong class="mt-1 block text-lg text-blue-900">S/ <?= number_format($finanzas['otros_ingresos'], 2) ?></strong></div>
                <div class="rounded-xl bg-rose-50 p-3.5"><p class="text-xs font-semibold text-rose-700">Egresos</p><strong class="mt-1 block text-lg text-rose-900">S/ <?= number_format($finanzas['egresos'], 2) ?></strong></div>
                <div class="rounded-xl bg-violet-50 p-3.5"><p class="text-xs font-semibold text-violet-700">Balance neto</p><strong class="mt-1 block text-lg <?= $finanzas['balance'] >= 0 ? 'text-violet-900' : 'text-rose-700' ?>">S/ <?= number_format($finanzas['balance'], 2) ?></strong></div>
            </div>
            <div class="mt-4 flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cobrado hoy</p><strong class="mt-1 block text-xl text-slate-900">S/ <?= number_format($finanzas['cobros_hoy'], 2) ?></strong></div>
                <div class="sm:text-right"><p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Deuda vencida</p><strong class="mt-1 block text-xl text-rose-800">S/ <?= number_format($finanzas['deuda_vencida'], 2) ?></strong><small class="text-slate-500"><?= number_format($finanzas['cuentas_vencidas']) ?> cuentas</small></div>
            </div>
            <div class="mt-4 rounded-xl border <?= $caja ? ($caja['fecha'] === date('Y-m-d') ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50') : 'border-slate-200 bg-slate-50' ?> p-4">
                <div class="flex items-center justify-between gap-3"><div><p class="text-sm font-bold <?= $caja ? ($caja['fecha'] === date('Y-m-d') ? 'text-emerald-900' : 'text-rose-900') : 'text-slate-800' ?>"><i class="bi <?= $caja ? 'bi-unlock' : 'bi-lock' ?>"></i> <?= $caja ? 'Caja abierta' : 'Caja cerrada' ?></p><?php if ($caja): ?><p class="mt-1 text-xs text-slate-600">Responsable: <?= htmlspecialchars($caja['responsable']) ?> · <?= date('d/m/Y H:i', strtotime($caja['abierto_en'])) ?></p><?php else: ?><p class="mt-1 text-xs text-slate-500">No se pueden registrar operaciones hasta abrir un turno.</p><?php endif; ?></div><?php if ($caja): ?><div class="text-right"><p class="text-xs text-slate-500">Esperado</p><strong class="text-lg text-slate-900">S/ <?= number_format($caja['esperado'], 2) ?></strong></div><?php endif; ?></div>
            </div>
        </article>
    <?php endif; ?>

    <?php if ($puedeMatriculas): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="font-bold text-slate-900">Matrícula y capacidad</h2><p class="mt-1 text-sm text-slate-500"><?= $periodoActivo ? 'Ocupación del período ' . htmlspecialchars($periodoActivo['nombre']) : 'No existe un período académico activo.' ?></p></div><a href="index.php?url=matriculas" class="text-sm font-semibold text-blue-600">Ver matrículas <i class="bi bi-arrow-right"></i></a></div>
            <?php $ocupacion = ($matriculas['capacidad'] ?? 0) > 0 ? min(100, round(($matriculas['matriculados'] / $matriculas['capacidad']) * 100)) : 0; ?>
            <div class="mt-5 rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 p-5 text-white">
                <div class="flex items-end justify-between gap-4"><div><p class="text-sm text-blue-100">Ocupación institucional</p><strong class="mt-1 block text-3xl"><?= $ocupacion ?>%</strong></div><div class="text-right text-sm"><span class="block text-blue-100">Capacidad registrada</span><strong class="text-xl"><?= number_format($matriculas['capacidad'] ?? 0) ?></strong></div></div>
                <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-white/25"><div class="h-full rounded-full bg-white" style="width:<?= $ocupacion ?>%"></div></div>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-xl bg-blue-50 p-3"><strong class="block text-xl text-blue-900"><?= number_format($matriculas['matriculados'] ?? 0) ?></strong><span class="text-xs font-medium text-blue-700">Matriculados</span></div>
                <div class="rounded-xl bg-cyan-50 p-3"><strong class="block text-xl text-cyan-900"><?= number_format($matriculas['vacantes'] ?? 0) ?></strong><span class="text-xs font-medium text-cyan-700">Vacantes</span></div>
                <div class="rounded-xl <?= ($matriculas['sin_apoderado'] ?? 0) > 0 ? 'bg-rose-50' : 'bg-emerald-50' ?> p-3"><strong class="block text-xl <?= ($matriculas['sin_apoderado'] ?? 0) > 0 ? 'text-rose-900' : 'text-emerald-900' ?>"><?= number_format($matriculas['sin_apoderado'] ?? 0) ?></strong><span class="text-xs font-medium <?= ($matriculas['sin_apoderado'] ?? 0) > 0 ? 'text-rose-700' : 'text-emerald-700' ?>">Sin apoderado</span></div>
            </div>
            <?php if ($periodoActivo): ?><p class="mt-4 text-xs text-slate-500"><i class="bi bi-calendar-range text-blue-600"></i> Vigencia: <?= date('d/m/Y', strtotime($periodoActivo['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($periodoActivo['fecha_fin'])) ?></p><?php endif; ?>
        </article>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($accionesRapidas): ?>
<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div><h2 class="font-bold text-slate-900">Acciones rápidas</h2><p class="mt-1 text-sm text-slate-500">Accesos disponibles de acuerdo con sus permisos.</p></div>
    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php foreach ($accionesRapidas as [$url, $icono, $texto, $color]): ?>
            <a href="index.php?url=<?= $url ?>" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-sm"><span class="grid h-10 w-10 place-items-center rounded-xl <?= $color ?>"><i class="bi <?= $icono ?>"></i></span><strong class="text-sm text-slate-700 group-hover:text-blue-700"><?= htmlspecialchars($texto) ?></strong><i class="bi bi-arrow-right ml-auto text-slate-300 group-hover:text-blue-500"></i></a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
