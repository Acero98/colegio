<?php $flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']); ?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingreso · <?= SISTEMA_NOMBRE ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/inter.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/output.css">
</head>

<body class="grid min-h-screen place-items-center bg-gradient-to-br from-blue-50 via-slate-50 to-cyan-50 p-4 font-[Inter]">
    <section class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
        <div class="mb-7 text-center"><span class="inline-grid h-14 w-14 place-items-center rounded-2xl bg-blue-600 text-2xl text-white"><i class="bi bi-mortarboard-fill"></i></span>
            <h1 class="mt-4 text-xl font-bold">Bienvenido</h1>
            <p class="mt-1 text-sm text-slate-500"><?= SISTEMA_NOMBRE ?></p>
        </div>
        <form method="post" action="index.php?url=auth" class="space-y-4">
            <?= campoCsrf() ?>
            <label class="block text-sm font-medium">Usuario<input required name="usuario" autocomplete="username" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 outline-none focus:border-blue-500"></label>
            <label class="block text-sm font-medium">Contraseña<input required type="password" name="contrasena" autocomplete="current-password" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 outline-none focus:border-blue-500"></label>
            <button class="w-full rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-700">Ingresar</button>
        </form>
    </section>
    <?php if ($flash): ?><p class="fixed bottom-5 rounded-xl bg-red-600 px-4 py-3 text-sm text-white"><?= htmlspecialchars($flash['texto']) ?></p><?php endif; ?>
</body>

</html>