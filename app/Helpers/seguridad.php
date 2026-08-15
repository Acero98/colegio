<?php

function autenticado(): bool
{
    return isset($_SESSION['usuario']['id_usuario']);
}

function requiereLogin(): void
{
    if (!autenticado()) {
        header('Location: index.php?url=login');
        exit;
    }
}

function tienePermiso(string $permiso): bool
{
    return in_array($permiso, $_SESSION['usuario']['permisos'] ?? [], true);
}

function autorizar(string $permiso): void
{
    requiereLogin();
    if (!tienePermiso($permiso)) {
        http_response_code(403);
        require __DIR__ . '/../Views/errores/403.php';
        exit;
    }
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function validarCsrf(): void
{
    $recibido = $_POST['csrf_token'] ?? '';
    if (!is_string($recibido) || !hash_equals($_SESSION['csrf_token'] ?? '', $recibido)) {
        http_response_code(419);
        exit('Solicitud no válida. Actualice la página e intente nuevamente.');
    }
}

function mensaje(string $tipo, string $texto): void
{
    $_SESSION['flash'] = compact('tipo', 'texto');
}
