<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/PersonaModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class PersonaController
{
    public function index(): void
    {
        autorizar('personas.ver');
        global $conexion;
        $personas = (new PersonaModel($conexion))->listar();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/personas/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function guardar(): void
    {
        autorizar('personas.crear');
        validarCsrf();
        $perfiles = array_values(array_intersect(
            $_POST['perfiles'] ?? [],
            ['ALUMNO', 'DOCENTE', 'ADMINISTRATIVO', 'APODERADO']
        ));
        $data = [
            'tipo_documento' => $_POST['tipo_documento'] ?? 'DNI',
            'numero_documento' => trim($_POST['numero_documento'] ?? ''),
            'nombres' => trim($_POST['nombres'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
        ];
        if (!$data['numero_documento'] || !$data['nombres'] || !$data['apellidos'] || !$perfiles) {
            mensaje('warning', 'Complete los datos obligatorios y seleccione por lo menos un perfil.');
            header('Location: index.php?url=personas');
            exit;
        }
        global $conexion;
        $model = new PersonaModel($conexion);
        if ($model->documentoExiste($data['tipo_documento'], $data['numero_documento'])) {
            mensaje('warning', 'Ya existe una persona con ese documento.');
            header('Location: index.php?url=personas');
            exit;
        }
        $id = $model->crear($data, $perfiles);
        (new AuditoriaService($conexion))->registrar('CREAR', 'personas', $id, null, $data + ['perfiles' => $perfiles]);
        mensaje('success', 'Persona registrada correctamente.');
        header('Location: index.php?url=personas');
        exit;
    }
}
