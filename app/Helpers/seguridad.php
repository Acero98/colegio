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
        renderizarErrorHttp(403);
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
        renderizarErrorHttp(419);
    }
}

function mensaje(string $tipo, string $texto): void
{
    $_SESSION['flash'] = compact('tipo', 'texto');
}

function institucionActual(): array
{
    static $institucion = null;
    if (is_array($institucion)) return $institucion;
    global $conexion;
    if (!isset($conexion) || !($conexion instanceof PDO)) {
        return ['nombre' => 'Institución educativa', 'nombre_corto' => 'Colegio', 'lema' => '', 'logo' => null];
    }
    try {
        $institucion = $conexion->query('SELECT * FROM institucion ORDER BY id_institucion LIMIT 1')->fetch() ?: [];
    } catch (Throwable $e) {
        $institucion = [];
    }
    return $institucion + ['nombre' => 'Institución educativa', 'nombre_corto' => 'Colegio', 'lema' => '', 'logo' => null];
}
