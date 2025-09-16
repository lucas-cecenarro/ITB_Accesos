<?php
require_once __DIR__ . '/../app/session.php';
requireRole([ROLE_SEGURIDAD]);

require_once __DIR__ . '/../app/controllers/VisitasController.php';

$mensaje = $warning = $error = null;
$usrNombre   = $_SESSION['nombre']   ?? '';
$usrApellido = $_SESSION['apellido'] ?? '';
$usrRolId    = (int)($_SESSION['rol_id'] ?? 0);
$usrRolTxt   = $_SESSION['rol']      ?? '';

$ctrl  = new VisitasController();
try {
    $tipos = $ctrl->tiposDocumento();
} catch (Throwable $e) {
    $tipos = [];
    $error = "No se pudieron cargar los tipos de documento.";
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    [$mensaje, $warning, $error] = $ctrl->registrar($_POST);
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Registro manual (Visitas/Usuarios) — Seguridad</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --bg: #0f172a;
            --card: #111827;
            --muted: #94a3b8;
            --accent: #0ea5e9;
            --line: #1f2937;
            --ink: #e2e8f0;
            --ink-soft: #cbd5e1;
            --field: #0b1220;
            --field-line: #334155;
            --ok-bg: #052e1a;
            --ok-ink: #bbf7d0;
            --ok-line: #14532d;
            --warn-bg: #452103;
            --warn-ink: #fde68a;
            --warn-line: #b45309;
            --err-bg: #3b0a0a;
            --err-ink: #fecaca;
            --err-line: #7f1d1d;
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            font-family: system-ui, Segoe UI, Roboto, Arial;
            background: var(--bg);
            color: var(--ink);
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: #0b1220;
            border-bottom: 1px solid var(--line);
        }

        .navbar {
            display: flex;
            gap: 12px;
            align-items: center
        }

        .navbar h1 {
            margin: 0;
            font-size: 18px
        }

        .user-navbar {
            font-size: 14px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 10px
        }

        .btn-navbar {
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

        .badge-navbar {
            background: #1f2937;
            border: 1px solid #334155;
            color: #cbd5e1;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            text-transform: capitalize;
        }

        .pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid var(--field-line);
            background: var(--field);
            color: var(--ink-soft);
            font-size: 12px;
            text-decoration: none;
        }

        .container {
            max-width: 1000px;
            margin: 18px auto;
            padding: 0 16px
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
        }

        h2 {
            margin: 0 0 10px;
            font-size: 18px
        }

        .controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-start
        }

        .row {
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr 1fr
        }

        @media (max-width:720px) {
            .row {
                grid-template-columns: 1fr
            }
        }

        label {
            display: block;
            margin: 0 0 6px;
            font-size: 14px;
            font-weight: 600;
            color: var(--ink-soft);
            letter-spacing: .2px;
        }

        .field {
            display: block;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        select {
            width: 100%;
            background: var(--field);
            color: var(--ink);
            border: 1px solid var(--field-line);
            border-radius: 10px;
            padding: 10px;
            font-size: 14px;
        }

        .radios {
            display: flex;
            gap: 14px;
            flex-wrap: wrap
        }

        .radios label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            color: var(--ink-soft)
        }

        .btn,
        button {
            background: var(--field);
            color: var(--ink);
            border: 1px solid var(--field-line);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: transform .05s ease;
        }

        .btn:active,
        button:active {
            transform: scale(.98)
        }

        .primary {
            background: var(--accent);
            color: #031018;
            border-color: transparent
        }

        .full {
            width: 100%
        }

        .msg {
            margin-top: 12px;
            padding: 10px;
            border-radius: 8px
        }

        .ok {
            background: var(--ok-bg);
            color: var(--ok-ink);
            border: 1px solid var(--ok-line)
        }

        .warn {
            background: var(--warn-bg);
            color: var(--warn-ink);
            border: 1px solid var(--warn-line)
        }

        .err {
            background: var(--err-bg);
            color: var(--err-ink);
            border: 1px solid var(--err-line)
        }

        .mt8 {
            margin-top: 8px
        }

        .mt12 {
            margin-top: 12px
        }

        .mt16 {
            margin-top: 16px
        }

        textarea {
            width: 100%;
            background: var(--field);
            color: var(--ink);
            border: 1px solid var(--field-line);
            border-radius: 10px;
            padding: 10px;
            font-size: 14px;
            line-height: 1.4;
            resize: vertical;
            /* permitir redimensionar sólo en alto (o usa 'none') */
            min-height: 44px;
            /* altura base similar al input */
        }

        textarea::placeholder {
            color: var(--muted);
            opacity: .9;
        }

        /* Utilidad para que un campo ocupe las 2 columnas de .row */
        .full-span {
            grid-column: 1 / -1;
        }

        .success {
            background: #22c55e;
            /* verde */
            color: #06240f;
            border-color: transparent;
        }
    </style>
</head>



