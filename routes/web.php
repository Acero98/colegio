<?php

// URL canónica: el dashboard se accede únicamente mediante ?url=dashboard.
if (!isset($_GET['url'])) {
    header('Location: index.php?url=dashboard', true, 302);
    exit;
}

$url = $_GET['url'];
$publicas = ['login', 'auth'];

if (!in_array($url, $publicas, true)) {
    requiereLogin();
}

$rutas = [
    'login' => ['AuthController', 'login'],
    'auth' => ['AuthController', 'auth'],
    'logout' => ['AuthController', 'logout'],
    'dashboard' => ['DashboardController', 'index'],
    'portal_familia' => ['PortalController', 'index'],
    'usuarios' => ['UsuarioController', 'index'],
    'usuarios_guardar' => ['UsuarioController', 'guardar'],
    'usuarios_actualizar' => ['UsuarioController', 'actualizar'],
    'roles_permisos' => ['RolController', 'index'],
    'roles_guardar' => ['RolController', 'guardar'],
    'personas' => ['PersonaController', 'index'],
    'personas_guardar' => ['PersonaController', 'guardar'],
    'personas_actualizar' => ['PersonaController', 'actualizar'],
    'personas_cambiar_estado' => ['PersonaController', 'cambiarEstado'],
    'foto_persona' => ['PersonaController', 'mostrarFoto'],
    'alumnos' => ['AlumnoController', 'index'],
    'apoderados' => ['ApoderadoController', 'index'],
    'academico' => ['AcademicoController', 'index'],
    'academico_guardar' => ['AcademicoController', 'guardar'],
    'matriculas' => ['MatriculaController', 'index'],
    'matriculas_guardar' => ['MatriculaController', 'guardar'],
    'cobros' => ['CobroController', 'index'],
    'cobros_generar' => ['CobroController', 'generar'],
    'cobros_pagar' => ['CobroController', 'pagar'],
    'cobros_configurar' => ['CobroController', 'configurar'],
    'planilla' => ['PlanillaController', 'index'],
    'planilla_vinculo' => ['PlanillaController', 'guardarVinculo'],
    'planilla_periodo' => ['PlanillaController', 'guardarPeriodo'],
    'planilla_calcular' => ['PlanillaController', 'calcular'],
    'planilla_regla' => ['PlanillaController', 'guardarRegla'],
    'fotochecks' => ['FotocheckController', 'index'],
    'fotocheck_generar' => ['FotocheckController', 'generar'],
    'fotocheck_ver' => ['FotocheckController', 'ver'],
    'fotocheck_qr' => ['FotocheckController', 'imagenQr'],
    'asistencia_escanear' => ['AsistenciaController', 'escanear'],
    'asistencia_registrar' => ['AsistenciaController', 'registrar'],
    'asistencia_hoy' => ['AsistenciaController', 'hoy'],
    'asistencia_historial' => ['AsistenciaController', 'historial'],
    'incidencias' => ['AsistenciaController', 'incidencias'],
    'incidencia_guardar' => ['AsistenciaController', 'guardarIncidencia'],
    'asistencia_configuracion' => ['AsistenciaController', 'configuracion'],
    'asistencia_configurar' => ['AsistenciaController', 'guardarConfiguracion'],
    'horarios' => ['HorarioController', 'index'],
    'horarios_guardar' => ['HorarioController', 'guardar'],
];

if (!isset($rutas[$url])) {
    http_response_code(404);
    echo '404 - Página no encontrada';
    exit;
}

[$controlador, $metodo] = $rutas[$url];
require_once __DIR__ . '/../app/Controllers/' . $controlador . '.php';
(new $controlador())->$metodo();
