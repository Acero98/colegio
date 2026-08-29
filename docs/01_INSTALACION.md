# Instalación y puesta en marcha

## 1. Requisitos

| Componente | Recomendación |
|---|---|
| Servidor web | Apache 2.4 |
| PHP | 7.4 o superior |
| Base de datos | MySQL 8.0 |
| PHP | PDO MySQL, mbstring, GD y JSON habilitados |
| Dependencias | Composer 2 |
| Cliente | Chrome, Edge o Firefox actualizado |

Para utilizar la cámara desde un teléfono mediante una dirección IP, el navegador normalmente exige HTTPS. En `localhost` esta restricción suele relajarse.

## 2. Ubicación del proyecto

En Laragon puede mantenerse en:

```text
C:\laragon\www\colegio
```

La opción recomendada es configurar un VirtualHost cuyo `DocumentRoot` sea:

```text
C:\laragon\www\colegio\public
```

Ejemplo:

```apache
define ROOT "C:/laragon/www/colegio/public"
define SITE "colegio.test"

<VirtualHost *:80>
    DocumentRoot "${ROOT}"
    ServerName ${SITE}
    <Directory "${ROOT}">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

El `index.php` de la raíz también redirige hacia `public` cuando se ingresa mediante `http://localhost/colegio/`, pero el VirtualHost evita mostrar `public` en la dirección.

## 3. Crear la base

Cree una base vacía con codificación UTF-8:

```sql
CREATE DATABASE colegio
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Importe solamente uno de los siguientes archivos:

| Archivo | Uso |
|---|---|
| `db/colegio_limpio.sql` | Inicio real sin datos operativos; conserva un Super Administrador |
| `db/colegio_demo.sql` | Presentación, capacitación y pruebas con todos los casos |

Los archivos incluyen las 50 tablas, relaciones, índices y los dos triggers de planilla. Deben importarse sobre una base vacía.

## 4. Configurar la conexión

Copie:

```text
config/local.example.php
```

como:

```text
config/local.php
```

Complete únicamente valores locales:

```php
return [
    'host' => 'localhost',
    'database' => 'colegio',
    'username' => 'usuario_aplicacion',
    'password' => 'una-clave-segura',
];
```

No publique este archivo ni lo adjunte a respaldos destinados a terceros. En producción se recomienda un usuario MySQL exclusivo para la aplicación, no `root`.

## 5. Dependencias y permisos

Si `vendor` no existe:

```powershell
cd C:\laragon\www\colegio
composer install --no-dev --optimize-autoloader
```

El usuario con el que se ejecuta Apache debe poder escribir en:

- `storage/fotos`
- `storage/qr`
- `storage/institucion`
- `storage/comprobantes_pago`
- `storage/movimientos_financieros`
- `storage/rectificaciones_caja`
- `storage/logs`

## 6. Primer ingreso

Con `colegio_limpio.sql`, utilice el usuario administrador y la contraseña vigentes al momento de generar el archivo. Después del primer ingreso:

1. Cambie la contraseña si la instalación será entregada a otra institución.
2. Complete **Configuración → Datos institucionales**.
3. Cree el período académico.
4. Defina niveles, grados, secciones y capacidades.
5. Configure jornadas, calendario, conceptos financieros y reglas de planilla.
6. Cree usuarios operativos con los permisos mínimos necesarios.

`public/install.php` es una alternativa local únicamente cuando la tabla `usuarios` está vacía. No se utiliza después de importar los SQL entregados y debe eliminarse o bloquearse en producción.

## 7. Comprobaciones

- La pantalla de inicio de sesión responde sin errores.
- El administrador puede ingresar.
- El logo y los datos institucionales se guardan.
- Se puede crear una persona y una matrícula de prueba.
- Se puede abrir y cerrar una caja de prueba.
- La cámara dispone de permiso para escanear QR.
- `storage/logs` permite escribir registros.

## 8. Copias de seguridad

Debe respaldarse conjuntamente:

1. La base de datos MySQL.
2. Toda la carpeta `storage`.
3. `config/local.php` en un repositorio seguro de credenciales, no en Git.

Un SQL no recupera fotografías, evidencias, logos ni archivos QR. Pruebe periódicamente la restauración en una base separada.

## 9. Producción

- Use HTTPS.
- Desactive el listado de directorios y los errores visibles de PHP.
- Mantenga `public` como única raíz pública.
- Elimine o bloquee `public/install.php`.
- Cambie las claves de demostración.
- Configure rotación de `storage/logs/app-errors.log`.
- Programe respaldos automáticos.
- No utilice datos demostrativos junto con datos reales.
