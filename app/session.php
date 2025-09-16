<?php
$cfg = __DIR__ . '/config.php';
if (file_exists($cfg)) {
  require_once $cfg;
}

if (!defined('ROLE_SUPERUSUARIO')) define('ROLE_SUPERUSUARIO', 1);
if (!defined('ROLE_SEGURIDAD'))    define('ROLE_SEGURIDAD',    2);
if (!defined('BASE_URL')) {
  $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
  define('BASE_URL', $scriptDir === '' ? '/' : $scriptDir);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
  ]);
  session_start();
}

function setUserSession(array $u, bool $isOperator = true): void {
  if (function_exists('session_regenerate_id')) {
    session_regenerate_id(true);
  }
  $_SESSION['user_id']     = $u['Usuario_ID']    ?? $u['id'] ?? null;
  $_SESSION['dni']         = $u['Num_Documento'] ?? null;
  $_SESSION['nombre']      = $u['Nombre']        ?? $u['nombre'] ?? null;
  $_SESSION['apellido']    = $u['Apellido']      ?? $u['apellido'] ?? null;
  $_SESSION['rol_id']      = $u['Rol_ID']        ?? $u['rol_id'] ?? null;
  $_SESSION['rol']         = $u['rol']           ?? null;
  $_SESSION['is_operator'] = $isOperator;
}

function isLoggedIn(): bool {
  return isset($_SESSION['user_id']);
}

function currentUser() {
  return [
    'id'       => $_SESSION['user_id']   ?? null,
    'dni'      => $_SESSION['dni']       ?? null,
    'nombre'   => $_SESSION['nombre']    ?? null,
    'apellido' => $_SESSION['apellido']  ?? null,
    'rol_id'   => $_SESSION['rol_id']    ?? null,
    'rol'      => $_SESSION['rol']       ?? null,
  ];
}

function requireLogin() {
  if (!isLoggedIn()) {
    header('Location: '.(BASE_URL.'/login.php'));
    exit;
  }
}

function logout() {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
    );
  }
  session_destroy();
}

function isOperator(): bool {
  return isset($_SESSION['is_operator']) && $_SESSION['is_operator'] === true;
}

function requireOperator() {
  if (!isLoggedIn() || !isOperator()) {
    header('Location: '.(BASE_URL.'/login.php'));
    exit;
  }
}

function requireRole(array $allowedRolIds) {
  requireOperator();
  $rid = (int)($_SESSION['rol_id'] ?? 0);

  if (!in_array($rid, $allowedRolIds, true)) {
    if ($rid === ROLE_SEGURIDAD) {
      header('Location: '.(BASE_URL.'/kiosco.php'));
    } elseif ($rid === ROLE_SUPERUSUARIO) {
      header('Location: '.(BASE_URL.'/reportes.php'));
    } else {
      header('Location: '.(BASE_URL.'/login.php'));
    }
    exit;
  }
}
