<?php

$url = $_GET['url'] ?? (autenticado() ? 'dashboard' : 'login');
$publicas = ['login', 'auth'];

if (!in_array($url, $publicas, true)) {
    requiereLogin();
}

$rutas = [
    'login' => ['AuthController', 'login'],
    'auth' => ['AuthController', 'auth'],
    'logout' => ['AuthController', 'logout'],
    'dashboard' => ['DashboardController', 'index'],
    'personas' => ['PersonaController', 'index'],
    'personas_guardar' => ['PersonaController', 'guardar'],
    'foto_persona' => ['PersonaController', 'mostrarFoto'],
    'alumnos' => ['AlumnoController', 'index'],
    'apoderados' => ['ApoderadoController', 'index'],
    'academico' => ['AcademicoController', 'index'],
    'academico_guardar' => ['AcademicoController', 'guardar'],
    'matriculas' => ['MatriculaController', 'index'],
    'matriculas_guardar' => ['MatriculaController', 'guardar'],
    'fotochecks' => ['FotocheckController', 'index'],
    'fotocheck_generar' => ['FotocheckController', 'generar'],
    'fotocheck_ver' => ['FotocheckController', 'ver'],
    'fotocheck_qr' => ['FotocheckController', 'imagenQr'],
];

if (!isset($rutas[$url])) {
    http_response_code(404);
    echo '404 - Página no encontrada';
    exit;
}

[$controlador, $metodo] = $rutas[$url];
require_once __DIR__ . '/../app/Controllers/' . $controlador . '.php';
(new $controlador())->$metodo();
