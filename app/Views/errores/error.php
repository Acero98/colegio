<?php
$esc = static fn(string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
$nombreSistema = defined('SISTEMA_NOMBRE') ? SISTEMA_NOMBRE : 'Sistema escolar';
$puedeReintentar = in_array($estado, [419, 500, 503], true);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?= $estado ?> · <?= $esc($nombreSistema) ?></title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; color: #183153; background: radial-gradient(circle at top left, #e5f2ff 0, transparent 38%), #f5f8fc; }
        .card { width: min(560px, 100%); padding: 42px 38px; text-align: center; background: #fff; border: 1px solid #d9e7f5; border-radius: 24px; box-shadow: 0 22px 55px rgba(42, 74, 112, .13); }
        .icon { width: 72px; height: 72px; margin: 0 auto 20px; display: grid; place-items: center; border-radius: 22px; color: #155eef; background: #dcecff; font-size: 30px; font-weight: 800; }
        .code { margin: 0 0 8px; color: #155eef; font-size: 14px; font-weight: 800; letter-spacing: .13em; }
        h1 { margin: 0; font-size: clamp(25px, 5vw, 34px); line-height: 1.16; }
        .message { margin: 15px auto 0; max-width: 440px; color: #5d6f86; font-size: 16px; line-height: 1.65; }
        .reference { margin: 22px 0 0; padding: 11px 14px; color: #53657b; background: #f2f6fb; border-radius: 12px; font-size: 13px; }
        .actions { margin-top: 28px; display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; }
        .button { min-width: 155px; padding: 12px 18px; border: 1px solid #b9cbe0; border-radius: 12px; color: #23405f; background: #fff; font: inherit; font-weight: 700; text-decoration: none; cursor: pointer; }
        .button.primary { border-color: #2563eb; color: #fff; background: #2563eb; box-shadow: 0 8px 18px rgba(37, 99, 235, .2); }
        .brand { margin-top: 26px; color: #8493a7; font-size: 12px; }
    </style>
</head>
<body>
<main class="card" role="alert">
    <div class="icon" aria-hidden="true">!</div>
    <p class="code">ERROR <?= (int) $estado ?></p>
    <h1><?= $esc($titulo) ?></h1>
    <p class="message"><?= $esc($mensaje) ?></p>
    <?php if ($referencia): ?>
        <p class="reference">Código de referencia: <strong><?= $esc($referencia) ?></strong></p>
    <?php endif; ?>
    <div class="actions">
        <?php if ($puedeReintentar): ?>
            <button class="button primary" type="button" onclick="location.reload()">Intentar nuevamente</button>
        <?php endif; ?>
        <a class="button<?= $puedeReintentar ? '' : ' primary' ?>" href="index.php?url=dashboard">Volver al inicio</a>
    </div>
    <div class="brand"><?= $esc($nombreSistema) ?></div>
</main>
</body>
</html>
