<?php $rutaActual = $_GET['url'] ?? 'dashboard'; ?>
<div x-data="{ open:false, collapsed: localStorage.getItem('sidebar-collapsed') === 'true', init() { this.$watch('collapsed', value => localStorage.setItem('sidebar-collapsed', value)); } }">
    <header class="mobile-app-header sticky top-0 z-50 items-center justify-between border-b border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur">
        <a href="index.php?url=dashboard" class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 text-lg text-white shadow-sm"><i class="bi bi-mortarboard-fill"></i></span><span><strong class="block text-sm leading-tight">Colegio</strong><small class="text-xs text-slate-500">Asistencia y pagos</small></span></a>
        <button type="button" @click="open=!open" class="rounded-xl p-2 text-slate-600 hover:bg-slate-100" aria-label="Abrir menú"><i class="bi bi-list text-xl"></i></button>
    </header>

    <aside class="sidebar-panel border-r border-slate-200 bg-white" :class="{ 'sidebar-open': open, 'sidebar-collapsed': collapsed }">
        <div class="flex h-20 shrink-0 items-center gap-3 border-b border-slate-100 px-5">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 text-lg text-white shadow-sm"><i class="bi bi-mortarboard-fill"></i></span>
            <span class="sidebar-brand-text"><strong class="block text-sm leading-tight">Colegio</strong><small class="text-xs text-slate-500">Asistencia y pagos</small></span>
            <button type="button" @click="collapsed=!collapsed" class="sidebar-collapse-toggle" :title="collapsed ? 'Mostrar menú completo' : 'Mostrar solo iconos'" aria-label="Contraer o expandir menú"><i :class="collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'"></i></button>
            <button type="button" @click="open=false" class="ml-auto rounded-lg p-2 text-slate-400 hover:bg-slate-100 lg:hidden" aria-label="Cerrar menú"><i class="bi bi-x-lg"></i></button>
        </div>

        <nav class="sidebar-scroll flex-1 px-3 py-4 text-sm">
            <p class="sidebar-label">Principal</p>
            <a href="index.php?url=dashboard" class="sidebar-link <?= $rutaActual === 'dashboard' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-speedometer2"></i> Inicio</a>
            <?php if (($_SESSION['usuario']['rol'] ?? '') === 'Apoderado' && tienePermiso('portal.ver')): ?><a href="index.php?url=portal_familia" class="sidebar-link <?= $rutaActual === 'portal_familia' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-house-heart"></i> Mi familia</a><?php endif; ?>

            <?php if (tienePermiso('personas.ver') || tienePermiso('alumnos.ver') || tienePermiso('apoderados.ver') || tienePermiso('fotochecks.ver')): ?>
                <p class="sidebar-label mt-4">Personas</p>
                <?php if (tienePermiso('personas.ver')): ?><a href="index.php?url=personas" class="sidebar-link <?= $rutaActual === 'personas' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-people"></i> Personas</a><?php endif; ?>
                <?php if (tienePermiso('alumnos.ver')): ?><a href="index.php?url=alumnos" class="sidebar-link <?= $rutaActual === 'alumnos' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-mortarboard"></i> Alumnos</a><?php endif; ?>
                <?php if (tienePermiso('personas.ver')): ?><a href="index.php?url=docentes" class="sidebar-link <?= $rutaActual === 'docentes' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-person-workspace"></i> Docentes</a><a href="index.php?url=administrativos" class="sidebar-link <?= $rutaActual === 'administrativos' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-briefcase"></i> Administrativos</a><?php endif; ?>
                <?php if (tienePermiso('apoderados.ver')): ?><a href="index.php?url=apoderados" class="sidebar-link <?= $rutaActual === 'apoderados' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-person-hearts"></i> Apoderados</a><?php endif; ?>
                <?php if (tienePermiso('fotochecks.ver')): ?><a href="index.php?url=fotochecks" class="sidebar-link <?= $rutaActual === 'fotochecks' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-qr-code"></i> Fotochecks</a><?php endif; ?>
            <?php endif; ?>

            <?php if (tienePermiso('academico.ver') || tienePermiso('matriculas.ver')): ?>
                <p class="sidebar-label mt-4">Académico</p>
                <?php if (tienePermiso('academico.ver')): ?><a href="index.php?url=academico" class="sidebar-link <?= $rutaActual === 'academico' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-diagram-3"></i> Estructura académica</a><?php endif; ?>
                <?php if (tienePermiso('matriculas.ver')): ?><a href="index.php?url=matriculas" class="sidebar-link <?= $rutaActual === 'matriculas' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-journal-check"></i> Matrículas</a><?php endif; ?>
            <?php endif; ?>

            <?php if (tienePermiso('asistencia.escanear') || tienePermiso('asistencia.ver') || tienePermiso('horarios.ver') || tienePermiso('incidencias.ver') || tienePermiso('asistencia.configurar')): ?>
                <p class="sidebar-label mt-4">Asistencia</p>
                <?php if (tienePermiso('asistencia.escanear')): ?><a href="index.php?url=asistencia_escanear" class="sidebar-link <?= $rutaActual === 'asistencia_escanear' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-qr-code-scan"></i> Escanear QR</a><?php endif; ?>
                <?php if (tienePermiso('asistencia.ver')): ?><a href="index.php?url=asistencia_hoy" class="sidebar-link <?= $rutaActual === 'asistencia_hoy' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-calendar-check"></i> Asistencia de hoy</a><a href="index.php?url=asistencia_manual" class="sidebar-link <?= $rutaActual === 'asistencia_manual' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-clipboard2-check"></i> Registro manual</a><a href="index.php?url=asistencia_personal" class="sidebar-link <?= $rutaActual === 'asistencia_personal' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-person-workspace"></i> Asistencia de personal</a><a href="index.php?url=asistencia_historial" class="sidebar-link <?= $rutaActual === 'asistencia_historial' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-clock-history"></i> Historial</a><?php endif; ?>
                <?php if (tienePermiso('incidencias.ver')): ?><a href="index.php?url=incidencias" class="sidebar-link <?= $rutaActual === 'incidencias' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-exclamation-circle"></i> Incidencias</a><?php endif; ?>
                <?php if (tienePermiso('horarios.ver')): ?><a href="index.php?url=horarios" class="sidebar-link <?= $rutaActual === 'horarios' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-calendar-week"></i> Horarios</a><?php endif; ?>
                <?php if (tienePermiso('asistencia.configurar')): ?><a href="index.php?url=asistencia_configuracion" class="sidebar-link <?= $rutaActual === 'asistencia_configuracion' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-gear"></i> Configuración</a><?php endif; ?>
            <?php endif; ?>

            <?php if (tienePermiso('cobros.gestionar') || tienePermiso('planilla.calcular')): ?>
                <p class="sidebar-label mt-4">Finanzas</p>
                <?php if (tienePermiso('cobros.gestionar')): ?><a href="index.php?url=cobros" class="sidebar-link <?= $rutaActual === 'cobros' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-cash-coin"></i> Cobros</a><a href="index.php?url=cobros_pagos" class="sidebar-link <?= $rutaActual === 'cobros_pagos' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-receipt"></i> Recibos</a><a href="index.php?url=cobros_cierre_caja" class="sidebar-link <?= $rutaActual === 'cobros_cierre_caja' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-lock"></i> Cierre de caja</a><a href="index.php?url=cobros_tarifas" class="sidebar-link <?= $rutaActual === 'cobros_tarifas' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-list-ul"></i> Tarifas y conceptos</a><?php endif; ?>
                <?php if (tienePermiso('planilla.calcular')): ?><a href="index.php?url=planilla" class="sidebar-link <?= in_array($rutaActual, ['planilla','planilla_previa','planilla_revision','planilla_ajustes','planilla_pagos'], true) ? 'sidebar-link-active' : '' ?>"><i class="bi bi-wallet2"></i> Planilla</a><a href="index.php?url=planilla_historial" class="sidebar-link <?= $rutaActual === 'planilla_historial' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-clock-history"></i> Historial de planilla</a><a href="index.php?url=planilla_auditoria" class="sidebar-link <?= $rutaActual === 'planilla_auditoria' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-shield-check"></i> Auditoría de planilla</a><?php endif; ?>
            <?php endif; ?>

            <?php if (tienePermiso('reportes.ver')): ?>
                <p class="sidebar-label mt-4">Reportes</p>
                <a href="index.php?url=reportes_generales" class="sidebar-link <?= in_array($rutaActual, ['reportes_generales','reportes_generales_imprimir'], true) ? 'sidebar-link-active' : '' ?>"><i class="bi bi-clipboard-data"></i> Reportes generales</a>
                <?php if (tienePermiso('cobros.gestionar')): ?><a href="index.php?url=reportes_financieros" class="sidebar-link <?= $rutaActual === 'reportes_financieros' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-bar-chart-line"></i> Reportes financieros</a><?php endif; ?>
                <?php if (tienePermiso('planilla.calcular')): ?><a href="index.php?url=planilla_reporte" class="sidebar-link <?= $rutaActual === 'planilla_reporte' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-file-earmark-bar-graph"></i> Reportes de planilla</a><?php endif; ?>
            <?php endif; ?>

            <?php if (tienePermiso('configuracion.gestionar') || tienePermiso('asistencia.configurar') || tienePermiso('planilla.calcular')): ?>
                <p class="sidebar-label mt-4">Configuración</p>
                <a href="index.php?url=calendario_institucional" class="sidebar-link <?= $rutaActual === 'calendario_institucional' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-calendar-event"></i> Calendario institucional</a>
            <?php endif; ?>

            <?php if (tienePermiso('usuarios.gestionar')): ?><p class="sidebar-label mt-4">Seguridad</p><a href="index.php?url=usuarios" class="sidebar-link <?= $rutaActual === 'usuarios' ? 'sidebar-link-active' : '' ?>"><i class="bi bi-shield-lock"></i> Usuarios</a><?php endif; ?>
        </nav>

        <div class="shrink-0 border-t border-slate-100 p-3">
            <p class="mb-2 truncate px-3 text-xs text-slate-500"><?= htmlspecialchars($_SESSION['usuario']['nombre'] ?: $_SESSION['usuario']['rol']) ?></p>
            <a href="index.php?url=logout" class="sidebar-link text-red-600 hover:bg-red-50 hover:text-red-700"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
        </div>
    </aside>

    <main class="app-main w-full max-w-none px-4 py-6 lg:px-6" :class="{ 'app-main-collapsed': collapsed }">