<body>
    <header>
        <div class="navbar">
            <h1>Panel de Control - Registro Visitas</h1>
        </div>
        <div class="user-navbar">
            <span><?= htmlspecialchars("$usrNombre $usrApellido", ENT_QUOTES, 'UTF-8') ?></span>
            <span class="badge-navbar"><?= htmlspecialchars($usrRolTxt ?: ($usrRolId === 1 ? 'superusuario' : 'seguridad'), ENT_QUOTES, 'UTF-8') ?></span>
            <a href="dashboard.php" class="pill" style="padding:6px 10px">Regresar</a>
            <a class="pill" href="logout.php" style="padding:6px 10px">Cerrar sesión</a>
        </div>
    </header>

    <main class="container">
        <section class="card">
            <h2>Registro manual — Seguridad</h2>

            <?php if ($mensaje): ?><div class="msg ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($warning): ?><div class="msg warn"><?= htmlspecialchars($warning, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="msg err"><?= htmlspecialchars($error,   ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

            <form method="post" autocomplete="off" class="mt12" id="frm-visitas">
                <div class="row">
                    <div class="field">
                        <label for="v-nombre">Nombre</label>
                        <input id="v-nombre" type="text" name="nombre" required>
                    </div>
                    <div class="field">
                        <label for="v-apellido">Apellido</label>
                        <input id="v-apellido" type="text" name="apellido" required>
                    </div>
                </div>

                <div class="row mt12">
                    <div class="field">
                        <label for="v-tipodoc">Tipo de documento</label>
                        <select id="v-tipodoc" name="tipodoc_id" required>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= (int)$t['tipodoc_id'] ?>" <?= ((int)$t['tipodoc_id'] === 1 ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="v-dni">Número</label>
                        <input id="v-dni" type="number" name="dni" required>
                    </div>
                </div>
                <div class="row mt12">
                    <div class="field full-span">
                        <label for="v-motivo">Motivo (opcional)</label>
                        <textarea id="v-motivo" name="motivo" rows="2" maxlength="200"
                            placeholder="Ej.: visita a dirección, proveedor, mantenimiento…"></textarea>
                    </div>
                </div>



                <div class="mt12">
                    <label>Acción</label>
                    <div class="radios">
                        <label><input type="radio" name="tipo" value="INGRESO" checked> Ingreso</label>
                        <label><input type="radio" name="tipo" value="EGRESO"> Egreso</label>
                    </div>
                </div>

                <div class="mt12">
                    <button type="button" id="btn-open" class="btn" disabled>Abrir molinete</button>
                    <span id="open-msg" class="pill" style="display:none; margin-left:8px"></span>
                </div>

                <button type="submit" class="primary full mt16" id="btn-submit">Registrar</button>
            </form>
        </section>
    </main>
</body>

<script>
    const API_MOL = '<?= htmlspecialchars(BASE_URL, ENT_QUOTES, "UTF-8") ?>/api/molinete_abrir.php';
</script>

<script>
    (function() {
        const $nombre = document.getElementById('v-nombre');
        const $apellido = document.getElementById('v-apellido');
        const $dni = document.getElementById('v-dni');
        const $btn = document.getElementById('btn-open');
        const $msg = document.getElementById('open-msg');

        function isValid() {
            return $nombre.value.trim() !== '' &&
                $apellido.value.trim() !== '' &&
                $dni.value.trim() !== '';
        }

        function refreshButton() {
            const ok = isValid();
            $btn.disabled = !ok;
            $btn.classList.toggle('success', ok);
        }

        [$nombre, $apellido, $dni].forEach(el => el.addEventListener('input', refreshButton));
        refreshButton();

        $btn.addEventListener('click', async () => {
            if ($btn.disabled) return;

            $btn.disabled = true;
            const oldTxt = $btn.textContent;
            $btn.textContent = 'Abriendo…';
            $msg.style.display = 'none';

            try {
                const r = await fetch(API_MOL, {
                    method: 'POST'
                });
                const txt = await r.text(); // <- primero texto
                let data = null;
                try {
                    data = JSON.parse(txt); // <- intentamos parsear
                } catch (e) {
                    throw new Error(`HTTP ${r.status} – respuesta no JSON: ${txt.slice(0,120)}`);
                }

                $msg.textContent = data.ok ?
                    `Molinete abierto (${data.open_for}s)` :
                    (data.error || 'No se pudo abrir');
                $msg.style.display = 'inline-block';

            } catch (e) {
                $msg.textContent = (e && e.message) ? e.message : 'Error de red';
                $msg.style.display = 'inline-block';
            } finally {
                $btn.textContent = oldTxt;
                refreshButton();
            }
        });

    })();
</script>

<script>
    document.getElementById('frm-visitas').addEventListener('submit', function() {
        const btn = document.getElementById('btn-submit');
        btn.disabled = true;
        btn.textContent = 'Registrando…';
    });
</script>

</html>