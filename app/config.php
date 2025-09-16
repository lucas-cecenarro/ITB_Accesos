<?php
// ==== DB ====
define('DB_HOST',    '127.0.0.1');
define('DB_NAME',    'organizacion_accesos');   // <-- asegurate que coincida con tu base real
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ==== APP ====
define('APP_DEBUG', true);
date_default_timezone_set('America/Argentina/Buenos_Aires');

// URL base segura para redirecciones
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
define('BASE_URL', $scriptDir === '' ? '/' : $scriptDir);

// ==== ROLES (ajusta a tus IDs reales) ====
define('ROLE_SUPERUSUARIO', 1);
define('ROLE_SEGURIDAD',    2);
