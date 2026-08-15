<!doctype html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/inter.css"><link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap-icons.min.css">
<title>Fotocheck · <?= htmlspecialchars($alumno['nombres']) ?></title>
<style>
*{box-sizing:border-box}body{margin:0;background:#eef2f7;font-family:Inter,Arial,sans-serif;color:#172033}.actions{max-width:86mm;margin:24px auto;text-align:center}.actions button{border:0;border-radius:9px;background:#2563eb;color:#fff;padding:10px 16px;font-weight:600;cursor:pointer}.carnet{position:relative;width:85.60mm;height:53.98mm;margin:24px auto;overflow:hidden;border:1px solid #cbd5e1;border-radius:3mm;background:#fff;box-shadow:0 8px 24px #33415530}.barra{height:11mm;padding:3.5mm 4mm;background:linear-gradient(135deg,#1d4ed8,#0ea5e9);color:#fff}.barra strong{display:block;font-size:10pt}.barra small{font-size:6.6pt}.contenido{display:flex;gap:3mm;padding:3.5mm 4mm}.foto{display:grid;width:18mm;height:22mm;place-items:center;overflow:hidden;border:1px solid #bfdbfe;border-radius:2mm;background:#eff6ff;color:#2563eb;font-size:18pt}.foto img{width:100%;height:100%;object-fit:cover}.datos{width:35mm}.datos .nombre{font-size:8.5pt;font-weight:800;line-height:1.2}.datos p{margin:1.4mm 0;font-size:6.3pt;color:#475569}.qr{width:18mm;height:18mm;margin-left:auto}.qr img{width:100%;height:100%}.pie{position:absolute;bottom:2.5mm;left:4mm;font-size:5.3pt;color:#64748b}
@media print{body{background:#fff}.actions{display:none}.carnet{margin:0;box-shadow:none}}
</style></head><body>
<div class="actions"><button onclick="window.print()"><i class="bi bi-printer"></i> Imprimir fotocheck CR80</button></div>
<section class="carnet">
    <div class="barra"><strong>INSTITUCIÓN EDUCATIVA</strong><small>Fotocheck de alumno</small></div>
    <div class="contenido"><div class="foto"><?php if($alumno['fotografia']): ?><img src="index.php?url=foto_persona&id=<?= $alumno['id_persona'] ?>" alt="Foto"><?php else: ?><i class="bi bi-person-fill"></i><?php endif; ?></div><div class="datos"><div class="nombre"><?= htmlspecialchars($alumno['nombres'] . ' ' . $alumno['apellidos']) ?></div><p><b>Código:</b> <?= htmlspecialchars($alumno['codigo_alumno']) ?></p><p><b>Sección:</b> <?= htmlspecialchars($alumno['seccion']) ?></p><p><b>Documento:</b> <?= htmlspecialchars($alumno['numero_documento']) ?></p></div><div class="qr"><img src="index.php?url=fotocheck_qr&id=<?= $alumno['id_qr'] ?>" alt="QR"></div></div>
    <div class="pie">Documento institucional · QR de uso exclusivo para asistencia</div>
</section></body></html>
