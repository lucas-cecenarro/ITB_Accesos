<?php
require_once __DIR__ . '/../app/session.php';
requireRole([ROLE_SEGURIDAD]);

require_once __DIR__ . '/../app/controllers/VisitasController.php';

$usrNombre   = $_SESSION['nombre']   ?? '';
$usrApellido = $_SESSION['apellido'] ?? '';
$usrRolId    = (int)($_SESSION['rol_id'] ?? 0);
$usrRolTxt   = $_SESSION['rol']      ?? '';

// Mensajes flash (si vienen de registrar.php)
$mensaje = $_SESSION['flash']['mensaje'] ?? null;
$warning = $_SESSION['flash']['warning'] ?? null;
$error   = $_SESSION['flash']['error']   ?? null;
unset($_SESSION['flash']); // consumirlos

$ctrl  = new VisitasController();
try {
    $tipos = $ctrl->tiposDocumento();
} catch (Throwable $e) {
    $tipos = [];
    $error = $error ?: "No se pudieron cargar los tipos de documento.";
}

// Listar visitas EN CURSO para el panel superior
$enCurso = [];
try {
    $enCurso = $ctrl->listarVisitasEnCurso();
} catch (Throwable $e) {
    // no romper la vista si falla la carga
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
        select,
        textarea {
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
            line-height: 1.4;
            resize: vertical;
            min-height: 44px
        }

        .success {
            background: #22c55e;
            color: #06240f;
            border-color: transparent;
        }

        /* tabla */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            border-bottom: 1px solid var(--line);
        }

        th {
            text-align: left;
            color: var(--ink-soft);
            font-weight: 600
        }

        .actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap
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
            <span class="pill" style="text-transform:capitalize">
                <?= htmlspecialchars($usrRolTxt ?: ($usrRolId === 1 ? 'superusuario' : 'seguridad'), ENT_QUOTES, 'UTF-8') ?>
            </span>
            <a href="dashboard.php" class="pill" style="padding:6px 10px">Regresar</a>
            <a class="pill" href="logout.php" style="padding:6px 10px">Cerrar sesión</a>
        </div>
    </header>

    <main class="container">

        <!-- === NUEVO: PANEL VISITAS EN CURSO === -->
        <section class="card">
            <h2>Visitas en curso</h2>
            <div class="mt8" style="overflow:auto">
                <table id="tabla-en-curso">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Tipo doc</th>
                            <th>Ingreso</th>
                            <th>Motivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$enCurso): ?>
                            <tr>
                                <td colspan="6" style="color:var(--muted)">Sin visitas en curso</td>
                            </tr>
                            <?php else: foreach ($enCurso as $v): ?>
                                <tr
                                    data-acceso="<?= (int)$v['Acceso_ID'] ?>"
                                    data-nombre="<?= htmlspecialchars($v['Nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-apellido="<?= htmlspecialchars($v['Apellido'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-dni="<?= htmlspecialchars((string)$v['Num_Documento'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-tipodocid="<?= (int)($v['TipoDocId'] ?? 1) ?>">
                                    <!-- Nombre -->
                                    <td><?= htmlspecialchars($v['Nombre'] . ' ' . $v['Apellido'], ENT_QUOTES, 'UTF-8') ?></td>

                                    <!-- Documento -->
                                    <td><?= htmlspecialchars((string)$v['Num_Documento'], ENT_QUOTES, 'UTF-8') ?></td>

                                    <!-- Tipo doc (texto) -->
                                    <td><?= htmlspecialchars($v['TipoDocNombre'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>

                                    <!-- Ingreso (fecha/hora de entrada) -->
                                    <td><?= htmlspecialchars($v['FechaHora_Entrada'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>

                                    <!-- Motivo -->
                                    <td><?= htmlspecialchars($v['Motivo'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>

                                    <!-- Acciones -->
                                    <td class="actions">
                                        <button class="btn btn-auto" title="Autocompletar formulario">Autocompletar</button>
                                        <button class="btn btn-egreso success" title="Registrar egreso rápido">Egreso rápido</button>
                                    </td>
                                </tr>

                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- === FORMULARIO ORIGINAL === -->
        <section class="card mt16">
            <h2>Registro manual — Seguridad</h2>

            <?php if ($mensaje): ?><div class="msg ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($warning): ?><div class="msg warn"><?= htmlspecialchars($warning, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="msg err"><?= htmlspecialchars($error,   ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

            <form method="post" action="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/api/visitas/registrar.php" autocomplete="off" class="mt12" id="frm-visitas">
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
                    <div class="field" style="grid-column:1 / -1">
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

    <script>
        const API_MOL = '<?= htmlspecialchars(BASE_URL, ENT_QUOTES, "UTF-8") ?>/api/molinete_abrir.php';
        const API_EGRESO_RAPIDO = '<?= htmlspecialchars(BASE_URL, ENT_QUOTES, "UTF-8") ?>/api/visitas/visita_egreso_rapido.php';
    </script>

    <script>
        // === Habilitar botón molinete (tu lógica original) ===
        (function() {
            const $nombre = document.getElementById('v-nombre');
            const $apellido = document.getElementById('v-apellido');
            const $dni = document.getElementById('v-dni');
            const $btn = document.getElementById('btn-open');
            const $msg = document.getElementById('open-msg');

            function isValid() {
                return $nombre.value.trim() !== '' && $apellido.value.trim() !== '' && $dni.value.trim() !== '';
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
                    const txt = await r.text();
                    let data = null;
                    try {
                        data = JSON.parse(txt);
                    } catch (e) {
                        throw new Error(`HTTP ${r.status} – respuesta no JSON: ${txt.slice(0,120)}`);
                    }
                    $msg.textContent = data.ok ? `Molinete abierto (${data.open_for}s)` : (data.error || 'No se pudo abrir');
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
        // === NUEVO: Autocompletar + Egreso rápido de la tabla ===
        document.addEventListener('click', async (ev) => {
            const tr = ev.target.closest('tr[data-acceso]');
            if (!tr) return;

            if (ev.target.closest('.btn-auto')) {
                document.getElementById('v-nombre').value = tr.dataset.nombre || '';
                document.getElementById('v-apellido').value = tr.dataset.apellido || '';
                document.getElementById('v-dni').value = tr.dataset.dni || '';

                // Seleccionar el tipo de documento (por ID)
                const selTipo = document.getElementById('v-tipodoc');
                if (selTipo) {
                    const val = String(tr.dataset.tipodocid || '');
                    // ¿Existe esa opción en el select?
                    const exists = Array.from(selTipo.options).some(o => o.value === val);
                    selTipo.value = exists ? val : ''; // o usa '1' si querés forzar DNI por defecto
                    selTipo.dispatchEvent(new Event('change'));
                }

                // marcar EGRESO para agilizar
                const egresoRadio = document.querySelector('input[name="tipo"][value="EGRESO"]');
                if (egresoRadio) egresoRadio.checked = true;

                ['v-nombre', 'v-apellido', 'v-dni'].forEach(id =>
                    document.getElementById(id).dispatchEvent(new Event('input'))
                );
            }


            if (ev.target.closest('.btn-egreso')) {
                const btn = ev.target.closest('.btn-egreso');
                const accesoId = parseInt(tr.dataset.acceso, 10);
                if (!accesoId) return;

                btn.disabled = true;
                const old = btn.textContent;
                btn.textContent = 'Procesando…';

                try {
                    const r = await fetch(API_EGRESO_RAPIDO, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            acceso_id: accesoId
                        })
                    });
                    const data = await r.json();
                    if (data && data.ok) {
                        tr.remove();
                    } else {
                        alert(data && data.error ? data.error : 'No se pudo completar el egreso');
                    }
                } catch (e) {
                    alert('Error de red');
                } finally {
                    btn.disabled = false;
                    btn.textContent = old;
                }
            }
        });

        // UX: deshabilitar botón submit al enviar (tu lógica original)
        document.getElementById('frm-visitas').addEventListener('submit', function() {
            const btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.textContent = 'Registrando…';
        });
    </script>

    <script>
        // Auto-refresh del panel "Visitas en curso"
        const TBL_BODY = document.querySelector('#tabla-en-curso tbody');
        const REFRESH_MS = 20000; // 20s (ajústalo a gusto)

        async function refreshEnCurso(showSpinner = false) {
            if (!TBL_BODY) return;
            const prev = TBL_BODY.innerHTML;
            if (showSpinner) {
                TBL_BODY.innerHTML = '<tr><td colspan="6" style="color:#94a3b8">Actualizando…</td></tr>';
            }
            try {
                const r = await fetch('api/visitas/visitas_en_curso.php', {
                    cache: 'no-store'
                });
                const html = await r.text();
                TBL_BODY.innerHTML = html;
            } catch (e) {
                // si falla, dejamos lo anterior para no “vaciar” la tabla
                TBL_BODY.innerHTML = prev;
                console.warn('No se pudo refrescar visitas en curso:', e);
            }
        }

        // 1) Primer refresh al cargar
        document.addEventListener('DOMContentLoaded', () => refreshEnCurso(true));

        // 2) Polling cada X segundos
        setInterval(refreshEnCurso, REFRESH_MS);

        // 3) Refrescar inmediatamente tras un "Egreso rápido"
        document.addEventListener('click', (ev) => {
            if (ev.target.closest('.btn-egreso')) {
                // dejamos que el código de egreso quite la fila,
                // pero de todos modos refrescamos para traer cambios recientes
                setTimeout(refreshEnCurso, 400);
            }
        });
    </script>

</body>

</html>