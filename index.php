<?php

$rutaProyecto = rtrim(
    str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')),
    '/'
);

header('Location: ' . $rutaProyecto . '/public/', true, 302);
exit;