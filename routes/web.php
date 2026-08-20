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
    'docentes' => ['DocenteController', 'index'],
    'administrativos' => ['AdministrativoController', 'index'],
    'apoderados' => ['ApoderadoController', 'index'],
    'academico' => ['AcademicoController', 'index'],
    'academico_guardar' => ['AcademicoController', 'guardar'],
    'academico_actualizar' => ['AcademicoController', 'actualizar'],
    'academico_cambiar_estado' => ['AcademicoController', 'cambiarEstado'],
    'matriculas' => ['MatriculaController', 'index'],
    'matriculas_guardar' => ['MatriculaController', 'guardar'],
    'matriculas_actualizar' => ['MatriculaController', 'actualizar'],
    'matriculas_promover' => ['MatriculaController', 'promover'],
    'cobros' => ['CobroController', 'index'],
    'cobros_generar' => ['CobroController', 'generar'],
    'cobros_pagar' => ['CobroController', 'pagar'],
    'cobros_configurar' => ['CobroController', 'configurar'],
    'cobros_tarifas' => ['CobroController', 'tarifas'],
    'cobros_tarifa_estado' => ['CobroController', 'cambiarEstadoTarifa'],
    'cobros_concepto_estado' => ['CobroController', 'cambiarEstadoConcepto'],
    'cobros_pagos' => ['CobroController', 'pagos'],
    'cobros_pago_anular' => ['CobroController', 'anularPago'],
    'cobros_ajustar' => ['CobroController', 'ajustarCuenta'],
    'reportes_financieros' => ['CobroController', 'reportes'],
    'reportes_generales' => ['ReporteGeneralController', 'index'],
    'reportes_generales_imprimir' => ['ReporteGeneralController', 'imprimir'],
    'cobros_estado_cuenta' => ['CobroController', 'estadoCuenta'],
    'cobros_cierre_caja' => ['CobroController', 'cierreCaja'],
    'cobros_cierre_guardar' => ['CobroController', 'guardarCierreCaja'],
    'cobros_apertura_guardar' => ['CobroController', 'guardarAperturaCaja'],
    'planilla' => ['PlanillaController', 'index'],
    'planilla_previa' => ['PlanillaPreviewController', 'index'],
    'planilla_vinculo' => ['PlanillaController', 'guardarVinculo'],
    'planilla_periodo' => ['PlanillaController', 'guardarPeriodo'],
    'planilla_calcular' => ['PlanillaBorradorController', 'generar'],
    'planilla_revision' => ['PlanillaBorradorController', 'revision'],
    'planilla_ajuste_guardar' => ['PlanillaAjusteController', 'guardar'],
    'planilla_ajustes' => ['PlanillaAjusteController', 'index'],
    'planilla_ajuste_anular' => ['PlanillaAjusteController', 'anular'],
    'planilla_aprobar' => ['PlanillaEstadoController', 'aprobar'],
    'planilla_cerrar' => ['PlanillaEstadoController', 'cerrar'],
    'planilla_pagos' => ['PlanillaPagoController', 'index'],
    'planilla_pago_registrar' => ['PlanillaPagoController', 'registrar'],
    'planilla_pago_anular' => ['PlanillaPagoController', 'anular'],
    'planilla_pago_constancia' => ['PlanillaConsultaController', 'constancia'],
    'planilla_historial' => ['PlanillaConsultaController', 'historial'],
    'planilla_reporte' => ['PlanillaConsultaController', 'reporte'],
    'planilla_reporte_imprimir' => ['PlanillaConsultaController', 'reporteImprimir'],
    'planilla_auditoria' => ['PlanillaAuditoriaController', 'index'],
    'planilla_regla' => ['PlanillaController', 'guardarRegla'],
    'planilla_reglas' => ['PlanillaReglaController', 'index'],
    'planilla_regla_guardar' => ['PlanillaReglaController', 'guardar'],
    'planilla_regla_estado' => ['PlanillaReglaController', 'estado'],
    'calendario_institucional' => ['CalendarioInstitucionalController', 'index'],
    'calendario_guardar' => ['CalendarioInstitucionalController', 'guardar'],
    'calendario_actualizar' => ['CalendarioInstitucionalController', 'actualizar'],
    'calendario_estado' => ['CalendarioInstitucionalController', 'estado'],
    'vinculos_laborales' => ['VinculoLaboralController', 'index'],
    'vinculo_laboral_guardar' => ['VinculoLaboralController', 'guardar'],
    'vinculo_laboral_versionar' => ['VinculoLaboralController', 'versionar'],
    'fotochecks' => ['FotocheckController', 'index'],
    'fotocheck_generar' => ['FotocheckController', 'generar'],
    'fotocheck_ver' => ['FotocheckController', 'ver'],
    'fotocheck_qr' => ['FotocheckController', 'imagenQr'],
    'asistencia_escanear' => ['AsistenciaController', 'escanear'],
    'asistencia_registrar' => ['AsistenciaController', 'registrar'],
    'asistencia_hoy' => ['AsistenciaController', 'hoy'],
    'asistencia_manual' => ['AsistenciaController', 'manual'],
    'asistencia_manual_guardar' => ['AsistenciaController', 'guardarManual'],
    'asistencia_cerrar_dia' => ['AsistenciaController', 'cerrarDia'],
    'asistencia_personal' => ['AsistenciaController', 'personal'],
    'asistencia_cerrar_personal' => ['AsistenciaController', 'cerrarPersonal'],
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
