<?php

/**
 * Reinicio integral de datos demostrativos.
 *
 * Simular sin conservar cambios:
 *   php db/reiniciar_datos_demo.php --simular
 *
 * Ejecutar definitivamente:
 *   php db/reiniciar_datos_demo.php --confirmar
 */
if (PHP_SAPI !== 'cli') exit("Este proceso solo puede ejecutarse por consola.\n");
$simular = in_array('--simular', $argv, true);
$confirmar = in_array('--confirmar', $argv, true);
if (!$simular && !$confirmar) exit("Uso: php db/reiniciar_datos_demo.php --simular|--confirmar\n");

require_once __DIR__ . '/../config/conexion.php';

function ejecutarDemo(PDO $db, string $sql, array $parametros = []): PDOStatement
{
    $stmt = $db->prepare($sql);
    $stmt->execute($parametros);
    return $stmt;
}

function insertarDemo(PDO $db, string $sql, array $parametros = []): int
{
    ejecutarDemo($db, $sql, $parametros);
    return (int)$db->lastInsertId();
}

function personaDemoIntegral(PDO $db, array $dato): int
{
    return insertarDemo($db, "INSERT INTO personas(tipo_documento,numero_documento,nombres,apellidos,fecha_nacimiento,sexo,telefono,correo,direccion,fotografia,codigo_trabajador,estado,creado_en) VALUES(?,?,?,?,?,?,?,?,?,NULL,?,?,?)", [
        $dato['tipo_documento'] ?? 'DNI', $dato['documento'], $dato['nombres'], $dato['apellidos'],
        $dato['nacimiento'] ?? null, $dato['sexo'] ?? null, $dato['telefono'] ?? null,
        $dato['correo'] ?? null, $dato['direccion'] ?? 'Lima, Perú', $dato['codigo'] ?? null,
        $dato['estado'] ?? 'ACTIVO', $dato['creado_en'] ?? date('Y-m-d H:i:s'),
    ]);
}

function perfilDemoIntegral(PDO $db, int $persona, string $tipo, string $estado = 'ACTIVO'): void
{
    ejecutarDemo($db, 'INSERT INTO persona_perfiles(id_persona,tipo,estado,creado_en) VALUES(?,?,?,NOW())', [$persona, $tipo, $estado]);
}

function fechaDemo(string $base, string $modificador): string
{
    return date('Y-m-d', strtotime($base . ' ' . $modificador));
}

function fechaHoraDemo(string $fecha, string $hora): string
{
    return $fecha . ' ' . $hora;
}

function uuidDemo(int $numero): string
{
    return sprintf('10000000-0000-4000-8000-%012d', $numero);
}

$nombreBase = (string)$conexion->query('SELECT DATABASE()')->fetchColumn();
$respaldo = __DIR__ . '/colegioV01.sql';
if ($nombreBase !== 'colegio') exit("Seguridad: la conexión no apunta a la base colegio.\n");
if (!is_file($respaldo) || filesize($respaldo) < 50000) exit("Seguridad: no se encontró un respaldo válido en db/colegioV01.sql.\n");

$admin = $conexion->query("SELECT u.usuario,u.contrasena,u.estado FROM usuarios u INNER JOIN usuario_rol ur ON ur.id_usuario=u.id_usuario INNER JOIN roles r ON r.id_rol=ur.id_rol WHERE r.nombre='Super Administrador' ORDER BY u.id_usuario LIMIT 1")->fetch();
if (!$admin || empty($admin['contrasena'])) exit("Seguridad: no se encontró el Super Administrador que debe conservarse.\n");
$permisosOriginales = $conexion->query('SELECT clave,descripcion,estado FROM permisos ORDER BY clave')->fetchAll();
if (!$permisosOriginales) exit("Seguridad: no se encontraron permisos del sistema.\n");

$anio = (int)date('Y');
$anioAnterior = $anio - 1;
$anioSiguiente = $anio + 1;
$hoy = date('Y-m-d');
$ayer = fechaDemo($hoy, '-1 day');
$anteayer = fechaDemo($hoy, '-2 days');
$tresDias = fechaDemo($hoy, '-3 days');
$mesActual = date('Y-m-01');
$mesAnterior = date('Y-m-01', strtotime('-1 month'));
$mesSiguiente = date('Y-m-01', strtotime('+1 month'));
$finMesAnterior = date('Y-m-t', strtotime($mesAnterior));
$finMesActual = date('Y-m-t', strtotime($mesActual));
$finMesSiguiente = date('Y-m-t', strtotime($mesSiguiente));
$nombresMes = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

$tablas = $conexion->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);
if (count($tablas) < 43) exit("Seguridad: el esquema está incompleto; se esperaban al menos 43 tablas.\n");

