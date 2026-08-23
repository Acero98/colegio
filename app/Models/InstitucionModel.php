<?php

class InstitucionModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtener(): array
    {
        $registro = $this->db->query('SELECT * FROM institucion ORDER BY id_institucion LIMIT 1')->fetch();
        return $registro ?: [
            'id_institucion' => 1,
            'nombre' => 'Institución educativa',
            'nombre_corto' => 'Colegio',
            'razon_social' => '',
            'ruc' => '',
            'direccion' => '',
            'telefono' => '',
            'correo' => '',
            'sitio_web' => '',
            'lema' => '',
            'codigo_modular' => '',
            'ugel' => '',
            'representante_nombre' => '',
            'representante_cargo' => '',
            'logo' => null,
        ];
    }

    public function actualizar(array $datos, ?string $logo, int $usuario): array
    {
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $nombreCorto = trim((string) ($datos['nombre_corto'] ?? ''));
        $razonSocial = trim((string) ($datos['razon_social'] ?? ''));
        $ruc = preg_replace('/\D+/', '', (string) ($datos['ruc'] ?? ''));
        $direccion = trim((string) ($datos['direccion'] ?? ''));
        $telefono = trim((string) ($datos['telefono'] ?? ''));
        $correo = trim((string) ($datos['correo'] ?? ''));
        $sitioWeb = trim((string) ($datos['sitio_web'] ?? ''));
        $lema = trim((string) ($datos['lema'] ?? ''));
        $codigoModular = trim((string) ($datos['codigo_modular'] ?? ''));
        $ugel = trim((string) ($datos['ugel'] ?? ''));
        $representante = trim((string) ($datos['representante_nombre'] ?? ''));
        $cargoRepresentante = trim((string) ($datos['representante_cargo'] ?? ''));

        if ($nombre === '' || mb_strlen($nombre) > 180) {
            throw new InvalidArgumentException('Ingrese el nombre institucional (máximo 180 caracteres).');
        }
        if ($nombreCorto === '') $nombreCorto = $nombre;
        if (mb_strlen($nombreCorto) > 80) throw new InvalidArgumentException('El nombre corto admite hasta 80 caracteres.');
        if ($ruc !== '' && !preg_match('/^\d{11}$/', $ruc)) throw new InvalidArgumentException('El RUC debe contener exactamente 11 dígitos.');
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Ingrese un correo institucional válido.');
        if ($sitioWeb !== '' && !filter_var($sitioWeb, FILTER_VALIDATE_URL)) throw new InvalidArgumentException('El sitio web debe incluir http:// o https://.');

        $actual = $this->obtener();
        $logoFinal = $logo ?: ($actual['logo'] ?? null);
        $sql = "INSERT INTO institucion
            (id_institucion,nombre,nombre_corto,razon_social,ruc,direccion,telefono,correo,sitio_web,lema,codigo_modular,ugel,representante_nombre,representante_cargo,logo,actualizado_por,actualizado_en)
            VALUES (1,:nombre,:nombre_corto,:razon_social,:ruc,:direccion,:telefono,:correo,:sitio_web,:lema,:codigo_modular,:ugel,:representante,:cargo,:logo,:usuario,NOW())
            ON DUPLICATE KEY UPDATE nombre=VALUES(nombre),nombre_corto=VALUES(nombre_corto),razon_social=VALUES(razon_social),ruc=VALUES(ruc),direccion=VALUES(direccion),telefono=VALUES(telefono),correo=VALUES(correo),sitio_web=VALUES(sitio_web),lema=VALUES(lema),codigo_modular=VALUES(codigo_modular),ugel=VALUES(ugel),representante_nombre=VALUES(representante_nombre),representante_cargo=VALUES(representante_cargo),logo=VALUES(logo),actualizado_por=VALUES(actualizado_por),actualizado_en=VALUES(actualizado_en)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':nombre_corto' => $nombreCorto,
            ':razon_social' => $razonSocial ?: null,
            ':ruc' => $ruc ?: null,
            ':direccion' => $direccion ?: null,
            ':telefono' => $telefono ?: null,
            ':correo' => $correo ?: null,
            ':sitio_web' => $sitioWeb ?: null,
            ':lema' => $lema ?: null,
            ':codigo_modular' => $codigoModular ?: null,
            ':ugel' => $ugel ?: null,
            ':representante' => $representante ?: null,
            ':cargo' => $cargoRepresentante ?: null,
            ':logo' => $logoFinal,
            ':usuario' => $usuario ?: null,
        ]);
        return $this->obtener();
    }
}
