<?php

declare(strict_types=1);

function configurarManejoErrores(): void
{
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('expose_php', '0');
    ini_set('zend.exception_ignore_args', '1');
    if (function_exists('header_remove')) {
        header_remove('X-Powered-By');
    }
    set_exception_handler('manejarExcepcionAplicacion');
    register_shutdown_function('manejarErrorFatalAplicacion');
}

function generarReferenciaError(): string
{
    try {
        $sufijo = strtoupper(bin2hex(random_bytes(3)));
    } catch (Throwable $e) {
        $sufijo = strtoupper(substr(sha1(uniqid('', true)), 0, 6));
    }
    return 'ERR-' . date('Ymd-His') . '-' . $sufijo;
}

function limpiarMensajeError(string $mensaje): string
{
    $mensaje = preg_replace('/(password|passwd|pwd)\s*[=:]\s*[^\s;,]+/i', '$1=[OCULTO]', $mensaje) ?? $mensaje;
    $mensaje = preg_replace('/(mysql:[^\s]*)(password|passwd|pwd)=[^;\s]+/i', '$1$2=[OCULTO]', $mensaje) ?? $mensaje;
    return function_exists('mb_substr') ? mb_substr($mensaje, 0, 2000) : substr($mensaje, 0, 2000);
}

function registrarErrorSeguro(Throwable $error, string $referencia, int $estado): void
{
    $traza = [];
    foreach (array_slice($error->getTrace(), 0, 20) as $paso) {
        $traza[] = array_filter([
            'file' => $paso['file'] ?? null,
            'line' => $paso['line'] ?? null,
            'class' => $paso['class'] ?? null,
            'type' => $paso['type'] ?? null,
            'function' => $paso['function'] ?? null,
        ], static fn($valor) => $valor !== null);
    }

    $registro = [
        'fecha' => date('c'),
        'referencia' => $referencia,
        'http_status' => $estado,
        'tipo' => get_class($error),
        'codigo' => (string) $error->getCode(),
        'mensaje' => limpiarMensajeError($error->getMessage()),
        'archivo' => $error->getFile(),
        'linea' => $error->getLine(),
        'metodo_http' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
        'ruta' => $_GET['url'] ?? null,
        'usuario_id' => $_SESSION['usuario']['id_usuario'] ?? null,
        'rol' => $_SESSION['usuario']['rol'] ?? null,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'traza' => $traza,
    ];

    $directorio = dirname(__DIR__, 2) . '/storage/logs';
    if (!is_dir($directorio)) {
        @mkdir($directorio, 0775, true);
    }
    $linea = json_encode($registro, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    if (!is_dir($directorio) || @file_put_contents($directorio . '/app-errors.log', $linea, FILE_APPEND | LOCK_EX) === false) {
        error_log(sprintf('[%s] HTTP %d %s(%s): %s en %s:%d', $referencia, $estado, get_class($error), (string) $error->getCode(), limpiarMensajeError($error->getMessage()), $error->getFile(), $error->getLine()));
    }
}

function esServicioNoDisponible(Throwable $error): bool
{
    if (!$error instanceof PDOException) return false;
    $codigoControlador = isset($error->errorInfo[1]) ? (int) $error->errorInfo[1] : 0;
    if (in_array($codigoControlador, [2002, 2003, 2006, 2013], true)) return true;
    if (substr(strtoupper((string) $error->getCode()), 0, 2) === '08') return true;
    return (bool) preg_match('/\[2002\]|\[2003\]|connection refused|deneg[oó] expresamente|server has gone away|lost connection|no connection could be made/i', $error->getMessage());
}

function manejarExcepcionAplicacion(Throwable $error): void
{
    $estado = esServicioNoDisponible($error) ? 503 : 500;
    $referencia = generarReferenciaError();
    registrarErrorSeguro($error, $referencia, $estado);
    renderizarErrorHttp($estado, '', '', $referencia);
}

function manejarErrorFatalAplicacion(): void
{
    $ultimo = error_get_last();
    if (!$ultimo || !in_array($ultimo['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) return;
    $error = new ErrorException($ultimo['message'], 0, $ultimo['type'], $ultimo['file'], $ultimo['line']);
    $referencia = generarReferenciaError();
    registrarErrorSeguro($error, $referencia, 500);
    renderizarErrorHttp(500, '', '', $referencia);
}

function renderizarErrorHttp(int $estado, string $titulo = '', string $mensaje = '', ?string $referencia = null): void
{
    $contenidos = [
        403 => ['Acceso restringido', 'No tiene permisos para realizar esta operación.'],
        404 => ['Página no encontrada', 'La dirección solicitada no existe o fue movida.'],
        419 => ['La sesión del formulario venció', 'Actualice la página e intente nuevamente.'],
        500 => ['No pudimos completar la operación', 'Ocurrió un problema interno. Intente nuevamente en unos momentos.'],
        503 => ['Servicio temporalmente no disponible', 'No pudimos conectar con un servicio necesario. Verifique que el servidor esté iniciado e intente nuevamente.'],
    ];
    [$tituloPredeterminado, $mensajePredeterminado] = $contenidos[$estado] ?? $contenidos[500];
    $titulo = $titulo !== '' ? $titulo : $tituloPredeterminado;
    $mensaje = $mensaje !== '' ? $mensaje : $mensajePredeterminado;
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($estado);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    require dirname(__DIR__) . '/Views/errores/error.php';
    exit;
}
