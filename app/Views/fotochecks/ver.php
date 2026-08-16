<?php
$nombreInstitucion = defined('INSTITUCION_NOMBRE') ? INSTITUCION_NOMBRE : 'Caleb School';
$subtituloInstitucion = defined('INSTITUCION_SUBTITULO') ? INSTITUCION_SUBTITULO : SISTEMA_NOMBRE; 
$periodoInstitucional = defined('INSTITUCION_PERIODO') ? INSTITUCION_PERIODO : date('Y');
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/inter.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap-icons.min.css">
    <title>Fotocheck · <?= htmlspecialchars($alumno['nombres']) ?></title>
    <style>
        :root {
            --azul: #1d4ed8;
            --azul-oscuro: #173a8a;
            --celeste: #e0f2fe;
            --tinta: #172554;
            --muted: #64748b;
        }

        html {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            background: radial-gradient(circle at top, #dbeafe 0, #f8fafc 42%, #eef2ff 100%);
            color: var(--tinta);
            font-family: Inter, Arial, sans-serif;
        }

        .vista {
            width: min(100%, 38rem);
            padding: 2rem 1rem;
        }

        .acciones {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 auto 1rem;
            color: #475569;
            font-size: .82rem;
        }

        .acciones strong {
            color: var(--tinta);
        }

        .acciones button {
            border: 0;
            border-radius: .75rem;
            background: var(--azul);
            padding: .65rem .9rem;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 5px 12px #1d4ed83d;
        }

        .acciones button:hover {
            background: var(--azul-oscuro);
        }

        .carnet {
            position: relative;
            width: 85.60mm;
            height: 53.98mm;
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid #bfdbfe;
            border-radius: 3.2mm;
            background: #fff;
            box-shadow: 0 16px 34px #1e3a8a33;
        }

        .carnet::after {
            content: '';
            position: absolute;
            z-index: 0;
            right: -13mm;
            bottom: -15mm;
            width: 48mm;
            height: 48mm;
            border: 7mm solid #dbeafe;
            border-radius: 50%;
            opacity: .8;
        }

        .cabecera {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 2.4mm;
            min-height: 13.2mm;
            padding: 2.7mm 4mm;
            background: linear-gradient(120deg, #172554 0%, #1d4ed8 57%, #0284c7 100%);
            color: #fff;
        }

        .sello {
            display: grid;
            width: 8.3mm;
            height: 8.3mm;
            flex: 0 0 auto;
            place-items: center;
            border: .45mm solid #bfdbfe;
            border-radius: 50%;
            background: #ffffff1a;
            font-size: 12pt;
        }

        .institucion {
            min-width: 0;
        }

        .institucion strong {
            display: block;
            overflow: hidden;
            font-size: 8.4pt;
            line-height: 1.05;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .institucion small {
            display: block;
            margin-top: .7mm;
            font-size: 5.7pt;
            letter-spacing: .03em;
            opacity: .86;
        }

        .tipo {
            margin-left: auto;
            border: .35mm solid #ffffff70;
            border-radius: 5mm;
            padding: 1mm 1.8mm;
            font-size: 5.2pt;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .contenido {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 18mm minmax(0, 1fr) 18mm;
            gap: 2.8mm;
            padding: 3.3mm 4mm 2.4mm;
        }

        .foto {
            display: grid;
            width: 18mm;
            height: 21.8mm;
            place-items: center;
            overflow: hidden;
            border: .5mm solid #93c5fd;
            border-radius: 2.2mm;
            background: linear-gradient(145deg, #eff6ff, #dbeafe);
            color: #2563eb;
            font-size: 19pt;
        }

        .foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .datos {
            min-width: 0;
            padding-top: .3mm;
        }

        .nombre {
            margin-bottom: 1.4mm;
            color: #0f255d;
            font-size: 8.4pt;
            font-weight: 800;
            line-height: 1.14;
            text-transform: uppercase;
        }

        .dato {
            margin: 1mm 0;
            color: #475569;
            font-size: 5.8pt;
            line-height: 1.25;
        }

        .dato b {
            display: inline-block;
            min-width: 10.5mm;
            color: #1e3a8a;
            font-weight: 800;
        }

        .qr-bloque {
            align-self: start;
            padding: 1.2mm;
            border: .35mm solid #bfdbfe;
            border-radius: 1.8mm;
            background: #fff;
            box-shadow: 0 2px 6px #1d4ed81f;
        }

        .qr-bloque img {
            display: block;
            width: 15.6mm;
            height: 15.6mm;
        }

        .qr-texto {
            display: block;
            margin-top: .7mm;
            color: #1d4ed8;
            font-size: 4.2pt;
            font-weight: 800;
            letter-spacing: .04em;
            text-align: center;
            text-transform: uppercase;
        }

        .pie {
            position: absolute;
            z-index: 1;
            right: 4mm;
            bottom: 2.5mm;
            left: 4mm;
            display: flex;
            justify-content: space-between;
            padding-top: 1.4mm;
            border-top: .25mm solid #dbeafe;
            color: #64748b;
            font-size: 4.7pt;
        }

        .pie strong {
            color: #1e3a8a;
            font-weight: 800;
        }

        @page {
            size: 85.60mm 53.98mm;
            margin: 0;
        }

        @media screen {
            .vista {
                padding-bottom: 3.5rem;
            }

            .carnet {
                transform: scale(1.18);
                transform-origin: center top;
            }
        }

        @media print {

            html,
            body,
            .carnet,
            .cabecera,
            .foto,
            .qr-bloque {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body {
                display: block;
                min-height: 0;
                background: #fff;
            }

            .vista {
                width: auto;
                padding: 0;
            }

            .acciones {
                display: none;
            }

            .carnet {
                margin: 0;
                box-shadow: none;
                transform: none;
            }
        }
    </style>
</head>

<body>
    <main class="vista">
        <div class="acciones">
            <span><strong>Vista previa</strong> · Fotocheck institucional</span>
            <button type="button" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
        </div>

        <section class="carnet" aria-label="Fotocheck de <?= htmlspecialchars($alumno['nombres'] . ' ' . $alumno['apellidos']) ?>">
            <header class="cabecera">
                <span class="sello"><i class="bi bi-mortarboard-fill"></i></span>
                <div class="institucion">
                    <strong><?= htmlspecialchars($nombreInstitucion) ?></strong>
                    <small><?= htmlspecialchars($subtituloInstitucion) ?></small>
                </div>
                <span class="tipo">Alumno</span>
            </header>

            <div class="contenido">
                <div class="foto">
                    <?php if ($alumno['fotografia']): ?>
                        <img src="index.php?url=foto_persona&id=<?= $alumno['id_persona'] ?>" alt="Foto de <?= htmlspecialchars($alumno['nombres']) ?>">
                    <?php else: ?>
                        <i class="bi bi-person-fill"></i>
                    <?php endif; ?>
                </div>
                <div class="datos">
                    <div class="nombre"><?= htmlspecialchars($alumno['nombres'] . ' ' . $alumno['apellidos']) ?></div>
                    <p class="dato"><b>Código</b><?= htmlspecialchars($alumno['codigo_alumno']) ?></p>
                    <p class="dato"><b>Sección</b><?= htmlspecialchars($alumno['seccion']) ?></p>
                    <p class="dato"><b>Documento</b><?= htmlspecialchars($alumno['numero_documento']) ?></p>
                </div>
                <div class="qr-bloque">
                    <img src="index.php?url=fotocheck_qr&id=<?= $alumno['id_qr'] ?>" alt="Código QR de asistencia">
                    <span class="qr-texto">Asistencia QR</span>
                </div>
            </div>

            <footer class="pie">
                <span>Documento institucional de uso personal</span>
                <strong>Periodo <?= htmlspecialchars((string) $periodoInstitucional) ?></strong>
            </footer>
        </section>
    </main>
</body>

</html>