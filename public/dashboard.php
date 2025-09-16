<?php
require_once __DIR__ . '/../app/session.php';
requireRole([ROLE_SUPERUSUARIO, ROLE_SEGURIDAD]);

$nombre   = $_SESSION['nombre']   ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$rolId    = (int)($_SESSION['rol_id'] ?? 0);
$rolTxt   = $_SESSION['rol']      ?? '';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Dashboard - ITB</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        :root {
            --bg: #0f172a;
            --card: #111827;
            --muted: #94a3b8;
            --accent: #0ea5e9;
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            font-family: system-ui, Segoe UI, Roboto, Arial;
            background: var(--bg);
            color: #e2e8f0;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: #0b1220;
            border-bottom: 1px solid #1f2937;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .brand {
            display: flex;
            gap: 12px;
            align-items: center
        }

        .brand h1 {
            margin: 0;
            font-size: 18px
        }

        .user {
            font-size: 14px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge {
            background: #1f2937;
            border: 1px solid #334155;
            color: #cbd5e1;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            text-transform: capitalize;
        }

        .container {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px
        }

        .grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .card {
            background: var(--card);
            border: 1px solid #1f2937;
            border-radius: 12px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
        }

        .card h3 {
            margin: 0;
            font-size: 16px
        }

        .card p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.4
        }

        .actions {
            margin-top: auto;
            display: flex;
            gap: 10px
        }

        .btn {
            appearance: none;
            border: 1px solid #334155;
            background: #0b1220;
            color: #e2e8f0;
            padding: 10px 12px;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            width: 100%;
        }

        .btn.primary {
            background: var(--accent);
            color: #031018;
            border-color: transparent
        }

        .btn:active {
            transform: scale(.98)
        }

        footer {
            margin: 30px 0;
            text-align: center;
            color: #64748b;
            font-size: 12px
        }
    </style>
</head>

<body>
    <header>
        <div class="brand">
            <h1>Panel de Control — ITB</h1>
        </div>
        <div class="user">
            <span><?= htmlspecialchars("$nombre $apellido", ENT_QUOTES, 'UTF-8') ?></span>
            <span class="badge"><?= htmlspecialchars($rolTxt ?: ($rolId === 1 ? 'superusuario' : 'seguridad'), ENT_QUOTES, 'UTF-8') ?></span>
            <a class="btn" href="logout.php" style="padding:6px 10px">Cerrar sesión</a>
        </div>
    </header>

    <main class="container">
        <div class="grid">

            <?php if ($rolId === 2):
            ?>
                <div class="card">
                    <h3>Registro (Escáner QR)</h3>
                    <p>Escaneá códigos QR para registrar <b>ingresos/egresos</b> en tiempo real.</p>
                    <div class="actions">
                        <a class="btn primary" href="kiosco.php">Abrir Escaner</a>
                    </div>
                </div>

                <div class="card">
                    <h3>Visitas</h3>
                    <p>Registro manual de visitantes (sin QR) con datos mínimos obligatorios.</p>
                    <div class="actions">
                        <a class="btn primary" href="visita.php">Registrar Visita</a>
                    </div>
                </div>


            <?php endif; ?>

            <?php if ($rolId === 1):
            ?>
                <div class="card">
                    <h3>Usuarios / Actividad</h3>
                    <p>Consulta y filtrado de <b>movimientos</b>: ingresos, egresos y alertas.</p>
                    <div class="actions">
                        <a class="btn primary" href="usuarios.php">Abrir Buscador</a>
                    </div>
                </div>
                <div class="card">
                    <h3>Reportes</h3>
                    <p>Generación de reportes en <b>CSV</b> y <b>PDF</b> con filtros por fecha y tipo.</p>
                    <div class="actions">
                        <a class="btn primary" href="reportes.php">Generar Reportes</a>
                    </div>
                </div>

            <?php endif; ?>

        </div>
        <footer>Instituto Tecnológico Beltrán — Control de Acceso</footer>
    </main>
</body>

</html>