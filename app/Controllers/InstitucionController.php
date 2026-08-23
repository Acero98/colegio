<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/InstitucionModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class InstitucionController
{
    public function index(): void
    {
        autorizar('configuracion.gestionar');
        global $conexion;
        $institucion = (new InstitucionModel($conexion))->obtener();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/institucion/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function guardar(): void
    {
        autorizar('configuracion.gestionar');
        validarCsrf();
        global $conexion;
        $modelo = new InstitucionModel($conexion);
        $antes = $modelo->obtener();
        $logo = null;
        try {
            $logo = $this->guardarLogo();
            $despues = $modelo->actualizar($_POST, $logo, (int) ($_SESSION['usuario']['id_usuario'] ?? 0));
            (new AuditoriaService($conexion))->registrar('ACTUALIZAR', 'institucion', 1, $antes, $despues);
            mensaje('success', 'Los datos institucionales se actualizaron correctamente en todo el sistema.');
        } catch (Throwable $e) {
            if ($logo) {
                $ruta = __DIR__ . '/../../storage/institucion/' . basename($logo);
                if (is_file($ruta)) @unlink($ruta);
            }
            mensaje('error', $e->getMessage());
        }
        header('Location: index.php?url=institucion');
        exit;
    }

    public function logo(): void
    {
        global $conexion;
        $logo = (new InstitucionModel($conexion))->obtener()['logo'] ?? null;
        $ruta = $logo ? __DIR__ . '/../../storage/institucion/' . basename($logo) : '';
        if (!$ruta || !is_file($ruta)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($ruta));
        header('Cache-Control: public, max-age=3600');
        readfile($ruta);
        exit;
    }

    private function guardarLogo(): ?string
    {
        if (empty($_FILES['logo']['name'])) return null;
        $archivo = $_FILES['logo'];
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($archivo['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('El logotipo debe pesar como máximo 2 MB.');
        }
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        if (!isset($permitidos[$mime])) throw new InvalidArgumentException('El logotipo debe ser JPG, PNG o WEBP.');
        $directorio = __DIR__ . '/../../storage/institucion';
        if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
            throw new RuntimeException('No se pudo preparar el directorio del logotipo.');
        }
        $nombre = bin2hex(random_bytes(16)) . '.' . $permitidos[$mime];
        if (!move_uploaded_file($archivo['tmp_name'], $directorio . '/' . $nombre)) {
            throw new RuntimeException('No se pudo guardar el logotipo.');
        }
        return $nombre;
    }
}
