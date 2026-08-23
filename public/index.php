<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Helpers/errores.php';
configurarManejoErrores();

ob_start();
session_name('colegio_session');
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

require_once __DIR__ . '/../app/Helpers/seguridad.php';
require_once __DIR__ . '/../routes/web.php';
