<div class="mb-7 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
    <div><p class="text-sm font-medium text-blue-600">Resumen institucional</p><h1 class="text-2xl font-bold tracking-tight">Dashboard</h1><p class="mt-1 text-sm text-slate-500">La asistencia y los cobros se incorporarán en las siguientes etapas.</p></div>
    <span class="rounded-xl bg-blue-50 px-3 py-2 text-sm text-blue-700"><i class="bi bi-calendar3"></i> <?= date('d/m/Y') ?></span>
</div>
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
<?php foreach (['alumnos' => ['Alumnos', 'bi-backpack2', 'blue'], 'docentes' => ['Docentes', 'bi-person-workspace', 'violet'], 'administrativos' => ['Administrativos', 'bi-building', 'amber'], 'apoderados' => ['Apoderados', 'bi-person-hearts', 'emerald']] as $clave => [$titulo, $icono, $color]): ?>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between"><p class="text-sm font-medium text-slate-500"><?= $titulo ?></p><span class="grid h-10 w-10 place-items-center rounded-xl bg-<?= $color ?>-50 text-<?= $color ?>-600"><i class="bi <?= $icono ?>"></i></span></div>
        <strong class="mt-4 block text-3xl"><?= number_format($kpis[$clave]) ?></strong><p class="mt-1 text-xs text-slate-400">Perfiles activos registrados</p>
    </article>
<?php endforeach; ?>
</section>
<section class="mt-6 grid gap-5 lg:grid-cols-2">
    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-semibold">Primera etapa activa</h2><p class="mt-2 text-sm leading-6 text-slate-500">Base del sistema, seguridad, personas, alumnos y apoderados. La siguiente entrega incorporará matrícula, fotochecks y códigos QR.</p></article>
    <article class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-cyan-50 p-6"><h2 class="font-semibold text-blue-900">Accesos rápidos</h2><div class="mt-4 flex flex-wrap gap-2"><a class="quick-link" href="index.php?url=personas">Registrar persona</a><a class="quick-link" href="index.php?url=alumnos">Ver alumnos</a><a class="quick-link" href="index.php?url=apoderados">Ver apoderados</a></div></article>
</section>
