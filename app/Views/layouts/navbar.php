<header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur" x-data="{open:false}">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 lg:px-6">
        <a href="index.php?url=dashboard" class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 text-lg text-white shadow-sm">
                <i class="bi bi-mortarboard-fill"></i>
            </span>
            <span>
                <strong class="block text-sm leading-tight">Colegio</strong>
                <small class="text-xs text-slate-500">Asistencia y pagos</small>
            </span>
        </a>

        <button @click="open=!open" class="rounded-xl p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Abrir menú">
            <i class="bi bi-list text-xl"></i>
        </button>

        <nav class="hidden items-center gap-1 text-sm font-medium lg:flex">
            <a href="index.php?url=dashboard" class="nav-link"><i class="bi bi-speedometer2"></i> Inicio</a>
            <?php if (($_SESSION['usuario']['rol'] ?? '') === 'Apoderado' && tienePermiso('portal.ver')): ?><a href="index.php?url=portal_familia" class="nav-link"><i class="bi bi-house-heart"></i> Mi familia</a><?php endif; ?>

            <?php if (tienePermiso('personas.ver') || tienePermiso('alumnos.ver') || tienePermiso('apoderados.ver') || tienePermiso('fotochecks.ver')): ?>
                <div class="relative" x-data="{abierto:false}">
                    <button type="button" @click="abierto=!abierto" @keydown.escape.window="abierto=false" class="nav-link" :class="abierto && 'bg-blue-50 text-blue-700'">
                        <i class="bi bi-people"></i> Personas <i class="bi bi-chevron-down text-[10px]"></i>
                    </button>
                    <div x-cloak x-show="abierto" @click.outside="abierto=false" class="absolute left-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl">
                        <p class="px-4 pb-2 pt-1 text-[11px] font-bold uppercase tracking-wide text-slate-400">Comunidad educativa</p>
                        <?php if (tienePermiso('personas.ver')): ?><a href="index.php?url=personas" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-people mr-2"></i> Personas</a><?php endif; ?>
                        <?php if (tienePermiso('alumnos.ver')): ?><a href="index.php?url=alumnos" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-backpack2 mr-2"></i> Alumnos</a><?php endif; ?>
                        <?php if (tienePermiso('apoderados.ver')): ?><a href="index.php?url=apoderados" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-person-hearts mr-2"></i> Apoderados</a><?php endif; ?>
                        <?php if (tienePermiso('fotochecks.ver')): ?><a href="index.php?url=fotochecks" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-person-vcard mr-2"></i> Fotochecks</a><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (tienePermiso('academico.ver') || tienePermiso('matriculas.ver')): ?>
                <div class="relative" x-data="{abierto:false}">
                    <button type="button" @click="abierto=!abierto" @keydown.escape.window="abierto=false" class="nav-link" :class="abierto && 'bg-blue-50 text-blue-700'">
                        <i class="bi bi-mortarboard"></i> Académico <i class="bi bi-chevron-down text-[10px]"></i>
                    </button>
                    <div x-cloak x-show="abierto" @click.outside="abierto=false" class="absolute left-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl">
                        <p class="px-4 pb-2 pt-1 text-[11px] font-bold uppercase tracking-wide text-slate-400">Gestión escolar</p>
                        <?php if (tienePermiso('academico.ver')): ?><a href="index.php?url=academico" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-diagram-3 mr-2"></i> Estructura académica</a><?php endif; ?>
                        <?php if (tienePermiso('matriculas.ver')): ?><a href="index.php?url=matriculas" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-journal-check mr-2"></i> Matrículas</a><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (tienePermiso('asistencia.escanear') || tienePermiso('asistencia.ver') || tienePermiso('horarios.ver') || tienePermiso('incidencias.ver') || tienePermiso('asistencia.configurar')): ?>
                <div class="relative" x-data="{abierto:false}">
                    <button type="button" @click="abierto=!abierto" @keydown.escape.window="abierto=false" class="nav-link" :class="abierto && 'bg-blue-50 text-blue-700'">
                        <i class="bi bi-qr-code-scan"></i> Asistencia <i class="bi bi-chevron-down text-[10px]"></i>
                    </button>
                    <div x-cloak x-show="abierto" @click.outside="abierto=false" class="absolute left-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl">
                        <p class="px-4 pb-2 pt-1 text-[11px] font-bold uppercase tracking-wide text-slate-400">Control de asistencia</p>
                        <?php if (tienePermiso('asistencia.escanear')): ?><a href="index.php?url=asistencia_escanear" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-qr-code-scan mr-2"></i> Escanear QR</a><?php endif; ?>
                        <?php if (tienePermiso('asistencia.ver')): ?><a href="index.php?url=asistencia_hoy" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-calendar-day mr-2"></i> Asistencia de hoy</a><?php endif; ?>
                        <?php if (tienePermiso('asistencia.ver')): ?><a href="index.php?url=asistencia_historial" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-clock-history mr-2"></i> Historial</a><?php endif; ?>
                        <?php if (tienePermiso('incidencias.ver')): ?><a href="index.php?url=incidencias" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-file-earmark-medical mr-2"></i> Incidencias y permisos</a><?php endif; ?>
                        <?php if (tienePermiso('horarios.ver')): ?><a href="index.php?url=horarios" class="block px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-calendar-week mr-2"></i> Horarios y sesiones</a><?php endif; ?>
                        <?php if (tienePermiso('asistencia.configurar')): ?><a href="index.php?url=asistencia_configuracion" class="block border-t border-slate-100 px-4 py-2.5 text-slate-700 hover:bg-blue-50 hover:text-blue-700"><i class="bi bi-sliders mr-2"></i> Jornadas y dispositivos</a><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (tienePermiso('cobros.gestionar')): ?>
                <a href="index.php?url=cobros" class="nav-link"><i class="bi bi-cash-coin"></i> Cobros</a>
            <?php endif; ?>
            <?php if (tienePermiso('planilla.calcular')): ?><a href="index.php?url=planilla" class="nav-link"><i class="bi bi-wallet2"></i> Planilla</a><?php endif; ?>
            <?php if (tienePermiso('usuarios.gestionar')): ?><a href="index.php?url=usuarios" class="nav-link"><i class="bi bi-shield-lock"></i> Seguridad</a><?php endif; ?>

            <span class="ml-2 border-l pl-3 text-xs text-slate-500"><?= htmlspecialchars($_SESSION['usuario']['nombre'] ?: $_SESSION['usuario']['rol']) ?></span>
            <a href="index.php?url=logout" class="nav-link text-red-600" title="Cerrar sesión"><i class="bi bi-box-arrow-right"></i></a>
        </nav>
    </div>

    <nav x-cloak x-show="open" @click.outside="open=false" class="border-t border-slate-200 bg-white p-4 lg:hidden">
        <a href="index.php?url=dashboard" @click="open=false" class="mobile-link"><i class="bi bi-speedometer2 mr-2"></i> Inicio</a>
        <?php if (($_SESSION['usuario']['rol'] ?? '') === 'Apoderado' && tienePermiso('portal.ver')): ?><a href="index.php?url=portal_familia" @click="open=false" class="mobile-link"><i class="bi bi-house-heart mr-2"></i> Mi familia</a><?php endif; ?>

        <?php if (tienePermiso('personas.ver') || tienePermiso('alumnos.ver') || tienePermiso('apoderados.ver') || tienePermiso('fotochecks.ver')): ?>
            <p class="mb-1 mt-4 text-xs font-bold uppercase tracking-wide text-slate-400">Personas</p>
            <?php if (tienePermiso('personas.ver')): ?><a href="index.php?url=personas" @click="open=false" class="mobile-link">Personas</a><?php endif; ?>
            <?php if (tienePermiso('alumnos.ver')): ?><a href="index.php?url=alumnos" @click="open=false" class="mobile-link">Alumnos</a><?php endif; ?>
            <?php if (tienePermiso('apoderados.ver')): ?><a href="index.php?url=apoderados" @click="open=false" class="mobile-link">Apoderados</a><?php endif; ?>
            <?php if (tienePermiso('fotochecks.ver')): ?><a href="index.php?url=fotochecks" @click="open=false" class="mobile-link">Fotochecks</a><?php endif; ?>
        <?php endif; ?>

        <?php if (tienePermiso('academico.ver') || tienePermiso('matriculas.ver')): ?>
            <p class="mb-1 mt-4 text-xs font-bold uppercase tracking-wide text-slate-400">Académico</p>
            <?php if (tienePermiso('academico.ver')): ?><a href="index.php?url=academico" @click="open=false" class="mobile-link">Estructura académica</a><?php endif; ?>
            <?php if (tienePermiso('matriculas.ver')): ?><a href="index.php?url=matriculas" @click="open=false" class="mobile-link">Matrículas</a><?php endif; ?>
        <?php endif; ?>

        <?php if (tienePermiso('asistencia.escanear') || tienePermiso('asistencia.ver') || tienePermiso('horarios.ver') || tienePermiso('incidencias.ver') || tienePermiso('asistencia.configurar')): ?>
            <p class="mb-1 mt-4 text-xs font-bold uppercase tracking-wide text-slate-400">Asistencia</p>
            <?php if (tienePermiso('asistencia.escanear')): ?><a href="index.php?url=asistencia_escanear" @click="open=false" class="mobile-link">Escanear QR</a><?php endif; ?>
            <?php if (tienePermiso('asistencia.ver')): ?><a href="index.php?url=asistencia_hoy" @click="open=false" class="mobile-link">Asistencia de hoy</a><?php endif; ?>
            <?php if (tienePermiso('asistencia.ver')): ?><a href="index.php?url=asistencia_historial" @click="open=false" class="mobile-link">Historial</a><?php endif; ?>
            <?php if (tienePermiso('incidencias.ver')): ?><a href="index.php?url=incidencias" @click="open=false" class="mobile-link">Incidencias y permisos</a><?php endif; ?>
            <?php if (tienePermiso('horarios.ver')): ?><a href="index.php?url=horarios" @click="open=false" class="mobile-link">Horarios y sesiones</a><?php endif; ?>
            <?php if (tienePermiso('asistencia.configurar')): ?><a href="index.php?url=asistencia_configuracion" @click="open=false" class="mobile-link">Jornadas y dispositivos</a><?php endif; ?>
        <?php endif; ?>

        <?php if (tienePermiso('cobros.gestionar')): ?>
            <p class="mb-1 mt-4 text-xs font-bold uppercase tracking-wide text-slate-400">Finanzas</p>
            <a href="index.php?url=cobros" @click="open=false" class="mobile-link">Cobros y mensualidades</a>
        <?php endif; ?>
        <?php if (tienePermiso('planilla.calcular')): ?><a href="index.php?url=planilla" @click="open=false" class="mobile-link">Planilla</a><?php endif; ?>
        <?php if (tienePermiso('usuarios.gestionar')): ?><p class="mb-1 mt-4 text-xs font-bold uppercase tracking-wide text-slate-400">Seguridad</p><a href="index.php?url=usuarios" @click="open=false" class="mobile-link">Usuarios</a><?php endif; ?>

        <a href="index.php?url=logout" class="mobile-link mt-4 text-red-600"><i class="bi bi-box-arrow-right mr-2"></i> Cerrar sesión</a>
    </nav>
</header>
<main class="mx-auto w-full max-w-7xl px-4 py-6 lg:px-6">
