<?php
require_once __DIR__ . '/../app/session.php';
require_once __DIR__ . '/../app/config.php';

// Si llega un POST, ejecutamos el login desde el controlador (incluido por PHP)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    AuthController::login();
    exit;
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Login - Control Acceso QR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui, Segoe UI, Roboto, Arial;
            margin: 0;
            background: #0f172a;
            color: #e2e8f0;
            display: grid;
            place-items: center;
            height: 100dvh
        }

        .card {
            background: #111827;
            padding: 24px;
            border-radius: 12px;
            min-width: 320px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .3)
        }

        h1 {
            font-size: 20px;
            margin: 0 0 12px
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
            cursor: pointer
        }

        .primary {
            background: #22c55e;
            color: #05120a
        }

        .error {
            background: #7f1d1d;
            color: #fecaca;
            padding: 10px;
            border-radius: 8px;
            margin: 10px 0
        }

        a {
            color: #93c5fd
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Acceso al sistema</h1>

        <?php if ($error): ?>
            <div style="color:#b00020;margin:8px 0;">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <label>Email</label>
            <input name="email" type="email" required autocomplete="username">

            <label>Contraseña</label>
            <input name="password" type="password" required autocomplete="current-password">

            <button class="primary">Ingresar</button>

            <p style="margin-top:10px;text-align:center;">
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/api/auth/forgot.php">
                    ¿Olvidaste tu contraseña?
                </a>
            </p>
        </form>
    </div>
</body>

</html>