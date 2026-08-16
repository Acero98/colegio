<div class="mb-6 flex items-end justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Seguridad</p>
        <h1 class="text-2xl font-bold">Usuarios</h1>
        <p class="mt-1 text-sm text-slate-500">Cree accesos para el personal y los apoderados.</p>
    </div><button onclick="document.getElementById('modal-usuario').classList.remove('hidden')" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white"><i class="bi bi-person-plus"></i> Nuevo usuario</button>
</div>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-4">Usuario</th>
                    <th class="px-5 py-4">Persona</th>
                    <th class="px-5 py-4">Rol</th>
                    <th class="px-5 py-4">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y"><?php foreach ($usuarios as $u): ?><tr>
                        <td class="px-5 py-4 font-mono text-xs"><?= htmlspecialchars($u['usuario']) ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($u['persona'] ?: 'Cuenta institucional') ?></td>
                        <td class="px-5 py-4"><?= htmlspecialchars($u['roles']) ?></td>
                        <td class="px-5 py-4"><?= $u['estado'] ?></td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>
<div id="modal-usuario" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-slate-900/45 p-4">
    <div class="mx-auto mt-10 max-w-xl rounded-2xl bg-white p-6 shadow-2xl">
        <div class="flex justify-between">
            <div>
                <h2 class="text-lg font-bold">Nuevo usuario</h2>
                <p class="text-sm text-slate-500">Para un padre, seleccione la persona y el rol Apoderado.</p>
            </div><button onclick="this.closest('.fixed').classList.add('hidden')" class="p-2"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" action="index.php?url=usuarios_guardar" class="mt-5 grid gap-4 sm:grid-cols-2"><?= campoCsrf() ?><label class="form-label sm:col-span-2">Persona<select name="id_persona" class="form-input">
                    <option value="">Cuenta institucional</option><?php foreach ($opciones['personas'] as $p): ?><option value="<?= $p['id_persona'] ?>"><?= htmlspecialchars($p['apellidos'] . ', ' . $p['nombres'] . ' · ' . $p['numero_documento']) ?></option><?php endforeach; ?>
                </select></label><label class="form-label">Usuario<input required name="usuario" class="form-input"></label><label class="form-label">Rol<select required name="id_rol" class="form-input">
                    <option value="">Seleccione</option><?php foreach ($opciones['roles'] as $r): ?><option value="<?= $r['id_rol'] ?>"><?= htmlspecialchars($r['nombre']) ?></option><?php endforeach; ?>
                </select></label><label class="form-label sm:col-span-2">Contraseña inicial<input required minlength="8" type="password" name="contrasena" class="form-input"></label><button class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white sm:col-span-2">Crear usuario</button></form>
    </div>
</div>