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
            'fotografia' => null,
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
        if (!empty($_FILES['fotografia']['name'])) {
            $archivo = $_FILES['fotografia'];
            $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
            if ($archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > 2 * 1024 * 1024 || !isset($permitidos[$mime])) {
                mensaje('warning', 'La fotografía debe ser JPG, PNG o WEBP y pesar como máximo 2 MB.');
                header('Location: index.php?url=personas');
                exit;
            }
            $data['fotografia'] = bin2hex(random_bytes(16)) . '.' . $permitidos[$mime];
            if (!move_uploaded_file($archivo['tmp_name'], __DIR__ . '/../../storage/fotos/' . $data['fotografia'])) {
                mensaje('error', 'No se pudo guardar la fotografía.');
                header('Location: index.php?url=personas');
                exit;
            }
        }
        $id = $model->crear($data, $perfiles);
        (new AuditoriaService($conexion))->registrar('CREAR', 'personas', $id, null, $data + ['perfiles' => $perfiles]);
        mensaje('success', 'Persona registrada correctamente.');
        header('Location: index.php?url=personas');
        exit;
    }

    public function mostrarFoto(): void
    {
        requiereLogin();
        global $conexion;
        $foto = (new PersonaModel($conexion))->foto((int) ($_GET['id'] ?? 0));
        $ruta = $foto ? __DIR__ . '/../../storage/fotos/' . basename($foto) : '';
        if (!$ruta || !is_file($ruta)) {
            http_response_code(404);
            exit;
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($ruta);
        header('Content-Type: ' . $mime);
        header('Cache-Control: private, max-age=3600');
        readfile($ruta);
        exit;
    }

    public function actualizar(): void
    {
        autorizar('personas.crear'); validarCsrf(); global $conexion;
        try {
            $fotografia = $this->guardarFotografiaOpcional();
            (new PersonaModel($conexion))->actualizar($_POST, $_POST['perfiles'] ?? [], $fotografia);
            (new AuditoriaService($conexion))->registrar('EDITAR', 'personas', (int) $_POST['id_persona']);
            mensaje('success', 'Persona actualizada correctamente.');
        } catch (Throwable $e) {
            mensaje('error', 'No se pudo actualizar. Verifique que el documento no esté duplicado.');
        }
        header('Location:index.php?url=personas'); exit;
    }

    private function guardarFotografiaOpcional(): ?string
    {
        if (empty($_FILES['fotografia']['name'])) {
            return null;
        }
        $archivo = $_FILES['fotografia'];
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if ($archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > 2 * 1024 * 1024) {
            throw new RuntimeException('La fotografía debe ser JPG, PNG o WEBP y pesar como máximo 2 MB.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        if (!isset($permitidos[$mime])) {
            throw new RuntimeException('La fotografía debe ser JPG, PNG o WEBP y pesar como máximo 2 MB.');
        }
        $nombre = bin2hex(random_bytes(16)) . '.' . $permitidos[$mime];
        if (!move_uploaded_file($archivo['tmp_name'], __DIR__ . '/../../storage/fotos/' . $nombre)) {
            throw new RuntimeException('No se pudo guardar la fotografía.');
        }
        return $nombre;
    }

    public function cambiarEstado(): void
    {
        autorizar('personas.crear'); validarCsrf(); global $conexion;
        $idPersona = (int) ($_POST['id_persona'] ?? 0);
        $estado = $_POST['estado'] ?? '';
        if ($idPersona < 1 || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            mensaje('error', 'No se pudo cambiar el estado de la persona.');
        } else {
            try {
                (new PersonaModel($conexion))->cambiarEstado($idPersona, $estado);
                (new AuditoriaService($conexion))->registrar($estado === 'ACTIVO' ? 'ACTIVAR' : 'DESACTIVAR', 'personas', $idPersona);
                mensaje('success', $estado === 'ACTIVO' ? 'Persona activada correctamente.' : 'Persona desactivada correctamente.');
            } catch (Throwable $e) {
                mensaje('error', 'No se pudo cambiar el estado de la persona.');
            }
        }
        header('Location:index.php?url=personas'); exit;
    }
}
