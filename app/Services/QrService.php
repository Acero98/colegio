<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrService
{
    public function emitir(PDO $db, int $idPersona): int
    {
        $db->beginTransaction();
        try {
            $db->prepare("UPDATE codigos_qr SET estado='REVOCADO', revocado_en=NOW() WHERE id_persona=:persona AND estado='ACTIVO'")
                ->execute([':persona' => $idPersona]);
            $token = bin2hex(random_bytes(32));
            $archivo = 'qr_' . $idPersona . '_' . bin2hex(random_bytes(8)) . '.png';
            $ruta = __DIR__ . '/../../storage/qr/' . $archivo;
            $opciones = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_H,
                'scale' => 10,
                'imageBase64' => false,
            ]);
            (new QRCode($opciones))->render($token, $ruta);
            $stmt = $db->prepare("INSERT INTO codigos_qr(id_persona,token_hash,archivo_qr,estado,emitido_en) VALUES(:persona,:hash,:archivo,'ACTIVO',NOW())");
            $stmt->execute([':persona' => $idPersona, ':hash' => hash('sha256', $token), ':archivo' => $archivo]);
            $db->commit();
            return (int) $db->lastInsertId();
        } catch (Throwable $e) {
            $db->rollBack();
            if (isset($ruta) && is_file($ruta)) {
                unlink($ruta);
            }
            throw $e;
        }
    }
}
