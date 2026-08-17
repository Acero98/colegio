<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/FotocheckModel.php';
require_once __DIR__ . '/../Services/QrService.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class FotocheckController
{
    public function index(): void
    {
        autorizar('fotochecks.ver');
        global $conexion;
        $personas = (new FotocheckModel($conexion))->listarPersonas();
        require __DIR__ . '/../Views/layouts/header.php';
        require __DIR__ . '/../Views/layouts/navbar.php';
        require __DIR__ . '/../Views/fotochecks/index.php';
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    public function generar(): void
    {
        autorizar('fotochecks.generar');
        validarCsrf();
        $idPersona = (int) ($_POST['id_persona'] ?? 0);
        global $conexion;
        $persona = (new FotocheckModel($conexion))->obtenerPersona($idPersona);
        if (!$persona) {
            mensaje('error', 'La persona no está habilitada para recibir un fotocheck.');
        } else {
            try {
                $idQr = (new QrService())->emitir($conexion, $idPersona);
                (new AuditoriaService($conexion))->registrar('GENERAR_QR', 'codigos_qr', $idQr, null, ['id_persona' => $idPersona]);
                mensaje('success', 'Código QR generado. El QR anterior quedó revocado.');
            } catch (Throwable $e) {
                mensaje('error', 'No fue posible generar el código QR.');
            }
        }
        header('Location: index.php?url=fotochecks');
        exit;
    }

    public function ver(): void
    {
        autorizar('fotochecks.ver');
        global $conexion;
        $alumno = (new FotocheckModel($conexion))->obtenerPersona((int) ($_GET['id'] ?? 0));
        if (!$alumno || empty($alumno['id_qr'])) {
            mensaje('warning', 'Genere primero el código QR de la persona.');
            header('Location: index.php?url=fotochecks');
            exit;
        }
        require __DIR__ . '/../Views/fotochecks/ver.php';
    }

    public function imagenQr(): void
    {
        autorizar('fotochecks.ver');
        global $conexion;
        $qr = (new FotocheckModel($conexion))->qr((int) ($_GET['id'] ?? 0));
        $ruta = $qr ? __DIR__ . '/../../storage/qr/' . basename($qr['archivo_qr']) : '';
        if (!$ruta || !is_file($ruta)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: image/png');
        header('Cache-Control: private, max-age=3600');
        readfile($ruta);
        exit;
    }
}
