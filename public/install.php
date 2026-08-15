<?php

declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';

if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('La instalación inicial solo puede ejecutarse localmente.');
}

$usuarios = (int) $conexion->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
if ($usuarios > 0) {
    exit('La instalación ya fue completada. Elimine o proteja public/install.php.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmacion = $_POST['confirmacion'] ?? '';
    if (!preg_match('/^[a-zA-Z0-9._-]{4,80}$/', $usuario) || strlen($contrasena) < 10 || $contrasena !== $confirmacion) {
        $error = 'Use un usuario válido y una contraseña coincidente de al menos 10 caracteres.';
    } else {
        $rol = (int) $conexion->query("SELECT id_rol FROM roles WHERE nombre = 'Super Administrador' LIMIT 1")->fetchColumn();
        $stmt = $conexion->prepare('INSERT INTO usuarios(usuario, contrasena) VALUES(:usuario, :contrasena)');
        $stmt->execute([':usuario' => $usuario, ':contrasena' => password_hash($contrasena, PASSWORD_DEFAULT)]);
        $idUsuario = (int) $conexion->lastInsertId();
        $conexion->prepare('INSERT INTO usuario_rol(id_usuario,id_rol) VALUES(:usuario,:rol)')
            ->execute([':usuario' => $idUsuario, ':rol' => $rol]);
        header('Location: index.php?url=login');
        exit;
    }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="assets/css/output.css"><title>Instalación inicial</title></head>
<body class="grid min-h-screen place-items-center bg-slate-50 p-4"><form method="post" class="w-full max-w-md rounded-2xl bg-white p-7 shadow"><h1 class="text-xl font-bold">Crear Super Administrador</h1><p class="mt-2 text-sm text-slate-500">Complete esta acción una sola vez y luego elimine o proteja este archivo.</p><?php if (!empty($error)): ?><p class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></p><?php endif; ?><label class="mt-5 block text-sm font-medium">Usuario<input required name="usuario" class="mt-1 w-full rounded-xl border p-2.5"></label><label class="mt-4 block text-sm font-medium">Contraseña<input required type="password" name="contrasena" minlength="10" class="mt-1 w-full rounded-xl border p-2.5"></label><label class="mt-4 block text-sm font-medium">Confirmar contraseña<input required type="password" name="confirmacion" minlength="10" class="mt-1 w-full rounded-xl border p-2.5"></label><button class="mt-6 w-full rounded-xl bg-blue-600 p-3 font-semibold text-white">Crear administrador</button></form></body></html>
