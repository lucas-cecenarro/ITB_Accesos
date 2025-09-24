<?php
require_once __DIR__ . '/../../../app/session.php';
require_once __DIR__ . '/../../../app/config.php';
require_once __DIR__ . '/../../../app/db.php';

require_once __DIR__ . '/../../../app/models/password_reset.php';
require_once __DIR__ . '/../../../app/lib/mailer.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$db     = DB::conn();

$message = null;

if ($method === 'POST') {
  $email = trim($_POST['email'] ?? '');

  // === Log simple para depurar (se borra luego) ===
  $logDir = __DIR__ . '/../../../app/var';
  if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
  $logFile = $logDir . '/auth_debug.log';
  $__log = function (string $m) use ($logFile) {
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " | " . $m . "\n", FILE_APPEND);
  };

  $__log("FORGOT POST email='{$email}'");

  // Buscamos usuario con Rol 1/2 (ajustado a columnas reales)
  $st = $db->prepare(
    "SELECT Usuario_ID AS usuario_id, Correo AS correo
     FROM usuarios
     WHERE Correo = :e AND Rol_ID IN (1,2)
     LIMIT 1"
  );
  $st->execute([':e' => $email]);
  $user = $st->fetch(PDO::FETCH_ASSOC);

  $message = 'Si el correo existe, te enviamos un enlace para restablecer la contraseña. Revisá tu bandeja.';

  if ($user) {
    $__log("Usuario encontrado id={$user['usuario_id']} correo={$user['correo']}");

    $pr  = new PasswordReset($db);
    $ip  = $_SERVER['REMOTE_ADDR']      ?? null;
    $ua  = $_SERVER['HTTP_USER_AGENT']  ?? null;
    $tok = $pr->createForUser((int)$user['usuario_id'], (string)$ip, (string)$ua, 30);

    // Construir link ABSOLUTO sin depender de BASE_URL (evita duplicados)
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    // $basePath aquí es /public/api/auth ; apuntamos a reset.php en el mismo dir:
    $link = "{$scheme}://{$host}{$basePath}/reset.php?token=" . urlencode($tok);

    // Enviar (MailHog/Log)
    $okSend = sendPasswordReset($user['correo'], $link);
    $__log("sendPasswordReset=" . ($okSend ? 'OK' : 'FAIL') . " link={$link}");

    // DEBUG visible en pantalla (mientras APP_DEBUG=true)
    if (APP_DEBUG) {
      $message .= '<br><small>DEBUG: <a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">Abrir enlace de reset</a></small>';
    }
  } else {
    $__log("Usuario NO encontrado para email='{$email}'");
  }
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Olvidé mi contraseña</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: system-ui, Segoe UI, Roboto, Arial;
      background: #0f172a;
      color: #e2e8f0;
      display: grid;
      place-items: center;
      height: 100dvh;
      margin: 0
    }

    .card {
      background: #111827;
      padding: 24px;
      border-radius: 12px;
      min-width: 320px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, .3)
    }

    label {
      display: block;
      margin: 12px 0 4px
    }

    input {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #374151;
      background: #0b1220;
      color: #e5e7eb
    }

    button {
      margin-top: 16px;
      width: 100%;
      padding: 10px;
      border: 0;
      border-radius: 8px;
      background: #22c55e;
      color: #05120a;
      cursor: pointer
    }

    a {
      color: #93c5fd
    }

    .msg {
      margin-top: 10px;
      background: #0b1220;
      border: 1px solid #374151;
      border-radius: 8px;
      padding: 10px
    }
  </style>
</head>

<body>
  <div class="card">
    <h2>Recuperar contraseña</h2>
    <?php if ($message): ?>
      <div class="msg"><?= $message ?></div>
    <?php else: ?>
      <form method="post" autocomplete="off">
        <label>Email</label>
        <input type="email" name="email" required autocomplete="username" placeholder="tu@correo.com">
        <button>Enviar enlace</button>
      </form>
    <?php endif; ?>
    <p style="margin-top:10px;"><a href="../../login.php">Volver al login</a>
  </div>
</body>

</html>