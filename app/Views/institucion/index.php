<div class="mb-6">
    <p class="text-sm font-medium text-blue-600">Configuración general</p>
    <h1 class="text-2xl font-bold">Datos institucionales</h1>
    <p class="mt-1 text-sm text-slate-500">Esta información identifica al colegio en el sistema, fotochecks, recibos, constancias y reportes.</p>
</div>

<div class="mb-5 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
    <b>Fuente única institucional:</b> los cambios se mostrarán automáticamente en las nuevas pantallas e impresiones. Los documentos ya impresos conservarán naturalmente la información con la que fueron emitidos.
</div>

<form method="post" action="index.php?url=institucion_guardar" enctype="multipart/form-data" data-confirmar-accion="institucion-guardar" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
    <?= campoCsrf() ?>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-blue-100 text-xl text-blue-700"><i class="bi bi-building"></i></span>
            <div><h2 class="font-semibold">Identificación y contacto</h2><p class="text-sm text-slate-500">Datos legales y de comunicación del colegio.</p></div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="form-label md:col-span-2">Nombre institucional<input required maxlength="180" name="nombre" value="<?= htmlspecialchars($institucion['nombre'] ?? '') ?>" class="form-input" placeholder="Ej. Caleb School"></label>
            <label class="form-label">Nombre corto<input required maxlength="80" name="nombre_corto" value="<?= htmlspecialchars($institucion['nombre_corto'] ?? '') ?>" class="form-input" placeholder="Ej. Caleb School"></label>
            <label class="form-label">Razón social<input maxlength="180" name="razon_social" value="<?= htmlspecialchars($institucion['razon_social'] ?? '') ?>" class="form-input" placeholder="Nombre registrado ante SUNAT"></label>
            <label class="form-label">RUC<input inputmode="numeric" pattern="[0-9]{11}" maxlength="11" name="ruc" value="<?= htmlspecialchars($institucion['ruc'] ?? '') ?>" class="form-input" placeholder="11 dígitos"></label>
            <label class="form-label">Código modular<input maxlength="30" name="codigo_modular" value="<?= htmlspecialchars($institucion['codigo_modular'] ?? '') ?>" class="form-input" placeholder="Código del MINEDU"></label>
            <label class="form-label md:col-span-2">Dirección<input maxlength="250" name="direccion" value="<?= htmlspecialchars($institucion['direccion'] ?? '') ?>" class="form-input" placeholder="Dirección fiscal o institucional"></label>
            <label class="form-label">Teléfono<input maxlength="30" name="telefono" value="<?= htmlspecialchars($institucion['telefono'] ?? '') ?>" class="form-input" placeholder="Teléfono institucional"></label>
            <label class="form-label">Correo<input type="email" maxlength="150" name="correo" value="<?= htmlspecialchars($institucion['correo'] ?? '') ?>" class="form-input" placeholder="informes@colegio.edu.pe"></label>
            <label class="form-label">Sitio web<input type="url" maxlength="180" name="sitio_web" value="<?= htmlspecialchars($institucion['sitio_web'] ?? '') ?>" class="form-input" placeholder="https://www.colegio.edu.pe"></label>
            <label class="form-label">UGEL<input maxlength="120" name="ugel" value="<?= htmlspecialchars($institucion['ugel'] ?? '') ?>" class="form-input" placeholder="Ej. UGEL 03"></label>
        </div>

        <div class="mb-5 mt-7 flex items-center gap-3 border-b border-slate-100 pb-4">
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-violet-100 text-xl text-violet-700"><i class="bi bi-person-badge"></i></span>
            <div><h2 class="font-semibold">Presentación y responsable</h2><p class="text-sm text-slate-500">Información complementaria para documentos institucionales.</p></div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="form-label md:col-span-2">Lema institucional<input maxlength="180" name="lema" value="<?= htmlspecialchars($institucion['lema'] ?? '') ?>" class="form-input" placeholder="Ej. Educamos para transformar"></label>
            <label class="form-label">Director o representante<input maxlength="180" name="representante_nombre" value="<?= htmlspecialchars($institucion['representante_nombre'] ?? '') ?>" class="form-input" placeholder="Nombres y apellidos"></label>
            <label class="form-label">Cargo del representante<input maxlength="100" name="representante_cargo" value="<?= htmlspecialchars($institucion['representante_cargo'] ?? '') ?>" class="form-input" placeholder="Ej. Director general"></label>
        </div>
    </section>

    <aside class="space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Identidad visual</h2>
            <p class="mt-1 text-sm text-slate-500">Use una imagen cuadrada o apaisada con fondo transparente.</p>
            <div class="mt-5 grid min-h-44 place-items-center overflow-hidden rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50 p-5">
                <?php if (!empty($institucion['logo'])): ?>
                    <img id="vista-logo-institucion" src="index.php?url=institucion_logo&v=<?= urlencode((string) ($institucion['actualizado_en'] ?? '')) ?>" class="max-h-32 max-w-full object-contain" alt="Logotipo institucional">
                <?php else: ?>
                    <div id="logo-institucion-vacio" class="text-center text-blue-700"><i class="bi bi-mortarboard-fill text-5xl"></i><p class="mt-2 text-sm font-semibold">Sin logotipo registrado</p></div>
                    <img id="vista-logo-institucion" class="hidden max-h-32 max-w-full object-contain" alt="Vista previa del logotipo">
                <?php endif; ?>
            </div>
            <label class="form-label mt-4">Cambiar logotipo<input id="logo-institucion" type="file" name="logo" accept="image/jpeg,image/png,image/webp" class="form-input bg-white"><small class="mt-1 block font-normal text-slate-500">JPG, PNG o WEBP · máximo 2 MB.</small></label>
        </section>

        <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
            <h2 class="font-semibold"><i class="bi bi-shield-check mr-1"></i> Cambio auditado</h2>
            <p class="mt-2 leading-6">Se guardarán el usuario, fecha, valores anteriores y nuevos valores de cada actualización.</p>
        </section>

        <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"><i class="bi bi-floppy"></i> Guardar datos institucionales</button>
    </aside>
</form>

<script>
document.getElementById('logo-institucion')?.addEventListener('change', (evento) => {
    const archivo = evento.target.files?.[0];
    if (!archivo) return;
    const imagen = document.getElementById('vista-logo-institucion');
    imagen.src = URL.createObjectURL(archivo);
    imagen.classList.remove('hidden');
    document.getElementById('logo-institucion-vacio')?.classList.add('hidden');
});
</script>
