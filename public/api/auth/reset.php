<?php
require_once __DIR__ . '/../../../app/session.php';
require_once __DIR__ . '/../../../app/config.php';
require_once __DIR__ . '/../../../app/db.php';
require_once __DIR__ . '/../../../app/models/password_reset.php';

$db   = DB::conn();
$pr   = new PasswordReset($db);
$msg  = null;
$ok   = false;

$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));

// Si POST: intentar cambio
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $pass1 = (string)($_POST['password'] ?? '');
  $pass2 = (string)($_POST['password2'] ?? '');

  if ($token === '' || !$pr->findValidByToken($token)) {
    $msg = 'El enlace no es válido o venció.';
  } elseif ($pass1 === '' || $pass2 === '' || $pass1 !== $pass2) {
    $msg = 'Las contraseñas no coinciden.';
  } else {
    // Recuperar registro y usuario
    $row = $pr->findValidByToken($token);
    if (!$row) {
      $msg = 'El enlace no es válido o venció.';
    } else {
      $userId = (int)$row['user_id'];

      // Guardar contraseña en texto plano (según tu decisión actual)
      $st = $db->prepare("UPDATE usuarios SET Password = :p WHERE Usuario_ID = :u");
      $st->execute([':p' => $pass1, ':u' => $userId]);

      // Invalidar token
      $pr->markUsed((int)$row['id']);

      // Cerrar sesión actual por seguridad
      session_unset();
      session_destroy();

      $ok  = true;
      $msg = 'Contraseña actualizada correctamente. Ya podés ingresar.';
    }
  }
} else {
  // GET: validar token para mostrar formulario
  if ($token === '' || !$pr->findValidByToken($token)) {
    $msg = 'El enlace no es válido o venció.';
  }
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Restablecer contraseña</title>
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
    <h2>Restablecer contraseña</h2>

    <?php if ($msg): ?>
      <div class="msg"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!$ok && $token && $pr->findValidByToken($token)): ?>
      <form method="post" autocomplete="off">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <label>Nueva contraseña</label>
        <input type="password" name="password" required autocomplete="new-password" minlength="4">

        <label>Repetir contraseña</label>
        <input type="password" name="password2" required autocomplete="new-password" minlength="4">

        <button>Cambiar contraseña</button>
      </form>
    <?php else: ?>
      <p style="margin-top:10px;"><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/api/auth/forgot.php">Volver a recuperar</a></p>
    <?php endif; ?>

    <?php if ($ok): ?>
      <p style="margin-top:10px;"><a href="../../login.php">Volver al login</a>
    <?php endif; ?>
  </div>
</body>

</html>