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
        <button @click="open=!open" class="rounded-xl p-2 text-slate-600 hover:bg-slate-100 lg:hidden"><i class="bi bi-list text-xl"></i></button>
        <nav class="hidden items-center gap-1 text-sm font-medium lg:flex">
            <a href="index.php?url=dashboard" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <?php if (tienePermiso('personas.ver')): ?><a href="index.php?url=personas" class="nav-link"><i class="bi bi-people"></i> Personas</a><?php endif; ?>
            <?php if (tienePermiso('alumnos.ver')): ?><a href="index.php?url=alumnos" class="nav-link"><i class="bi bi-backpack2"></i> Alumnos</a><?php endif; ?>
            <?php if (tienePermiso('apoderados.ver')): ?><a href="index.php?url=apoderados" class="nav-link"><i class="bi bi-person-hearts"></i> Apoderados</a><?php endif; ?>
            <?php if (tienePermiso('academico.ver')): ?><a href="index.php?url=academico" class="nav-link"><i class="bi bi-diagram-3"></i> Académico</a><?php endif; ?>
            <?php if (tienePermiso('matriculas.ver')): ?><a href="index.php?url=matriculas" class="nav-link"><i class="bi bi-journal-check"></i> Matrículas</a><?php endif; ?>
            <span class="ml-2 border-l pl-3 text-xs text-slate-500"><?= htmlspecialchars($_SESSION['usuario']['nombre'] ?: $_SESSION['usuario']['rol']) ?></span>
            <a href="index.php?url=logout" class="nav-link text-red-600"><i class="bi bi-box-arrow-right"></i></a>
        </nav>
    </div>
    <nav x-cloak x-show="open" @click.outside="open=false" class="border-t border-slate-200 bg-white p-3 lg:hidden">
        <a href="index.php?url=dashboard" class="mobile-link">Dashboard</a>
        <?php if (tienePermiso('personas.ver')): ?><a href="index.php?url=personas" class="mobile-link">Personas</a><?php endif; ?>
        <?php if (tienePermiso('alumnos.ver')): ?><a href="index.php?url=alumnos" class="mobile-link">Alumnos</a><?php endif; ?>
        <?php if (tienePermiso('apoderados.ver')): ?><a href="index.php?url=apoderados" class="mobile-link">Apoderados</a><?php endif; ?>
        <?php if (tienePermiso('academico.ver')): ?><a href="index.php?url=academico" class="mobile-link">Académico</a><?php endif; ?>
        <?php if (tienePermiso('matriculas.ver')): ?><a href="index.php?url=matriculas" class="mobile-link">Matrículas</a><?php endif; ?>
        <a href="index.php?url=logout" class="mobile-link text-red-600">Cerrar sesión</a>
    </nav>
</header>
<main class="mx-auto w-full max-w-7xl px-4 py-6 lg:px-6">