$conexion->beginTransaction();
try {
    $conexion->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($tablas as $tabla) $conexion->exec('DELETE FROM `' . str_replace('`', '``', $tabla) . '`');
    $conexion->exec('SET FOREIGN_KEY_CHECKS=1');

    // Institución.
    ejecutarDemo($conexion, 'INSERT INTO institucion(id_institucion,nombre,nombre_corto,razon_social,ruc,direccion,telefono,correo,sitio_web,lema,codigo_modular,ugel,representante_nombre,representante_cargo,logo,creado_en) VALUES(1,?,?,?,?,?,?,?,?,?,?,?,?,?,NULL,NOW())', [
        'CALEB SCHOOL · DEMOSTRACIÓN', 'Caleb School', 'Caleb School S.A.C.', '20601234567', 'Av. Educación 125, Lima', '999 888 777', 'informes@calebschool.demo', 'https://calebschool.demo', 'Educación con propósito', '1234567', 'UGEL 03', 'María Elena Torres', 'Directora general',
    ]);

    // Seguridad: permisos existentes, roles profesionales y acceso administrador conservado.
    $idPermisos = [];
    foreach ($permisosOriginales as $permiso) {
        $idPermisos[$permiso['clave']] = insertarDemo($conexion, 'INSERT INTO permisos(clave,descripcion,estado) VALUES(?,?,?)', [$permiso['clave'], $permiso['descripcion'], $permiso['estado']]);
    }
    $nombresRoles = ['Super Administrador','Administrador','Director','Responsable de asistencia','Responsable de cobros','Responsable de planilla','Docente','Apoderado','Consulta'];
    $idRoles = [];
    foreach ($nombresRoles as $rol) $idRoles[$rol] = insertarDemo($conexion, "INSERT INTO roles(nombre,estado) VALUES(?,'ACTIVO')", [$rol]);

    $permisosPorRol = [
        'Super Administrador' => array_keys($idPermisos),
        'Administrador' => array_keys($idPermisos),
        'Director' => ['personas.ver','alumnos.ver','apoderados.ver','academico.ver','matriculas.ver','asistencia.ver','incidencias.ver','incidencias.gestionar','horarios.ver','fotochecks.ver','reportes.ver','auditoria.ver','caja.rectificar'],
        'Responsable de asistencia' => ['personas.ver','alumnos.ver','academico.ver','matriculas.ver','asistencia.escanear','asistencia.ver','asistencia.modificar','asistencia.configurar','incidencias.ver','incidencias.gestionar','horarios.ver','horarios.gestionar','fotochecks.ver','reportes.ver'],
        'Responsable de cobros' => ['personas.ver','alumnos.ver','apoderados.ver','matriculas.ver','cobros.gestionar','reportes.ver'],
        'Responsable de planilla' => ['personas.ver','asistencia.ver','incidencias.ver','horarios.ver','planilla.calcular','planilla.cerrar','reportes.ver','auditoria.ver'],
        'Docente' => ['alumnos.ver','academico.ver','matriculas.ver','asistencia.ver','incidencias.ver','horarios.ver','reportes.ver'],
        'Apoderado' => ['portal.ver'],
        'Consulta' => ['personas.ver','alumnos.ver','apoderados.ver','academico.ver','matriculas.ver','asistencia.ver','incidencias.ver','horarios.ver','fotochecks.ver','reportes.ver'],
    ];
    foreach ($permisosPorRol as $rol => $claves) {
        foreach ($claves as $clave) if (isset($idPermisos[$clave])) ejecutarDemo($conexion, 'INSERT INTO rol_permiso(id_rol,id_permiso) VALUES(?,?)', [$idRoles[$rol], $idPermisos[$clave]]);
    }
    $idAdmin = insertarDemo($conexion, 'INSERT INTO usuarios(id_persona,usuario,contrasena,estado,ultimo_acceso_en,creado_en) VALUES(NULL,?,?,?,NULL,NOW())', [$admin['usuario'], $admin['contrasena'], $admin['estado']]);
    ejecutarDemo($conexion, 'INSERT INTO usuario_rol(id_usuario,id_rol) VALUES(?,?)', [$idAdmin, $idRoles['Super Administrador']]);

    // Períodos académicos.
    $periodos = [];
    $periodos[$anioAnterior] = insertarDemo($conexion, "INSERT INTO periodos_academicos(nombre,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,'CERRADO')", ["Año escolar {$anioAnterior}", "{$anioAnterior}-03-03", "{$anioAnterior}-12-19"]);
    $periodos[$anio] = insertarDemo($conexion, "INSERT INTO periodos_academicos(nombre,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,'ACTIVO')", ["Año escolar {$anio}", "{$anio}-03-02", "{$anio}-12-18"]);
    $periodos[$anioSiguiente] = insertarDemo($conexion, "INSERT INTO periodos_academicos(nombre,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,'PLANIFICADO')", ["Año escolar {$anioSiguiente}", "{$anioSiguiente}-03-01", "{$anioSiguiente}-12-17"]);

    // Estructura académica completa.
    $idNiveles = [];
    foreach ([['Inicial',1],['Primaria',2],['Secundaria',3]] as $nivel) $idNiveles[$nivel[0]] = insertarDemo($conexion, "INSERT INTO niveles(nombre,orden,estado) VALUES(?,?,'ACTIVO')", $nivel);
    $gradosDefinidos = [
        'Inicial' => ['3 años','4 años','5 años'],
        'Primaria' => ['1.° grado','2.° grado','3.° grado','4.° grado','5.° grado','6.° grado'],
        'Secundaria' => ['1.° año','2.° año','3.° año','4.° año','5.° año'],
    ];
    $idGrados = [];
    foreach ($gradosDefinidos as $nivel => $gradosNivel) {
        foreach ($gradosNivel as $indice => $grado) $idGrados[$nivel . '|' . $grado] = insertarDemo($conexion, "INSERT INTO grados(id_nivel,nombre,orden,estado) VALUES(?,?,?,'ACTIVO')", [$idNiveles[$nivel], $grado, $indice + 1]);
    }
    $seccionesDefinidas = [
        ['Inicial','3 años','A',18],['Inicial','4 años','A',20],['Inicial','5 años','A',20],
        ['Primaria','1.° grado','A',3],['Primaria','1.° grado','B',5],['Primaria','2.° grado','A',5],
        ['Primaria','3.° grado','A',25],['Primaria','4.° grado','A',25],['Primaria','5.° grado','A',25],['Primaria','6.° grado','A',25],
        ['Secundaria','1.° año','A',30],['Secundaria','2.° año','A',30],['Secundaria','3.° año','A',30],['Secundaria','4.° año','A',30],['Secundaria','5.° año','A',2],
    ];
    $idSecciones = [];
    foreach ($seccionesDefinidas as $seccion) {
        $clave = $seccion[0] . '|' . $seccion[1] . '|' . $seccion[2];
        $idSecciones[$clave] = insertarDemo($conexion, "INSERT INTO secciones(id_grado,nombre,capacidad,estado) VALUES(?,?,?,'ACTIVO')", [$idGrados[$seccion[0].'|'.$seccion[1]], $seccion[2], $seccion[3]]);
    }

    // Personas de demostración.
    $personas = [];
    $personasDefinidas = [
        'ana' => ['documento'=>'75000001','nombres'=>'Ana','apellidos'=>'Torres Mendoza','nacimiento'=>"{$anioAnterior}-05-12",'sexo'=>'F','telefono'=>'990100001','correo'=>'ana.torres@demo.test'],
        'bruno' => ['documento'=>'75000002','nombres'=>'Bruno','apellidos'=>'Díaz Mendoza','nacimiento'=>"{$anioAnterior}-09-20",'sexo'=>'M','telefono'=>'990100002','correo'=>'bruno.diaz@demo.test'],
        'carla' => ['documento'=>'75000003','nombres'=>'Carla','apellidos'=>'Ruiz Salas','nacimiento'=>($anioAnterior-1).'-02-08','sexo'=>'F','telefono'=>'990100003','correo'=>'carla.ruiz@demo.test'],
        'diego' => ['documento'=>'75000004','nombres'=>'Diego','apellidos'=>'Pérez Núñez','nacimiento'=>($anioAnterior-2).'-11-16','sexo'=>'M','telefono'=>'990100004','correo'=>'diego.perez@demo.test'],
        'elena' => ['documento'=>'75000005','nombres'=>'Elena','apellidos'=>'Soto Campos','nacimiento'=>($anioAnterior-2).'-07-04','sexo'=>'F','telefono'=>'990100005','correo'=>'elena.soto@demo.test'],
        'felipe' => ['documento'=>'75000006','nombres'=>'Felipe','apellidos'=>'León Arias','nacimiento'=>($anioAnterior-1).'-01-23','sexo'=>'M','telefono'=>'990100006','correo'=>'felipe.leon@demo.test'],
        'gabriela' => ['documento'=>'75000007','nombres'=>'Gabriela','apellidos'=>'Ramos Ortiz','nacimiento'=>($anioAnterior-1).'-06-14','sexo'=>'F','telefono'=>'990100007','correo'=>'gabriela.ramos@demo.test'],
        'hugo' => ['documento'=>'75000008','nombres'=>'Hugo','apellidos'=>'Medina Vera','nacimiento'=>($anioAnterior-16).'-03-19','sexo'=>'M','telefono'=>'990100008','correo'=>'hugo.medina@demo.test'],
        'ines' => ['documento'=>'75000009','nombres'=>'Inés','apellidos'=>'Castro Luna','nacimiento'=>($anioAnterior-2).'-08-30','sexo'=>'F','telefono'=>'990100009','correo'=>'ines.castro@demo.test'],
        'jose' => ['documento'=>'75000010','nombres'=>'José','apellidos'=>'Vega Ríos','nacimiento'=>($anioAnterior-2).'-12-02','sexo'=>'M','telefono'=>'990100010','correo'=>'jose.vega@demo.test'],
        'lucia_alumna' => ['documento'=>'75000011','nombres'=>'Lucía','apellidos'=>'Morales Peña','nacimiento'=>($anioAnterior-1).'-10-11','sexo'=>'F','telefono'=>'990100011','correo'=>'lucia.morales@demo.test'],
        'raul' => ['documento'=>'75000012','nombres'=>'Raúl','apellidos'=>'Alumno Trabajador','nacimiento'=>($anioAnterior-22).'-04-10','sexo'=>'M','telefono'=>'990200012','correo'=>'raul.trabajador@demo.test','codigo'=>"CS-{$anio}-0010"],
        'paula' => ['documento'=>'45000001','nombres'=>'Paula','apellidos'=>'Mendoza Rojas','nacimiento'=>($anioAnterior-34).'-02-14','sexo'=>'F','telefono'=>'991100001','correo'=>'paula.mendoza@demo.test'],
        'roberto' => ['documento'=>'45000002','nombres'=>'Roberto','apellidos'=>'Ruiz Flores','nacimiento'=>($anioAnterior-38).'-07-22','sexo'=>'M','telefono'=>'991100002','correo'=>'roberto.ruiz@demo.test'],
        'sandra' => ['documento'=>'45000003','nombres'=>'Sandra','apellidos'=>'Núñez Silva','nacimiento'=>($anioAnterior-33).'-03-03','sexo'=>'F','telefono'=>'991100003','correo'=>'sandra.nunez@demo.test'],
        'miguel' => ['documento'=>'45000004','nombres'=>'Miguel','apellidos'=>'Soto Reyes','nacimiento'=>($anioAnterior-40).'-05-18','sexo'=>'M','telefono'=>'991100004','correo'=>'miguel.soto@demo.test'],
        'patricia_apoderada' => ['documento'=>'45000005','nombres'=>'Patricia','apellidos'=>'Castro Díaz','nacimiento'=>($anioAnterior-36).'-09-09','sexo'=>'F','telefono'=>'991100005','correo'=>'patricia.apoderada@demo.test'],
        'olga' => ['documento'=>'45000006','nombres'=>'Olga','apellidos'=>'Morales Torres','nacimiento'=>($anioAnterior-31).'-01-15','sexo'=>'F','telefono'=>'991100006','correo'=>'olga.morales@demo.test','estado'=>'INACTIVO'],
        'maria_docente' => ['documento'=>'35000001','nombres'=>'María','apellidos'=>'Paredes Soto','nacimiento'=>($anioAnterior-31).'-04-06','sexo'=>'F','telefono'=>'992100001','correo'=>'maria.paredes@demo.test','codigo'=>"CS-".($anioAnterior-3).'-0001'],
        'carlos_docente' => ['documento'=>'35000002','nombres'=>'Carlos','apellidos'=>'Rivera León','nacimiento'=>($anioAnterior-29).'-06-17','sexo'=>'M','telefono'=>'992100002','correo'=>'carlos.rivera@demo.test','codigo'=>"CS-".($anioAnterior-2).'-0002'],
        'lucia_docente' => ['documento'=>'35000003','nombres'=>'Lucía','apellidos'=>'Gómez Vidal','nacimiento'=>($anioAnterior-27).'-10-24','sexo'=>'F','telefono'=>'992100003','correo'=>'lucia.gomez@demo.test','codigo'=>"CS-".($anioAnterior-1).'-0003'],
        'mariana' => ['documento'=>'35000004','nombres'=>'Mariana','apellidos'=>'López Cruz','nacimiento'=>($anioAnterior-35).'-12-01','sexo'=>'F','telefono'=>'992100004','correo'=>'mariana.lopez@demo.test','codigo'=>"CS-".($anioAnterior-5).'-0004'],
        'jorge' => ['documento'=>'35000005','nombres'=>'Jorge','apellidos'=>'Vargas León','nacimiento'=>($anioAnterior-37).'-08-12','sexo'=>'M','telefono'=>'992100005','correo'=>'jorge.vargas@demo.test','codigo'=>"CS-".($anioAnterior-4).'-0005'],
        'rosa' => ['documento'=>'35000006','nombres'=>'Rosa','apellidos'=>'Salazar Vega','nacimiento'=>($anioAnterior-30).'-03-29','sexo'=>'F','telefono'=>'992100006','correo'=>'rosa.salazar@demo.test','codigo'=>"CS-".($anioAnterior-1).'-0006'],
        'patricia_planilla' => ['documento'=>'35000007','nombres'=>'Patricia','apellidos'=>'Quispe Mora','nacimiento'=>($anioAnterior-32).'-11-08','sexo'=>'F','telefono'=>'992100007','correo'=>'patricia.planilla@demo.test','codigo'=>"CS-".($anioAnterior-2).'-0007'],
        'ernesto' => ['documento'=>'35000008','nombres'=>'Ernesto','apellidos'=>'Cáceres Ruiz','nacimiento'=>($anioAnterior-39).'-05-25','sexo'=>'M','telefono'=>'992100008','correo'=>'ernesto.asistencia@demo.test','codigo'=>"CS-".($anioAnterior-6).'-0008'],
        'diana' => ['documento'=>'35000009','nombres'=>'Diana','apellidos'=>'Fernández Prado','nacimiento'=>($anioAnterior-42).'-02-20','sexo'=>'F','telefono'=>'992100009','correo'=>'diana.directora@demo.test','codigo'=>"CS-".($anioAnterior-10).'-0009'],
        'pedro' => ['documento'=>'35000010','nombres'=>'Pedro','apellidos'=>'Retirado Demo','nacimiento'=>($anioAnterior-45).'-07-07','sexo'=>'M','telefono'=>'992100010','correo'=>'pedro.retirado@demo.test','codigo'=>"CS-".($anioAnterior-8).'-0011','estado'=>'INACTIVO'],
    ];
    foreach ($personasDefinidas as $clave => $dato) $personas[$clave] = personaDemoIntegral($conexion, $dato);

    foreach (['ana','bruno','carla','diego','elena','felipe','gabriela','ines','jose','lucia_alumna'] as $clave) perfilDemoIntegral($conexion, $personas[$clave], 'ALUMNO');
    perfilDemoIntegral($conexion, $personas['hugo'], 'ALUMNO', 'INACTIVO');
    perfilDemoIntegral($conexion, $personas['raul'], 'ALUMNO', 'INACTIVO');
    perfilDemoIntegral($conexion, $personas['raul'], 'ADMINISTRATIVO');
    foreach (['paula','roberto','sandra','miguel','patricia_apoderada'] as $clave) perfilDemoIntegral($conexion, $personas[$clave], 'APODERADO');
    perfilDemoIntegral($conexion, $personas['olga'], 'APODERADO', 'INACTIVO');
    foreach (['maria_docente','carlos_docente','lucia_docente'] as $clave) perfilDemoIntegral($conexion, $personas[$clave], 'DOCENTE');
    perfilDemoIntegral($conexion, $personas['mariana'], 'DOCENTE');
    perfilDemoIntegral($conexion, $personas['mariana'], 'ADMINISTRATIVO');
    foreach (['jorge','rosa','patricia_planilla','ernesto','diana'] as $clave) perfilDemoIntegral($conexion, $personas[$clave], 'ADMINISTRATIVO');
    perfilDemoIntegral($conexion, $personas['pedro'], 'DOCENTE', 'INACTIVO');

    // Usuarios demostrativos. Todos usan Demo2026*; el administrador conserva su clave actual.
    $claveDemo = password_hash('Demo2026*', PASSWORD_DEFAULT);
    $usuarios = ['admin'=>$idAdmin];
    $usuariosDefinidos = [
        'director' => [$personas['diana'],'director.demo','Director'],
        'asistencia' => [$personas['ernesto'],'asistencia.demo','Responsable de asistencia'],
        'caja' => [$personas['rosa'],'caja.demo','Responsable de cobros'],
        'planilla' => [$personas['patricia_planilla'],'planilla.demo','Responsable de planilla'],
        'docente' => [$personas['maria_docente'],'docente.demo','Docente'],
        'apoderado' => [$personas['paula'],'apoderado.demo','Apoderado'],
    ];
    foreach ($usuariosDefinidos as $clave => $dato) {
        $usuarios[$clave] = insertarDemo($conexion, "INSERT INTO usuarios(id_persona,usuario,contrasena,estado,creado_en) VALUES(?,?,?,'ACTIVO',NOW())", [$dato[0],$dato[1],$claveDemo]);
        ejecutarDemo($conexion, 'INSERT INTO usuario_rol(id_usuario,id_rol) VALUES(?,?)', [$usuarios[$clave],$idRoles[$dato[2]]]);
    }

    // Familias: hermanos, responsable economico, contacto de emergencia y relacion inactiva.
    $relaciones = [
        ['ana','paula','MADRE',1,1,1,'ACTIVO'],
        ['bruno','paula','MADRE',1,1,1,'ACTIVO'],
        ['carla','roberto','PADRE',1,1,1,'ACTIVO'],
        ['diego','sandra','MADRE',1,1,1,'ACTIVO'],
        ['elena','miguel','PADRE',1,0,1,'ACTIVO'],
        ['ines','patricia_apoderada','TIA',1,1,1,'ACTIVO'],
        ['jose','patricia_apoderada','TIA',1,1,1,'ACTIVO'],
        ['lucia_alumna','olga','MADRE',1,1,1,'INACTIVO'],
    ];
    foreach ($relaciones as $r) {
        ejecutarDemo($conexion, 'INSERT INTO alumno_apoderado(id_alumno,id_apoderado,parentesco,es_principal,responsable_economico,contacto_emergencia,estado) VALUES(?,?,?,?,?,?,?)', [
            $personas[$r[0]],$personas[$r[1]],$r[2],$r[3],$r[4],$r[5],$r[6],
        ]);
    }

    // Matriculas historicas y actuales: promocion, repitencia, egreso, retiro, anulacion y aula llena.
    $matriculas = [];
    $crearMatricula = function(string $clave, string $persona, int $periodo, string $seccion, string $codigo, string $fecha, string $estado = 'ACTIVO') use (&$matriculas,$conexion,$personas,$periodos,$idSecciones): int {
        $id = insertarDemo($conexion, 'INSERT INTO matriculas(id_persona,id_periodo_academico,id_seccion,codigo_alumno,fecha_matricula,estado) VALUES(?,?,?,?,?,?)', [
            $personas[$persona],$periodos[$periodo],$idSecciones[$seccion],$codigo,$fecha,$estado,
        ]);
        $matriculas[$clave] = $id;
        return $id;
    };
    $crearMatricula('ana_actual','ana',$anio,'Primaria|1.° grado|A',"A-{$anio}-0001","{$anio}-02-10");
    $crearMatricula('bruno_actual','bruno',$anio,'Primaria|1.° grado|A',"A-{$anio}-0002","{$anio}-02-10");
    $crearMatricula('carla_actual','carla',$anio,'Primaria|1.° grado|A',"A-{$anio}-0003","{$anio}-02-11");
    $crearMatricula('diego_actual','diego',$anio,'Primaria|2.° grado|A',"A-{$anio}-0004","{$anio}-02-12");
    $crearMatricula('elena_actual','elena',$anio,'Primaria|2.° grado|A',"A-{$anio}-0005","{$anio}-02-12");
    $crearMatricula('ines_actual','ines',$anio,'Primaria|2.° grado|A',"A-{$anio}-0006","{$anio}-02-13");
    $crearMatricula('jose_actual','jose',$anio,'Primaria|2.° grado|A',"A-{$anio}-0007","{$anio}-02-13");
    $crearMatricula('lucia_actual','lucia_alumna',$anio,'Primaria|1.° grado|B',"A-{$anio}-0008","{$anio}-02-14");
    $crearMatricula('felipe_retirado','felipe',$anio,'Primaria|1.° grado|B',"A-{$anio}-0009","{$anio}-02-15",'RETIRADO');
    $crearMatricula('gabriela_anulada','gabriela',$anio,'Primaria|1.° grado|B',"A-{$anio}-0010","{$anio}-02-15",'ANULADO');
    $crearMatricula('jose_anterior','jose',$anioAnterior,'Primaria|1.° grado|A',"A-{$anioAnterior}-0001","{$anioAnterior}-02-12",'ACTIVO');
    $crearMatricula('ines_anterior','ines',$anioAnterior,'Primaria|2.° grado|A',"A-{$anioAnterior}-0002","{$anioAnterior}-02-12",'ACTIVO');
    $crearMatricula('carla_anterior','carla',$anioAnterior,'Primaria|1.° grado|B',"A-{$anioAnterior}-0003","{$anioAnterior}-02-13",'ACTIVO');
    $crearMatricula('hugo_egresado','hugo',$anioAnterior,'Secundaria|5.° año|A',"A-{$anioAnterior}-0004","{$anioAnterior}-02-13",'EGRESADO');

    // Areas, cargos y vinculos: mensual, por hora, por sesion, mixto y cambio de puesto.
    $areas = [];
    foreach (['Academica','Administracion','Direccion'] as $nombre) $areas[$nombre] = insertarDemo($conexion, "INSERT INTO areas(nombre,estado) VALUES(?,'ACTIVO')", [$nombre]);
    $cargos = [];
    $cargosDefinidos = [
        ['Docente de primaria','Academica'],['Docente de secundaria','Academica'],['Tallerista','Academica'],
        ['Director','Direccion'],['Secretaria','Administracion'],['Cajera','Administracion'],
        ['Analista de planilla','Administracion'],['Responsable de asistencia','Administracion'],['Auxiliar administrativo','Administracion'],
    ];
    foreach ($cargosDefinidos as $c) $cargos[$c[0]] = insertarDemo($conexion, "INSERT INTO cargos(id_area,nombre,estado) VALUES(?,?,'ACTIVO')", [$areas[$c[1]],$c[0]]);

    $vinculos = [];
    $crearVinculo = function(string $clave, string $persona, string $area, string $cargo, string $tipo, string $modalidad, float $mensual, float $hora, float $sesion, string $dias, string $inicio, ?string $fin = null, string $estado = 'ACTIVO', ?int $anterior = null, ?string $motivo = null) use (&$vinculos,$conexion,$personas,$areas,$cargos,$usuarios): int {
        $base = $mensual > 0 ? $mensual : ($hora > 0 ? $hora : $sesion);
        $id = insertarDemo($conexion, 'INSERT INTO vinculos_laborales(id_persona,id_vinculo_anterior,id_area,id_cargo,tipo,modalidad_pago,monto_base,monto_mensual,monto_hora,monto_sesion,dias_laborables,fecha_inicio,fecha_fin,estado,motivo_cambio,registrado_por,registrado_en) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())', [
            $personas[$persona],$anterior,$areas[$area],$cargos[$cargo],$tipo,$modalidad,$base,$mensual,$hora,$sesion,$dias,$inicio,$fin,$estado,$motivo,$usuarios['planilla'],
        ]);
        $vinculos[$clave] = $id;
        return $id;
    };
    $crearVinculo('maria','maria_docente','Academica','Docente de primaria','DOCENTE','MENSUAL',2400,0,0,'1,2,3,4,5',($anioAnterior-3).'-03-01');
    $crearVinculo('carlos','carlos_docente','Academica','Docente de secundaria','DOCENTE','HORA',0,45,0,'1,2,3,4,5',($anioAnterior-2).'-03-01');
    $crearVinculo('lucia','lucia_docente','Academica','Tallerista','DOCENTE','SESION',0,0,70,'2,4',($anioAnterior-1).'-03-01');
    $marianaAnterior = $crearVinculo('mariana_anterior','mariana','Administracion','Secretaria','ADMINISTRATIVO','MENSUAL',1750,0,0,'1,2,3,4,5',($anioAnterior-5).'-01-02',"{$anioAnterior}-12-31",'FINALIZADO',null,'Cambio de administrativo a docente');
    $crearVinculo('mariana','mariana','Academica','Docente de primaria','DOCENTE','MIXTO',1500,50,0,'1,2,3,4,5',"{$anio}-01-01",null,'ACTIVO',$marianaAnterior,'Cambio de puesto y modalidad mixta');
    $crearVinculo('jorge','jorge','Administracion','Auxiliar administrativo','ADMINISTRATIVO','MENSUAL',1900,0,0,'1,2,3,4,5',($anioAnterior-4).'-01-02');
    $crearVinculo('rosa','rosa','Administracion','Cajera','ADMINISTRATIVO','MENSUAL',1650,0,0,'1,2,3,4,5',($anioAnterior-1).'-01-02');
    $crearVinculo('patricia','patricia_planilla','Administracion','Analista de planilla','ADMINISTRATIVO','MENSUAL',2100,0,0,'1,2,3,4,5',($anioAnterior-2).'-01-02');
    $crearVinculo('ernesto','ernesto','Administracion','Responsable de asistencia','ADMINISTRATIVO','MENSUAL',1800,0,0,'1,2,3,4,5',($anioAnterior-6).'-01-02');
    $crearVinculo('diana','diana','Direccion','Director','ADMINISTRATIVO','MENSUAL',3500,0,0,'1,2,3,4,5',($anioAnterior-10).'-01-02');
    $crearVinculo('raul','raul','Administracion','Auxiliar administrativo','ADMINISTRATIVO','MENSUAL',1700,0,0,'1,2,3,4,5',"{$anio}-08-01");
    $crearVinculo('pedro','pedro','Academica','Docente de primaria','DOCENTE','MENSUAL',2000,0,0,'1,2,3,4,5',($anioAnterior-8).'-03-01',"{$anioAnterior}-12-20",'FINALIZADO',null,'Cese laboral');

    // Jornadas generales/individuales y sesiones docentes.
    $jornadas = [];
    $jornadas['alumnos'] = insertarDemo($conexion, "INSERT INTO jornadas(nombre,aplica_a,hora_entrada,hora_salida,tolerancia_minutos,requiere_salida,es_predeterminada,estado) VALUES('Jornada escolar general','ALUMNO','08:00','15:00',10,0,1,'ACTIVO')");
    $jornadas['docentes'] = insertarDemo($conexion, "INSERT INTO jornadas(nombre,aplica_a,hora_entrada,hora_salida,tolerancia_minutos,requiere_salida,estado) VALUES('Docentes tiempo completo','DOCENTE','07:30','15:30',5,1,'ACTIVO')");
    $jornadas['administrativos'] = insertarDemo($conexion, "INSERT INTO jornadas(nombre,aplica_a,hora_entrada,hora_salida,tolerancia_minutos,requiere_salida,estado) VALUES('Administrativos','ADMINISTRATIVO','08:00','17:00',10,1,'ACTIVO')");
    $jornadas['medio_tiempo'] = insertarDemo($conexion, "INSERT INTO jornadas(nombre,aplica_a,hora_entrada,hora_salida,tolerancia_minutos,requiere_salida,estado) VALUES('Medio tiempo','ADMINISTRATIVO','08:00','13:00',5,1,'ACTIVO')");
    $jornadas['antigua'] = insertarDemo($conexion, "INSERT INTO jornadas(nombre,aplica_a,hora_entrada,hora_salida,tolerancia_minutos,requiere_salida,estado) VALUES('Jornada anterior','ADMINISTRATIVO','09:00','18:00',5,1,'INACTIVO')");

    foreach (['maria_docente','mariana'] as $p) ejecutarDemo($conexion, "INSERT INTO asignaciones_horario(id_persona,id_jornada,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,NULL,'ACTIVO')", [$personas[$p],$jornadas['docentes'],"{$anio}-01-01"]);
    foreach (['jorge','rosa','patricia_planilla','ernesto','diana'] as $p) ejecutarDemo($conexion, "INSERT INTO asignaciones_horario(id_persona,id_jornada,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,NULL,'ACTIVO')", [$personas[$p],$jornadas['administrativos'],"{$anio}-01-01"]);
    ejecutarDemo($conexion, "INSERT INTO asignaciones_horario(id_persona,id_jornada,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,NULL,'ACTIVO')", [$personas['raul'],$jornadas['medio_tiempo'],"{$anio}-08-01"]);
    ejecutarDemo($conexion, "INSERT INTO asignaciones_horario(id_persona,id_jornada,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,?, 'INACTIVO')", [$personas['jorge'],$jornadas['antigua'],"{$anioAnterior}-01-01","{$anioAnterior}-12-31"]);

    $asignaturas = [];
    foreach (['Comunicacion','Matematica','Ciencia y Tecnologia','Ingles','Arte','Tutoria'] as $nombre) $asignaturas[$nombre] = insertarDemo($conexion, "INSERT INTO asignaturas(nombre,estado) VALUES(?,'ACTIVO')", [$nombre]);
    $cargas = [];
    $cargas['carlos_lunes'] = insertarDemo($conexion, "INSERT INTO carga_academica(id_vinculo,id_periodo_academico,id_seccion,id_asignatura,dia_semana,hora_inicio,hora_fin,tolerancia_minutos,estado) VALUES(?,?,?,?,1,'08:00','10:00',5,'ACTIVO')", [$vinculos['carlos'],$periodos[$anio],$idSecciones['Primaria|2.° grado|A'],$asignaturas['Matematica']]);
    $cargas['carlos_martes'] = insertarDemo($conexion, "INSERT INTO carga_academica(id_vinculo,id_periodo_academico,id_seccion,id_asignatura,dia_semana,hora_inicio,hora_fin,tolerancia_minutos,estado) VALUES(?,?,?,?,2,'10:30','12:30',5,'ACTIVO')", [$vinculos['carlos'],$periodos[$anio],$idSecciones['Primaria|1.° grado|A'],$asignaturas['Matematica']]);
    $cargas['lucia_martes'] = insertarDemo($conexion, "INSERT INTO carga_academica(id_vinculo,id_periodo_academico,id_seccion,id_asignatura,dia_semana,hora_inicio,hora_fin,tolerancia_minutos,estado) VALUES(?,?,?,?,2,'14:00','15:30',10,'ACTIVO')", [$vinculos['lucia'],$periodos[$anio],$idSecciones['Primaria|1.° grado|B'],$asignaturas['Arte']]);
    $cargas['mariana_miercoles'] = insertarDemo($conexion, "INSERT INTO carga_academica(id_vinculo,id_periodo_academico,id_seccion,id_asignatura,dia_semana,hora_inicio,hora_fin,tolerancia_minutos,estado) VALUES(?,?,?,?,3,'08:00','10:00',5,'ACTIVO')", [$vinculos['mariana'],$periodos[$anio],$idSecciones['Primaria|1.° grado|A'],$asignaturas['Comunicacion']]);
    $cargas['inactiva'] = insertarDemo($conexion, "INSERT INTO carga_academica(id_vinculo,id_periodo_academico,id_seccion,id_asignatura,dia_semana,hora_inicio,hora_fin,tolerancia_minutos,estado) VALUES(?,?,?,?,5,'12:00','13:00',5,'INACTIVO')", [$vinculos['maria'],$periodos[$anio],$idSecciones['Primaria|1.° grado|A'],$asignaturas['Tutoria']]);

    // Calendario y dispositivos.
    ejecutarDemo($conexion, "INSERT INTO calendario_institucional(fecha,tipo,descripcion,estado,creado_por,creado_en) VALUES(?,'FERIADO','Fiestas Patrias','ACTIVO',?,NOW())", ["{$anio}-07-28",$usuarios['planilla']]);
    ejecutarDemo($conexion, "INSERT INTO calendario_institucional(fecha,tipo,descripcion,estado,creado_por,creado_en) VALUES(?,'NO_LABORABLE','Jornada institucional','ACTIVO',?,NOW())", ["{$anio}-10-09",$usuarios['planilla']]);
    ejecutarDemo($conexion, "INSERT INTO calendario_institucional(fecha,tipo,descripcion,estado,creado_por,creado_en) VALUES(?,'SUSPENSION','Mantenimiento preventivo','ACTIVO',?,NOW())", ["{$anio}-11-06",$usuarios['planilla']]);
    ejecutarDemo($conexion, "INSERT INTO calendario_institucional(fecha,tipo,descripcion,estado,creado_por,creado_en) VALUES(?,'NO_LABORABLE','Registro cancelado de demostracion','INACTIVO',?,NOW())", ["{$anio}-09-18",$usuarios['planilla']]);

    $dispositivos = [];
    foreach ([
        ['entrada-principal','Entrada principal','Puerta principal','ACTIVO'],
        ['movil-coordinacion','Movil coordinacion','Patio','ACTIVO'],
        ['lector-retirado','Lector retirado','Puerta posterior','INACTIVO'],
    ] as $d) $dispositivos[$d[0]] = insertarDemo($conexion, 'INSERT INTO dispositivos_escaneo(codigo,nombre,token_hash,punto_control,estado,ultimo_sync_en) VALUES(?,?,?,?,?,NOW())', [$d[0],$d[1],hash('sha256','DEMO-'.$d[0]),$d[2],$d[3]]);

    // QR historicos; los QR activos y sus PNG se generan despues del commit.
    ejecutarDemo($conexion, "INSERT INTO codigos_qr(id_persona,token_hash,archivo_qr,version,estado,emitido_en,revocado_en) VALUES(?,?,?,1,'REVOCADO',?,?)", [$personas['ana'],hash('sha256','qr-revocado-ana'),'demo_qr_revocado.png',fechaHoraDemo($anteayer,'08:00:00'),fechaHoraDemo($ayer,'09:00:00')]);
    ejecutarDemo($conexion, "INSERT INTO codigos_qr(id_persona,token_hash,archivo_qr,version,estado,emitido_en,vence_en) VALUES(?,?,?,1,'VENCIDO',?,?)", [$personas['pedro'],hash('sha256','qr-vencido-pedro'),'demo_qr_vencido.png',"{$anioAnterior}-01-01 08:00:00","{$anioAnterior}-12-31 23:59:59"]);
    $personasQr = ['ana','bruno','carla','diego','elena','maria_docente','carlos_docente','mariana','jorge','rosa','patricia_planilla','ernesto','diana','raul'];

    // Asistencia: todos los estados importantes para alumnos y personal.
    $asistencias = [];
    $crearAsistencia = function(string $clave, string $persona, string $fecha, ?int $jornada, ?int $matricula, ?int $vinculo, ?int $carga, ?string $entrada, ?string $salida, int $tardanza, int $anticipada, int $trabajados, string $estado, ?string $observacion = null) use (&$asistencias,$conexion,$personas): int {
        $id = insertarDemo($conexion, 'INSERT INTO asistencias_diarias(id_persona,fecha,id_jornada,id_matricula,id_vinculo,id_carga,hora_entrada,hora_salida,minutos_tardanza,minutos_salida_anticipada,minutos_trabajados,estado,observacion,calculado_en) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())', [
            $personas[$persona],$fecha,$jornada,$matricula,$vinculo,$carga,$entrada,$salida,$tardanza,$anticipada,$trabajados,$estado,$observacion,
        ]);
        $asistencias[$clave] = $id;
        return $id;
    };
    $crearMarcacion = function(int $numero, string $persona, string $fechaHora, string $tipo, string $metodo = 'QR', string $validacion = 'VALIDA', ?string $observacion = null, ?int $usuario = null) use ($conexion,$personas,$dispositivos,$usuarios): int {
        return insertarDemo($conexion, 'INSERT INTO marcaciones_asistencia(uuid_cliente,id_persona,id_dispositivo,id_usuario,fecha_hora_dispositivo,fecha_hora_servidor,tipo,metodo,estado_validacion,observacion) VALUES(?,?,?,?,?,?,?,?,?,?)', [
            uuidDemo($numero),$personas[$persona],$metodo === 'MANUAL' ? null : $dispositivos['entrada-principal'],$usuario ?: ($metodo === 'MANUAL' ? $usuarios['asistencia'] : null),$fechaHora,$fechaHora,$tipo,$metodo,$validacion,$observacion,
        ]);
    };

    $crearAsistencia('ana_hoy','ana',$hoy,$jornadas['alumnos'],$matriculas['ana_actual'],null,null,'07:56:00',null,0,0,0,'PUNTUAL','Salida no obligatoria para alumnos');
    $crearMarcacion(1,'ana',fechaHoraDemo($hoy,'07:56:00'),'ENTRADA');
    $crearAsistencia('bruno_hoy','bruno',$hoy,$jornadas['alumnos'],$matriculas['bruno_actual'],null,null,'08:17:00',null,7,0,0,'TARDANZA','Supero la tolerancia en 7 minutos');
    $crearMarcacion(2,'bruno',fechaHoraDemo($hoy,'08:17:00'),'ENTRADA');
    $crearAsistencia('carla_hoy','carla',$hoy,null,$matriculas['carla_actual'],null,null,null,null,0,0,0,'FALTA','Generada en el cierre diario');
    $crearAsistencia('diego_hoy','diego',$hoy,null,$matriculas['diego_actual'],null,null,null,null,0,0,0,'JUSTIFICADO','Cita medica');
    $crearAsistencia('elena_hoy','elena',$hoy,null,$matriculas['elena_actual'],null,null,null,null,0,0,0,'PERMISO','Permiso familiar');
    $crearAsistencia('ines_hoy','ines',$hoy,$jornadas['alumnos'],$matriculas['ines_actual'],null,null,'07:58:00','12:10:00',0,170,252,'SALIDA_ANTICIPADA','Recojo autorizado');
    $crearMarcacion(3,'ines',fechaHoraDemo($hoy,'07:58:00'),'ENTRADA');
    $crearMarcacion(4,'ines',fechaHoraDemo($hoy,'12:10:00'),'SALIDA');
    $crearAsistencia('jose_hoy','jose',$hoy,null,$matriculas['jose_actual'],null,null,null,null,0,0,0,'ANULADO','Registro manual anulado');
    $crearAsistencia('lucia_hoy','lucia_alumna',$hoy,$jornadas['alumnos'],$matriculas['lucia_actual'],null,null,'08:03:00',null,0,0,0,'PUNTUAL','Registro manual');
    $crearMarcacion(5,'lucia_alumna',fechaHoraDemo($hoy,'08:03:00'),'ENTRADA','MANUAL','VALIDA','Registro manual: PUNTUAL',$usuarios['asistencia']);

    $crearAsistencia('ana_ayer','ana',$ayer,$jornadas['alumnos'],$matriculas['ana_actual'],null,null,'08:01:00',null,0,0,0,'PUNTUAL');
    $crearAsistencia('bruno_ayer','bruno',$ayer,null,$matriculas['bruno_actual'],null,null,null,null,0,0,0,'FALTA','Falta sin justificar');
    $crearAsistencia('carla_ayer','carla',$ayer,null,$matriculas['carla_actual'],null,null,null,null,0,0,0,'JUSTIFICADO','Descanso medico aprobado');

    // Personal por jornada.
    $crearAsistencia('maria_hoy','maria_docente',$hoy,$jornadas['docentes'],null,$vinculos['maria'],null,'07:28:00','15:31:00',0,0,483,'PUNTUAL');
    $crearMarcacion(10,'maria_docente',fechaHoraDemo($hoy,'07:28:00'),'ENTRADA');
    $crearMarcacion(11,'maria_docente',fechaHoraDemo($hoy,'15:31:00'),'SALIDA');
    $crearAsistencia('jorge_hoy','jorge',$hoy,$jornadas['administrativos'],null,$vinculos['jorge'],null,'08:18:00','17:02:00',8,0,524,'TARDANZA','18 minutos desde la entrada; 8 descontables tras tolerancia');
    $crearMarcacion(12,'jorge',fechaHoraDemo($hoy,'08:18:00'),'ENTRADA');
    $crearMarcacion(13,'jorge',fechaHoraDemo($hoy,'17:02:00'),'SALIDA');
    $crearAsistencia('rosa_hoy','rosa',$hoy,$jornadas['administrativos'],null,$vinculos['rosa'],null,'07:59:00',null,0,0,0,'INCOMPLETO','No registro salida');
    $crearMarcacion(14,'rosa',fechaHoraDemo($hoy,'07:59:00'),'ENTRADA');
    $crearAsistencia('patricia_hoy','patricia_planilla',$hoy,$jornadas['administrativos'],null,$vinculos['patricia'],null,null,null,0,0,0,'FALTA','Generada por cierre de personal');
    $crearAsistencia('ernesto_hoy','ernesto',$hoy,$jornadas['administrativos'],null,$vinculos['ernesto'],null,null,null,0,0,0,'LICENCIA','Licencia aprobada');
    $crearAsistencia('diana_hoy','diana',$hoy,$jornadas['administrativos'],null,$vinculos['diana'],null,null,null,0,0,0,'PERMISO','Comision de servicio');
    $crearAsistencia('raul_hoy','raul',$hoy,$jornadas['medio_tiempo'],null,$vinculos['raul'],null,'08:03:00','13:01:00',0,0,298,'PUNTUAL');
    $crearMarcacion(15,'raul',fechaHoraDemo($hoy,'08:03:00'),'ENTRADA','MANUAL','VALIDA','Ingreso registrado por responsable',$usuarios['asistencia']);
    $crearMarcacion(16,'raul',fechaHoraDemo($hoy,'13:01:00'),'SALIDA','MANUAL','VALIDA','Salida registrada por responsable',$usuarios['asistencia']);

    // Personal por horas/sesiones.
    $crearAsistencia('carlos_lunes','carlos_docente',$anteayer,null,null,$vinculos['carlos'],$cargas['carlos_lunes'],'07:59:00','10:01:00',0,0,122,'PUNTUAL');
    $crearMarcacion(20,'carlos_docente',fechaHoraDemo($anteayer,'07:59:00'),'ENTRADA');
    $crearMarcacion(21,'carlos_docente',fechaHoraDemo($anteayer,'10:01:00'),'SALIDA');
    $crearAsistencia('carlos_martes','carlos_docente',$ayer,null,null,$vinculos['carlos'],$cargas['carlos_martes'],'10:42:00','12:30:00',7,0,108,'TARDANZA');
    $crearMarcacion(22,'carlos_docente',fechaHoraDemo($ayer,'10:42:00'),'ENTRADA');
    $crearMarcacion(23,'carlos_docente',fechaHoraDemo($ayer,'12:30:00'),'SALIDA');
    $crearAsistencia('lucia_sesion','lucia_docente',$ayer,null,null,$vinculos['lucia'],$cargas['lucia_martes'],'14:02:00','15:00:00',0,30,58,'SALIDA_ANTICIPADA','Sesion finalizada antes de hora');
    $crearMarcacion(24,'lucia_docente',fechaHoraDemo($ayer,'14:02:00'),'ENTRADA');
    $crearMarcacion(25,'lucia_docente',fechaHoraDemo($ayer,'15:00:00'),'SALIDA');
    $crearAsistencia('mariana_sesion','mariana',$hoy,null,null,$vinculos['mariana'],$cargas['mariana_miercoles'],'07:58:00','10:03:00',0,0,125,'PUNTUAL');
    $crearMarcacion(26,'mariana',fechaHoraDemo($hoy,'07:58:00'),'ENTRADA');
    $crearMarcacion(27,'mariana',fechaHoraDemo($hoy,'10:03:00'),'SALIDA');

    // Marcaciones duplicadas/anuladas para demostrar validacion y auditoria.
    $crearMarcacion(30,'ana',fechaHoraDemo($hoy,'07:56:03'),'ENTRADA','QR','DUPLICADA','Lectura repetida en tres segundos');
    $crearMarcacion(31,'jose',fechaHoraDemo($hoy,'08:05:00'),'ENTRADA','MANUAL','ANULADA','Marcacion corregida',$usuarios['asistencia']);

    // Incidencias pendientes, aprobadas y rechazadas.
    ejecutarDemo($conexion, "INSERT INTO incidencias_asistencia(id_asistencia,tipo,motivo,estado,registrado_por,creado_en) VALUES(?,'JUSTIFICACION_FALTA','Presentara certificado medico','PENDIENTE',?,NOW())", [$asistencias['carla_hoy'],$usuarios['docente']]);
    ejecutarDemo($conexion, "INSERT INTO incidencias_asistencia(id_asistencia,tipo,motivo,estado,registrado_por,resuelto_por,resolucion_observacion,creado_en,resuelto_en) VALUES(?,'JUSTIFICACION_TARDANZA','Transporte escolar demorado','APROBADA',?,?,?, ?,?)", [$asistencias['bruno_hoy'],$usuarios['docente'],$usuarios['asistencia'],'Sustento verificado por el responsable de asistencia.',fechaHoraDemo($hoy,'09:00:00'),fechaHoraDemo($hoy,'09:20:00')]);
    ejecutarDemo($conexion, "INSERT INTO incidencias_asistencia(id_asistencia,tipo,motivo,estado,registrado_por,resuelto_por,resolucion_observacion,creado_en,resuelto_en) VALUES(?,'LICENCIA','Licencia personal documentada','APROBADA',?,?,?, ?,?)", [$asistencias['ernesto_hoy'],$usuarios['asistencia'],$usuarios['director'],'Licencia autorizada por Dirección.',fechaHoraDemo($ayer,'16:00:00'),fechaHoraDemo($hoy,'07:30:00')]);
    ejecutarDemo($conexion, "INSERT INTO incidencias_asistencia(id_asistencia,tipo,motivo,estado,registrado_por,resuelto_por,resolucion_observacion,creado_en,resuelto_en) VALUES(?,'OBSERVACION','Solicitud sin sustento','RECHAZADA',?,?,?, ?,?)", [$asistencias['patricia_hoy'],$usuarios['planilla'],$usuarios['asistencia'],'Solicitud rechazada por falta de sustento.',fechaHoraDemo($hoy,'10:00:00'),fechaHoraDemo($hoy,'11:00:00')]);

    // Cierres manual y automatico, conservando responsable, proceso y resultado.
    ejecutarDemo($conexion, "INSERT INTO cierres_asistencia(fecha,alcance,tipo,ejecutado_por,proceso,faltas_generadas,incompletas_generadas,detalle,ejecutado_en) VALUES(?,'ALUMNOS','MANUAL',?,'usuario',1,0,?,?)", [
        $ayer,$usuarios['asistencia'],json_encode(['faltas_generadas'=>1,'observacion'=>'Cierre manual de demostración'], JSON_UNESCAPED_UNICODE),fechaHoraDemo($ayer,'23:00:00'),
    ]);
    ejecutarDemo($conexion, "INSERT INTO cierres_asistencia(fecha,alcance,tipo,ejecutado_por,proceso,faltas_generadas,incompletas_generadas,detalle,ejecutado_en) VALUES(?,'PERSONAL','AUTOMATICO',NULL,'tarea_programada',0,1,?,?)", [
        $anteayer,json_encode(['faltas_jornada'=>0,'faltas_sesion'=>0,'incompletas'=>1], JSON_UNESCAPED_UNICODE),fechaHoraDemo($anteayer,'23:00:00'),
    ]);

    // Finanzas: conceptos unicos/mensuales, pagos parciales, tarifas generales y por grado.
    $conceptosCobro = [];
    foreach ([
        ['Matricula','MATRICULA',0,'ACTIVO'],['Pension mensual','MENSUALIDAD',1,'ACTIVO'],
        ['Taller extracurricular','TALLER',1,'ACTIVO'],['Movilidad escolar','OTRO',1,'ACTIVO'],
        ['Material educativo','OTRO',0,'ACTIVO'],['Fotocheck','OTRO',0,'ACTIVO'],
        ['Cuota extraordinaria','CUOTA',1,'ACTIVO'],['Concepto antiguo','OTRO',1,'INACTIVO'],
    ] as $c) $conceptosCobro[$c[0]] = insertarDemo($conexion, 'INSERT INTO conceptos_cobro(nombre,tipo,admite_parcial,estado) VALUES(?,?,?,?)', $c);

    $crearTarifa = function(string $concepto, int $periodo, ?int $grado, float $importe, string $inicio, ?string $fin = null, string $estado = 'ACTIVO') use ($conexion,$conceptosCobro,$periodos): int {
        return insertarDemo($conexion, 'INSERT INTO tarifas_cobro(id_concepto_cobro,id_periodo_academico,id_grado,importe,vigencia_inicio,vigencia_fin,estado) VALUES(?,?,?,?,?,?,?)', [$conceptosCobro[$concepto],$periodos[$periodo],$grado,$importe,$inicio,$fin,$estado]);
    };
    $crearTarifa('Matricula',$anio,null,500,"{$anio}-01-01","{$anio}-04-30");
    $crearTarifa('Pension mensual',$anio,null,420,"{$anio}-03-01","{$anio}-12-31");
    $crearTarifa('Pension mensual',$anio,$idGrados['Primaria|1.° grado'],450,"{$anio}-03-01","{$anio}-12-31");
    $crearTarifa('Taller extracurricular',$anio,null,160,"{$anio}-03-01","{$anio}-12-31");
    $crearTarifa('Movilidad escolar',$anio,null,120,"{$anio}-03-01","{$anio}-12-31");
    $crearTarifa('Material educativo',$anio,null,200,"{$anio}-01-01","{$anio}-12-31");
    $crearTarifa('Fotocheck',$anio,null,35,"{$anio}-01-01","{$anio}-12-31");
    $crearTarifa('Cuota extraordinaria',$anio,null,150,"{$anio}-06-01","{$anio}-12-31");
    $crearTarifa('Matricula',$anioAnterior,null,450,"{$anioAnterior}-01-01","{$anioAnterior}-04-30");
    $crearTarifa('Pension mensual',$anioAnterior,null,380,"{$anioAnterior}-03-01","{$anioAnterior}-12-31");
    $crearTarifa('Pension mensual',$anio,null,400,"{$anio}-01-01","{$anio}-02-28",'INACTIVO');

    $cuentas = [];
    $crearCuenta = function(string $clave, string $matricula, string $concepto, string $periodoCobro, ?string $vence, float $original, float $descuento, float $recargo, float $saldo, string $estado) use (&$cuentas,$conexion,$matriculas,$conceptosCobro): int {
        $id = insertarDemo($conexion, 'INSERT INTO cuentas_cobrar(id_matricula,id_concepto_cobro,periodo_cobro,fecha_vencimiento,importe_original,descuento,recargo,saldo,estado) VALUES(?,?,?,?,?,?,?,?,?)', [
            $matriculas[$matricula],$conceptosCobro[$concepto],$periodoCobro,$vence,$original,$descuento,$recargo,$saldo,$estado,
        ]);
        $cuentas[$clave] = $id;
        return $id;
    };
    $crearCuenta('ana_matricula','ana_actual','Matricula',"{$anio}-03-01","{$anio}-03-10",500,0,0,0,'PAGADA');
    $crearCuenta('ana_mensual_parcial','ana_actual','Pension mensual',$mesActual,fechaDemo($hoy,'+5 days'),450,20,0,330,'PARCIAL');
    $crearCuenta('ines_mensual_pagada','ines_actual','Pension mensual',$mesActual,fechaDemo($hoy,'+5 days'),420,0,0,0,'PAGADA');
    $crearCuenta('elena_taller','elena_actual','Taller extracurricular',$mesActual,fechaDemo($hoy,'+7 days'),160,0,0,80,'PARCIAL');
    $crearCuenta('bruno_mensual','bruno_actual','Pension mensual',$mesActual,fechaDemo($hoy,'+7 days'),450,0,0,450,'PENDIENTE');
    $crearCuenta('bruno_material','bruno_actual','Material educativo',"{$anio}-03-01",fechaDemo($hoy,'+2 days'),180,0,0,180,'PENDIENTE');
    $crearCuenta('diego_beca','diego_actual','Pension mensual',$mesActual,fechaDemo($hoy,'+4 days'),420,100,0,320,'PENDIENTE');
    $crearCuenta('diego_mora_15','diego_actual','Cuota extraordinaria',date('Y-m-01',strtotime('-1 month')),fechaDemo($hoy,'-15 days'),150,0,15,165,'VENCIDA');
    $crearCuenta('ines_mora_45','ines_actual','Pension mensual',date('Y-m-01',strtotime('-2 months')),fechaDemo($hoy,'-45 days'),420,0,0,420,'VENCIDA');
    $crearCuenta('jose_mora_90','jose_anterior','Pension mensual',"{$anioAnterior}-11-01",fechaDemo($hoy,'-90 days'),380,0,0,380,'VENCIDA');
    $crearCuenta('carla_deuda_anterior','carla_anterior','Pension mensual',"{$anioAnterior}-12-01","{$anioAnterior}-12-10",350,0,30,280,'VENCIDA');
    $crearCuenta('jose_proxima','jose_actual','Pension mensual',$mesSiguiente,date('Y-m-10',strtotime($mesSiguiente)),420,0,0,420,'PENDIENTE');
    $crearCuenta('lucia_anulada','lucia_actual','Movilidad escolar',$mesActual,fechaDemo($hoy,'+5 days'),120,0,0,120,'ANULADA');
    $crearCuenta('felipe_retirado','felipe_retirado','Pension mensual',date('Y-m-01',strtotime('-3 months')),fechaDemo($hoy,'-70 days'),450,0,0,450,'VENCIDA');
    $crearCuenta('jose_exonerada','jose_actual','Material educativo',"{$anio}-03-01","{$anio}-03-15",200,200,0,0,'PAGADA');

    ejecutarDemo($conexion, "INSERT INTO ajustes_cobro(id_cuenta,tipo,importe,motivo,registrado_por,creado_en) VALUES(?,'DESCUENTO',20,'Descuento promocional',?,NOW())", [$cuentas['ana_mensual_parcial'],$usuarios['caja']]);
    ejecutarDemo($conexion, "INSERT INTO ajustes_cobro(id_cuenta,tipo,importe,motivo,registrado_por,creado_en) VALUES(?,'BECA',100,'Beca de rendimiento',?,NOW())", [$cuentas['diego_beca'],$usuarios['caja']]);
    ejecutarDemo($conexion, "INSERT INTO ajustes_cobro(id_cuenta,tipo,importe,motivo,registrado_por,creado_en) VALUES(?,'RECARGO',15,'Recargo por mora',?,NOW())", [$cuentas['diego_mora_15'],$usuarios['caja']]);
    ejecutarDemo($conexion, "INSERT INTO ajustes_cobro(id_cuenta,tipo,importe,motivo,registrado_por,creado_en) VALUES(?,'RECARGO',30,'Recargo historico',?,NOW())", [$cuentas['carla_deuda_anterior'],$usuarios['caja']]);
    ejecutarDemo($conexion, "INSERT INTO ajustes_cobro(id_cuenta,tipo,importe,motivo,registrado_por,creado_en) VALUES(?,'EXONERACION',200,'Exoneracion total aprobada',?,NOW())", [$cuentas['jose_exonerada'],$usuarios['director']]);

    // Caja con dos turnos cerrados el mismo dia y un nuevo turno abierto.
    $aperturas = [];
    $aperturas['turno_manana'] = insertarDemo($conexion, "INSERT INTO aperturas_caja(fecha,monto_apertura,observacion,abierto_por,abierto_en,estado) VALUES(?,100,'Turno de manana',?,?,'CERRADA')", [$ayer,$usuarios['caja'],fechaHoraDemo($ayer,'08:00:00')]);
    $aperturas['turno_tarde'] = insertarDemo($conexion, "INSERT INTO aperturas_caja(fecha,monto_apertura,observacion,abierto_por,abierto_en,estado) VALUES(?,150,'Turno de tarde',?,?,'CERRADA')", [$ayer,$usuarios['caja'],fechaHoraDemo($ayer,'13:30:00')]);
    $aperturas['turno_actual'] = insertarDemo($conexion, "INSERT INTO aperturas_caja(fecha,monto_apertura,observacion,abierto_por,abierto_en,estado) VALUES(?,500,'Caja abierta para continuar las pruebas',?,?,'ABIERTA')", [$hoy,$usuarios['caja'],fechaHoraDemo($hoy,'08:00:00')]);

    // Movimientos adicionales: capital y préstamos no se confunden con ingresos operativos.
    $categoriasFinanzas = [];
    foreach ([
        ['capital','Aporte o inyección de capital','INGRESO','CAPITAL'],
        ['prestamo','Préstamo recibido','INGRESO','FINANCIAMIENTO'],
        ['otros_ingresos','Otros ingresos','INGRESO','OPERATIVO'],
        ['compras','Compras','EGRESO','OPERATIVO'],
        ['alquiler','Alquiler','EGRESO','OPERATIVO'],
        ['servicios','Servicios básicos','EGRESO','OPERATIVO'],
        ['pago_prestamo','Pago de préstamo','EGRESO','FINANCIAMIENTO'],
    ] as $categoria) {
        $categoriasFinanzas[$categoria[0]] = insertarDemo($conexion,"INSERT INTO categorias_movimiento_financiero(nombre,tipo,clasificacion,estado) VALUES(?,?,?,'ACTIVO')",array_slice($categoria,1));
    }
    foreach ([
        ["MF-{$anio}-000001",'INGRESO','capital',$mesActual.' 09:00:00',5000,'TRANSFERENCIA','Socios del colegio','APORTE-'.$anio,'Capital de trabajo para el período',null],
        ["MF-{$anio}-000002",'INGRESO','prestamo',$mesActual.' 10:00:00',3000,'DEPOSITO','Banco de demostración','PREST-'.$anio,'Préstamo recibido para equipamiento',null],
        ["MF-{$anio}-000003",'EGRESO','alquiler',fechaHoraDemo($hoy,'09:20:00'),1200,'TRANSFERENCIA','Arrendador de demostración','OP-ALQ-001','Pago del alquiler institucional',$aperturas['turno_actual']],
        ["MF-{$anio}-000004",'EGRESO','compras',fechaHoraDemo($hoy,'10:30:00'),250,'EFECTIVO','Librería escolar','B001-125','Compra de útiles administrativos',$aperturas['turno_actual']],
        ["MF-{$anio}-000005",'EGRESO','servicios',fechaHoraDemo($hoy,'11:00:00'),380,'YAPE','Empresa de servicios','OP-SERV-001','Pago de servicios básicos',$aperturas['turno_actual']],
    ] as $movimiento) {
        ejecutarDemo($conexion,"INSERT INTO movimientos_financieros(codigo,tipo,id_categoria,fecha_movimiento,monto,medio,tercero,referencia,descripcion,id_apertura,registrado_por,estado) VALUES(?,?,?,?,?,?,?,?,?,?,?,'REGISTRADO')",[$movimiento[0],$movimiento[1],$categoriasFinanzas[$movimiento[2]],$movimiento[3],$movimiento[4],$movimiento[5],$movimiento[6],$movimiento[7],$movimiento[8],$movimiento[9],$usuarios['caja']]);
    }

    $pagosAlumno = [];
    $crearPagoAlumno = function(string $clave, string $recibo, ?string $apoderado, int $apertura, string $fechaHora, string $medio, float $total, string $estado, array $detalles, ?string $motivoAnulacion = null) use (&$pagosAlumno,$conexion,$personas,$usuarios): int {
        $id = insertarDemo($conexion, 'INSERT INTO pagos_alumno(numero_recibo,id_apoderado,fecha_pago,medio_pago,importe_total,estado,registrado_por,observacion,motivo_anulacion,anulado_por,anulado_en,id_apertura) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)', [
            $recibo,$apoderado ? $personas[$apoderado] : null,$fechaHora,$medio,$total,$estado,$usuarios['caja'],'Pago de demostracion',$motivoAnulacion,$estado === 'ANULADO' ? $usuarios['director'] : null,$estado === 'ANULADO' ? $fechaHora : null,$apertura,
        ]);
        foreach ($detalles as $cuenta => $importe) ejecutarDemo($conexion, 'INSERT INTO pagos_alumno_detalles(id_pago,id_cuenta,importe) VALUES(?,?,?)', [$id,$cuenta,$importe]);
        ejecutarDemo($conexion, 'INSERT INTO pagos_alumno_medios(id_pago,medio,importe_aplicado,importe_recibido,vuelto,referencia) VALUES(?,?,?,?,?,?)', [$id,$medio,$total,$total,0,$medio === 'EFECTIVO' ? null : 'OP-DEMO-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT)]);
        $pagosAlumno[$clave] = $id;
        return $id;
    };
    $crearPagoAlumno('recibo_multiple',"R-{$anio}-000001",'paula',$aperturas['turno_manana'],fechaHoraDemo($ayer,'09:15:00'),'TRANSFERENCIA',920,'REGISTRADO',[$cuentas['ana_matricula']=>500,$cuentas['ines_mensual_pagada']=>420]);
    $crearPagoAlumno('parcial_ana',"R-{$anio}-000002",'paula',$aperturas['turno_tarde'],fechaHoraDemo($ayer,'14:20:00'),'EFECTIVO',100,'REGISTRADO',[$cuentas['ana_mensual_parcial']=>100]);
    $crearPagoAlumno('parcial_elena',"R-{$anio}-000003",'miguel',$aperturas['turno_tarde'],fechaHoraDemo($ayer,'15:10:00'),'YAPE',80,'REGISTRADO',[$cuentas['elena_taller']=>80]);
    $crearPagoAlumno('deuda_anterior',"R-{$anio}-000004",'roberto',$aperturas['turno_actual'],fechaHoraDemo($hoy,'10:05:00'),'TRANSFERENCIA',100,'REGISTRADO',[$cuentas['carla_deuda_anterior']=>100]);
    $crearPagoAlumno('anulado',"R-{$anio}-000005",'paula',$aperturas['turno_actual'],fechaHoraDemo($hoy,'11:15:00'),'PLIN',180,'ANULADO',[$cuentas['bruno_material']=>180],'Pago registrado en una cuenta equivocada');

    $cierreManana = insertarDemo($conexion, 'INSERT INTO cierres_caja(fecha,efectivo_sistema,efectivo_declarado,otros_sistema,otros_declarado,diferencia,observacion,cerrado_por,cerrado_en,id_apertura) VALUES(?,100,100,920,920,0,?,?,?,?)', [$ayer,'Turno conciliado',$usuarios['caja'],fechaHoraDemo($ayer,'12:30:00'),$aperturas['turno_manana']]);
    $cierreTarde = insertarDemo($conexion, 'INSERT INTO cierres_caja(fecha,efectivo_sistema,efectivo_declarado,otros_sistema,otros_declarado,diferencia,observacion,cerrado_por,cerrado_en,id_apertura) VALUES(?,250,245,80,80,-5,?,?,?,?)', [$ayer,'Diferencia de caja demostrativa',$usuarios['caja'],fechaHoraDemo($ayer,'17:45:00'),$aperturas['turno_tarde']]);
    ejecutarDemo($conexion,"UPDATE cierres_caja SET estado_revision='PENDIENTE' WHERE id_cierre=?",[$cierreTarde]);
    foreach ([
        [$cierreManana,'EFECTIVO',100,100],[$cierreManana,'TRANSFERENCIA',920,920],
        [$cierreTarde,'EFECTIVO',250,245],[$cierreTarde,'YAPE',80,80],
    ] as $detalleCierre) ejecutarDemo($conexion, 'INSERT INTO cierres_caja_medios(id_cierre,medio,importe_sistema,importe_declarado,diferencia) VALUES(?,?,?,?,?)', [$detalleCierre[0],$detalleCierre[1],$detalleCierre[2],$detalleCierre[3],$detalleCierre[3]-$detalleCierre[2]]);

    // Una rectificacion historica ya resuelta y otra diferencia pendiente para practicar ambos flujos.
    $rectificacionCaja = insertarDemo($conexion, "INSERT INTO rectificaciones_caja(id_cierre,accion,motivo_tipo,motivo,diferencia_anterior,diferencia_nueva,registrado_por,registrado_en) VALUES(?,'RECTIFICACION','ERROR_CONTEO','Se volvió a contar el efectivo y se corrigió el importe declarado.',-5,0,?,?)", [
        $cierreManana,$usuarios['director'],fechaHoraDemo($ayer,'12:45:00'),
    ]);
    ejecutarDemo($conexion, "INSERT INTO rectificaciones_caja_medios(id_rectificacion,medio,importe_sistema,importe_anterior,importe_nuevo,diferencia_anterior,diferencia_nueva) VALUES(?, 'EFECTIVO',100,95,100,-5,0)", [$rectificacionCaja]);
    ejecutarDemo($conexion, "INSERT INTO rectificaciones_caja_medios(id_rectificacion,medio,importe_sistema,importe_anterior,importe_nuevo,diferencia_anterior,diferencia_nueva) VALUES(?, 'TRANSFERENCIA',920,920,920,0,0)", [$rectificacionCaja]);
    ejecutarDemo($conexion, "UPDATE cierres_caja SET estado_revision='RECTIFICADO' WHERE id_cierre=?", [$cierreManana]);

    // Planilla: reglas, modalidades, feriado, tardanzas, pagos totales/parciales y anulaciones.
    $reglas = [];
    foreach ([
        ['Minuto de tardanza','DESCUENTO','TARDANZA',0.50,"{$anio}-01-01",null,'ACTIVO'],
        ['Falta injustificada','DESCUENTO','FALTA',80.00,"{$anio}-01-01",null,'ACTIVO'],
        ['Salida anticipada','DESCUENTO','SALIDA_ANTICIPADA',0.40,"{$anio}-01-01",null,'ACTIVO'],
        ['Bono institucional','BONIFICACION','FIJO',100.00,"{$anio}-01-01",null,'ACTIVO'],
        ['Regla anterior','DESCUENTO','TARDANZA',1.00,"{$anioAnterior}-01-01","{$anioAnterior}-12-31",'INACTIVO'],
    ] as $r) $reglas[$r[0]] = insertarDemo($conexion, 'INSERT INTO reglas_descuento(nombre,naturaleza,evento,formula,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,?,?,?,?)', [$r[0],$r[1],$r[2],json_encode(['monto_unitario'=>$r[3]]),$r[4],$r[5],$r[6]]);

    $conceptosPago = [];
    foreach ([
        ['Remuneracion mensual','INGRESO','BASE','ACTIVO'],['Pago por horas','INGRESO','HORA','ACTIVO'],
        ['Pago por sesiones','INGRESO','HORA','ACTIVO'],['Bono institucional','INGRESO','BONIFICACION','ACTIVO'],
        ['Descuento por tardanza','DESCUENTO','TARDANZA','ACTIVO'],['Descuento por falta','DESCUENTO','FALTA','ACTIVO'],
        ['Descuento por salida anticipada','DESCUENTO','MANUAL','ACTIVO'],['Ajuste manual ingreso','INGRESO','MANUAL','ACTIVO'],
        ['Ajuste manual descuento','DESCUENTO','MANUAL','ACTIVO'],['Concepto de planilla antiguo','INGRESO','MANUAL','INACTIVO'],
    ] as $c) $conceptosPago[$c[0]] = insertarDemo($conexion, 'INSERT INTO conceptos_pago(nombre,naturaleza,tipo,estado) VALUES(?,?,?,?)', $c);

    $periodosPago = [];
    $nombreMesAnterior = $nombresMes[(int)date('n',strtotime($mesAnterior))] . ' ' . date('Y',strtotime($mesAnterior));
    $nombreMesActual = $nombresMes[(int)date('n',strtotime($mesActual))] . ' ' . date('Y',strtotime($mesActual));
    $nombreMesSiguiente = $nombresMes[(int)date('n',strtotime($mesSiguiente))] . ' ' . date('Y',strtotime($mesSiguiente));
    $periodosPago['anterior'] = insertarDemo($conexion, "INSERT INTO periodos_pago(nombre,fecha_inicio,fecha_fin,estado,revisado_por,revisado_en,cerrado_por,cerrado_en) VALUES(?,?,?,'PAGADO',?,?,?,?)", [$nombreMesAnterior,$mesAnterior,$finMesAnterior,$usuarios['planilla'],fechaHoraDemo(fechaDemo($finMesAnterior,'+1 day'),'09:00:00'),$usuarios['planilla'],fechaHoraDemo(fechaDemo($finMesAnterior,'+1 day'),'11:00:00')]);
    $periodosPago['actual'] = insertarDemo($conexion, "INSERT INTO periodos_pago(nombre,fecha_inicio,fecha_fin,estado,revisado_por,revisado_en,cerrado_por,cerrado_en) VALUES(?,?,?,'CERRADO',?,?,?,?)", [$nombreMesActual,$mesActual,$finMesActual,$usuarios['planilla'],fechaHoraDemo($ayer,'16:00:00'),$usuarios['planilla'],fechaHoraDemo($hoy,'09:00:00')]);
    $periodosPago['siguiente'] = insertarDemo($conexion, "INSERT INTO periodos_pago(nombre,fecha_inicio,fecha_fin,estado) VALUES(?,?,?,'ABIERTO')", [$nombreMesSiguiente,$mesSiguiente,$finMesSiguiente]);

    $detallesPlanilla = [];
    $crearDetallePlanilla = function(string $clave, string $periodo, string $vinculo, float $base, float $bonos, float $descuentos, float $neto, float $pagado, string $estado) use (&$detallesPlanilla,$conexion,$periodosPago,$vinculos): int {
        $id = insertarDemo($conexion, 'INSERT INTO planilla_detalles(id_periodo_pago,id_vinculo,monto_base,bonificaciones,descuentos,monto_pagar,monto_pagado,saldo_pendiente,estado,calculado_en) VALUES(?,?,?,?,?,?,?,?,?,NOW())', [
            $periodosPago[$periodo],$vinculos[$vinculo],$base,$bonos,$descuentos,$neto,$pagado,round($neto-$pagado,2),$estado,
        ]);
        $detallesPlanilla[$clave] = $id;
        return $id;
    };
    $conceptosDetalle = [];
    $agregarConceptoPlanilla = function(string $clave, string $detalle, string $concepto, float $cantidad, float $importe, ?string $regla, array $explicacion) use (&$conceptosDetalle,$conexion,&$detallesPlanilla,$conceptosPago,$reglas): int {
        $id = insertarDemo($conexion, 'INSERT INTO planilla_detalle_conceptos(id_planilla_detalle,id_concepto_pago,id_regla,cantidad,importe,explicacion) VALUES(?,?,?,?,?,?)', [
            $detallesPlanilla[$detalle],$conceptosPago[$concepto],$regla ? $reglas[$regla] : null,$cantidad,$importe,json_encode($explicacion),
        ]);
        $conceptosDetalle[$clave] = $id;
        return $id;
    };

    $crearDetallePlanilla('ant_maria','anterior','maria',2400,100,50,2450,2450,'PAGADO');
    $crearDetallePlanilla('ant_carlos','anterior','carlos',1800,0,25,1775,1775,'PAGADO');
    $crearDetallePlanilla('ant_lucia','anterior','lucia',840,0,0,840,840,'PAGADO');
    $crearDetallePlanilla('ant_mariana','anterior','mariana',2000,100,40,2060,2060,'PAGADO');
    $agregarConceptoPlanilla('ant_maria_base','ant_maria','Remuneracion mensual',22,2400,null,['dias'=>22,'feriados'=>1]);
    $agregarConceptoPlanilla('ant_maria_bono','ant_maria','Bono institucional',1,100,'Bono institucional',['automatico'=>true]);
    $agregarConceptoPlanilla('ant_maria_tarde','ant_maria','Descuento por tardanza',100,-50,'Minuto de tardanza',['minutos'=>100,'tolerancia_aplicada'=>true]);
    $agregarConceptoPlanilla('ant_carlos_horas','ant_carlos','Pago por horas',40,1800,null,['horas'=>40]);
    $agregarConceptoPlanilla('ant_carlos_tarde','ant_carlos','Descuento por tardanza',50,-25,'Minuto de tardanza',['minutos'=>50]);
    $agregarConceptoPlanilla('ant_lucia_sesiones','ant_lucia','Pago por sesiones',12,840,null,['sesiones'=>12,'esperadas'=>12]);
    $agregarConceptoPlanilla('ant_mariana_base','ant_mariana','Remuneracion mensual',22,1500,null,['dias'=>22]);
    $agregarConceptoPlanilla('ant_mariana_horas','ant_mariana','Pago por horas',10,500,null,['horas'=>10]);
    $agregarConceptoPlanilla('ant_mariana_bono','ant_mariana','Bono institucional',1,100,'Bono institucional',['automatico'=>true]);
    $agregarConceptoPlanilla('ant_mariana_tarde','ant_mariana','Descuento por tardanza',80,-40,'Minuto de tardanza',['minutos'=>80]);

    $crearDetallePlanilla('act_maria','actual','maria',2400,0,60,2340,0,'CERRADO');
    $crearDetallePlanilla('act_carlos','actual','carlos',900,0,30,870,870,'PAGADO');
    $crearDetallePlanilla('act_lucia','actual','lucia',700,0,0,700,350,'PARCIAL');
    $crearDetallePlanilla('act_mariana','actual','mariana',2000,100,40,2060,2060,'PAGADO');
    $crearDetallePlanilla('act_jorge','actual','jorge',1900,0,50,1850,1000,'PARCIAL');
    $crearDetallePlanilla('act_rosa','actual','rosa',1650,0,0,1650,0,'CERRADO');
    $crearDetallePlanilla('act_patricia','actual','patricia',2100,150,0,2250,2250,'PAGADO');
    $crearDetallePlanilla('act_ernesto','actual','ernesto',1800,0,20,1780,0,'CERRADO');
    $crearDetallePlanilla('act_diana','actual','diana',3500,0,0,3500,0,'CERRADO');
    $crearDetallePlanilla('act_raul','actual','raul',850,0,0,850,0,'CERRADO');
    $agregarConceptoPlanilla('act_maria_base','act_maria','Remuneracion mensual',21,2400,null,['dias'=>21,'feriados'=>1]);
    $agregarConceptoPlanilla('act_maria_tarde','act_maria','Descuento por tardanza',120,-60,'Minuto de tardanza',['minutos'=>120]);
    $agregarConceptoPlanilla('act_carlos_horas','act_carlos','Pago por horas',20,900,null,['horas'=>20]);
    $agregarConceptoPlanilla('act_carlos_tarde','act_carlos','Descuento por tardanza',60,-30,'Minuto de tardanza',['minutos'=>60]);
    $agregarConceptoPlanilla('act_lucia_sesiones','act_lucia','Pago por sesiones',10,700,null,['sesiones'=>10,'esperadas'=>12]);
    $agregarConceptoPlanilla('act_mariana_base','act_mariana','Remuneracion mensual',21,1500,null,['dias'=>21]);
    $agregarConceptoPlanilla('act_mariana_horas','act_mariana','Pago por horas',10,500,null,['horas'=>10]);
    $agregarConceptoPlanilla('act_mariana_bono','act_mariana','Bono institucional',1,100,'Bono institucional',['automatico'=>true]);
    $agregarConceptoPlanilla('act_mariana_tarde','act_mariana','Descuento por tardanza',80,-40,'Minuto de tardanza',['minutos'=>80]);
    $agregarConceptoPlanilla('act_jorge_base','act_jorge','Remuneracion mensual',21,1900,null,['dias'=>21]);
    $agregarConceptoPlanilla('act_jorge_tarde','act_jorge','Descuento por tardanza',100,-50,'Minuto de tardanza',['minutos'=>100]);
    $agregarConceptoPlanilla('act_rosa_base','act_rosa','Remuneracion mensual',21,1650,null,['dias'=>21]);
    $agregarConceptoPlanilla('act_patricia_base','act_patricia','Remuneracion mensual',21,2100,null,['dias'=>21]);
    $agregarConceptoPlanilla('act_patricia_ajuste','act_patricia','Ajuste manual ingreso',1,150,null,['motivo'=>'Responsabilidad adicional','manual'=>true]);
    $agregarConceptoPlanilla('act_ernesto_base','act_ernesto','Remuneracion mensual',21,1800,null,['dias'=>21]);
    $agregarConceptoPlanilla('act_ernesto_desc','act_ernesto','Ajuste manual descuento',1,-20,null,['motivo'=>'Descuento autorizado','manual'=>true]);
    $agregarConceptoPlanilla('act_diana_base','act_diana','Remuneracion mensual',21,3500,null,['dias'=>21]);
    $agregarConceptoPlanilla('act_raul_base','act_raul','Remuneracion mensual',10,850,null,['dias'=>10,'proporcional'=>true]);

    ejecutarDemo($conexion, "INSERT INTO planilla_ajustes(id_planilla_detalle,id_detalle_concepto,naturaleza,importe,motivo,registrado_por,registrado_en,estado) VALUES(?,?,?,?,?,?,NOW(),'ACTIVO')", [$detallesPlanilla['act_patricia'],$conceptosDetalle['act_patricia_ajuste'],'BONIFICACION',150,'Responsabilidad adicional',$usuarios['planilla']]);
    ejecutarDemo($conexion, "INSERT INTO planilla_ajustes(id_planilla_detalle,id_detalle_concepto,naturaleza,importe,motivo,registrado_por,registrado_en,estado,anulado_por,anulado_en,motivo_anulacion) VALUES(?,NULL,'DESCUENTO',75,'Descuento ingresado por error',?,?,'ANULADO',?,?,?)", [$detallesPlanilla['act_maria'],$usuarios['planilla'],fechaHoraDemo($ayer,'11:00:00'),$usuarios['planilla'],fechaHoraDemo($ayer,'11:15:00'),'Importe equivocado']);

    $pagosPlanilla = [];
    $crearPagoPlanilla = function(string $clave, string $detalle, float $importe, string $medio, string $referencia, string $fechaHora, string $estado = 'REGISTRADO', ?string $motivo = null) use (&$pagosPlanilla,$conexion,&$detallesPlanilla,$usuarios): int {
        $id = insertarDemo($conexion, 'INSERT INTO pagos_planilla(id_planilla_detalle,importe,medio_pago,referencia,observacion,pagado_por,pagado_en,estado,anulado_por,anulado_en,motivo_anulacion) VALUES(?,?,?,?,?,?,?,?,?,?,?)', [
            $detallesPlanilla[$detalle],$importe,$medio,$referencia,'Pago de demostracion',$usuarios['planilla'],$fechaHora,$estado,$estado === 'ANULADO' ? $usuarios['planilla'] : null,$estado === 'ANULADO' ? $fechaHora : null,$motivo,
        ]);
        $pagosPlanilla[$clave] = $id;
        return $id;
    };
    $fechaPagoAnterior = fechaHoraDemo(fechaDemo($finMesAnterior,'+2 days'),'10:00:00');
    $crearPagoPlanilla('ant_maria','ant_maria',2450,'TRANSFERENCIA',"NOM-{$anio}-0701",$fechaPagoAnterior);
    $crearPagoPlanilla('ant_carlos','ant_carlos',1775,'DEPOSITO',"NOM-{$anio}-0702",$fechaPagoAnterior);
    $crearPagoPlanilla('ant_lucia','ant_lucia',840,'EFECTIVO',"NOM-{$anio}-0703",$fechaPagoAnterior);
    $crearPagoPlanilla('ant_mariana','ant_mariana',2060,'TRANSFERENCIA',"NOM-{$anio}-0704",$fechaPagoAnterior);
    $crearPagoPlanilla('act_carlos','act_carlos',870,'TRANSFERENCIA',"NOM-{$anio}-0801",fechaHoraDemo($hoy,'12:00:00'));
    $crearPagoPlanilla('act_lucia','act_lucia',350,'EFECTIVO',"NOM-{$anio}-0802",fechaHoraDemo($hoy,'12:10:00'));
    $crearPagoPlanilla('act_mariana','act_mariana',2060,'DEPOSITO',"NOM-{$anio}-0803",fechaHoraDemo($hoy,'12:20:00'));
    $crearPagoPlanilla('act_jorge','act_jorge',1000,'TRANSFERENCIA',"NOM-{$anio}-0804",fechaHoraDemo($hoy,'12:30:00'));
    $crearPagoPlanilla('act_patricia','act_patricia',2250,'TRANSFERENCIA',"NOM-{$anio}-0805",fechaHoraDemo($hoy,'12:40:00'));
    $crearPagoPlanilla('act_maria_anulado','act_maria',100,'OTRO',"ERR-{$anio}-0001",fechaHoraDemo($hoy,'12:50:00'),'ANULADO','Pago duplicado');

    // El trigger de insercion inicializa los saldos; se concilian despues de registrar los pagos.
    ejecutarDemo($conexion, "UPDATE planilla_detalles pd LEFT JOIN (SELECT id_planilla_detalle,SUM(importe) pagado FROM pagos_planilla WHERE estado='REGISTRADO' GROUP BY id_planilla_detalle) pg ON pg.id_planilla_detalle=pd.id_planilla_detalle SET pd.monto_pagado=COALESCE(pg.pagado,0),pd.saldo_pendiente=GREATEST(0,pd.monto_pagar-COALESCE(pg.pagado,0)),pd.estado=CASE WHEN COALESCE(pg.pagado,0)>=pd.monto_pagar THEN 'PAGADO' WHEN COALESCE(pg.pagado,0)>0 THEN 'PARCIAL' ELSE 'CERRADO' END");

    // Auditoria transversal para mostrar acciones y cambios en distintos modulos.
    $auditorias = [
        [$usuarios['admin'],'INICIO_DATOS_DEMO','sistema',null,null,['version'=>'V01']],
        [$usuarios['director'],'ACTUALIZAR','periodos_academicos',$periodos[$anio],['estado'=>'PLANIFICADO'],['estado'=>'ACTIVO']],
        [$usuarios['asistencia'],'REGISTRAR_MANUAL','asistencias_diarias',$asistencias['lucia_hoy'],null,['estado'=>'PUNTUAL']],
        [$usuarios['caja'],'REGISTRAR_PAGO','pagos_alumno',$pagosAlumno['recibo_multiple'],null,['importe'=>920]],
        [$usuarios['planilla'],'CERRAR_PLANILLA','periodos_pago',$periodosPago['actual'],['estado'=>'REVISADO'],['estado'=>'CERRADO']],
    ];
    foreach ($auditorias as $a) ejecutarDemo($conexion, 'INSERT INTO auditoria(id_usuario,accion,entidad,id_entidad,valores_antes,valores_despues,ip,creado_en) VALUES(?,?,?,?,?,?,?,NOW())', [$a[0],$a[1],$a[2],$a[3],$a[4] === null ? null : json_encode($a[4]),$a[5] === null ? null : json_encode($a[5]),'127.0.0.1']);

    if ($simular) {
        $conexion->rollBack();
        echo "SIMULACION CORRECTA: todas las relaciones fueron validadas y no se conservaron cambios.\n";
        exit(0);
    }

    $conexion->commit();
    $qrGenerados = 0;
    $advertenciasQr = [];
    require_once __DIR__ . '/../app/Services/QrService.php';
    $servicioQr = new QrService();
    foreach ($personasQr as $clavePersona) {
        try {
            $servicioQr->emitir($conexion, $personas[$clavePersona]);
            $qrGenerados++;
        } catch (Throwable $errorQr) {
            $advertenciasQr[] = $clavePersona . ': ' . $errorQr->getMessage();
        }
    }

    echo "DATOS DEMO CREADOS CORRECTAMENTE.\n";
    echo "Base: {$nombreBase}\n";
    echo "Respaldo conservado: {$respaldo}\n";
    echo "Super Administrador conservado: {$admin['usuario']}\n";
    echo "Usuarios demo: director.demo, asistencia.demo, caja.demo, planilla.demo, docente.demo y apoderado.demo\n";
    echo "Clave comun demo: Demo2026*\n";
    echo "QR activos generados: {$qrGenerados}\n";
    if ($advertenciasQr) echo "Advertencias QR: " . implode(' | ', $advertenciasQr) . "\n";
} catch (Throwable $e) {
    if ($conexion->inTransaction()) $conexion->rollBack();
    try { $conexion->exec('SET FOREIGN_KEY_CHECKS=1'); } catch (Throwable $ignorado) {}
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
