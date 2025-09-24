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

// === SMTP ===
// APP_ENV: 'local' usa MailHog (127.0.0.1:1025). 'prod' usa SMTP real.
if (!defined('APP_ENV')) define('APP_ENV', 'prod'); // 'local' o 'prod'

// PRODUCCIÓN (ejemplo Gmail con App Password y TLS 587)
if (!defined('SMTP_HOST'))        define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT'))        define('SMTP_PORT', 587);
if (!defined('SMTP_SECURE'))      define('SMTP_SECURE', 'tls'); // 'tls' o 'ssl'
if (!defined('SMTP_USER'))        define('SMTP_USER', 'cecenarro08@gmail.com');
if (!defined('SMTP_PASS'))        define('SMTP_PASS', 'ylte elzt pjho jhit'); // 16 chars
if (!defined('SMTP_FROM'))        define('SMTP_FROM', 'cecenarro08@gmail.com'); // o tu gmail
if (!defined('SMTP_FROM_NAME'))   define('SMTP_FROM_NAME', 'Control Acceso ITB');
