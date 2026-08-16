<?php

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../Models/UsuarioModel.php';
require_once __DIR__ . '/../Services/AuditoriaService.php';

class AuthController
{
    public function login(): void
    {
        if (autenticado()) {
            header('Location: index.php?url=dashboard');
            exit;
        }
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function auth(): void
    {
        validarCsrf();
        global $conexion;
        $model = new UsuarioModel($conexion);
        $registro = $model->buscarParaLogin(trim($_POST['usuario'] ?? ''));

        if (!$registro || !password_verify($_POST['contrasena'] ?? '', $registro['contrasena'])) {
            mensaje('error', 'Usuario o contraseña incorrectos.');
            header('Location: index.php?url=login');
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id_usuario' => (int) $registro['id_usuario'],
            'id_persona' => $registro['id_persona'] ? (int) $registro['id_persona'] : null,
            'nombre' => trim($registro['nombres'] . ' ' . $registro['apellidos']),
            'rol' => $registro['nombre'],
            'permisos' => $model->permisos((int) $registro['id_usuario']),
        ];
        (new AuditoriaService($conexion))->registrar('INICIO_SESION', 'usuarios', (int) $registro['id_usuario']);
        header('Location: index.php?url=' . ($registro['nombre'] === 'Apoderado' ? 'portal_familia' : 'dashboard'));
        exit;
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: index.php?url=login');
        exit;
    }
}
