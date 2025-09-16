<?php
require_once __DIR__ . '/../app/session.php';
requireRole([ROLE_SEGURIDAD]);

$nombre   = $_SESSION['nombre']   ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$rolId    = (int)($_SESSION['rol_id'] ?? 0);
$rolTxt   = $_SESSION['rol']      ?? '';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Kiosco — Escáner QR | ITB</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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
            padding: 0 16px;
        }

        .grid {
            display: grid;
            gap: 16px;
            grid-template-columns: 1fr 360px;
        }

        @media (max-width: 980px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--card);
            border: 1px solid #1f2937;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
        }

        .controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin: 12px 0 6px;
        }

        select,
        button {
            background: #0b1220;
            color: #e2e8f0;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 8px 10px;
            cursor: pointer;
            font-size: 14px;
        }

        button.primary {
            background: var(--accent);
            color: #031018;
            border-color: transparent;
        }

        button:disabled {
            opacity: .55;
            cursor: not-allowed
        }

        .status {
            font-size: 13px;
            color: var(--muted);
            margin-top: 8px
        }

        .pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid #334155;
            background: #0b1220;
            color: #cbd5e1;
            font-size: 12px;
        }

        .result {
            font-size: 14px;
            line-height: 1.45
        }

        .ok {
            background: #052e1a;
            color: #bbf7d0;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #14532d;
        }

        .err {
            background: #3b0a0a;
            color: #fecaca;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #7f1d1d;
        }

        .hint {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 6px
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
    </style>
    <!-- Librería de lectura QR -->
    <script src="https://unpkg.com/html5-qrcode" defer></script>
</head>

<body>
    <header>
        <div class="navbar">
            <h1>Panel de Control - Registro con Escaner QR</h1>
        </div>
        <div class="user-navbar">
            <span><?= htmlspecialchars("$nombre $apellido", ENT_QUOTES, 'UTF-8') ?></span>
            <span class="badge-navbar"><?= htmlspecialchars($rolTxt ?: ($rolId === 1 ? 'superusuario' : 'seguridad'), ENT_QUOTES, 'UTF-8') ?></span>
            <a href="dashboard.php" class="pill" style="padding:6px 10px">Regresar</a>
            <a class="pill" href="logout.php" style="padding:6px 10px">Cerrar sesión</a>
        </div>
    </header>

    <main class="container">
        <div class="grid">
            <!-- Escáner -->
            <section class="card">
                <h2 style="margin:0 0 10px">Cámara / Lector</h2>

                <div class="controls">
                    <label for="cameraSel" style="font-size:13px;color:#94a3b8">Cámara:</label>
                    <select id="cameraSel"></select>

                    <button id="btnStart" class="primary">Iniciar</button>
                    <button id="btnStop">Detener</button>
                    <button id="btnFlash" title="Activar/Desactivar flash">Flash</button>
                </div>

                <div id="reader" style="width:100%;max-width:680px;min-height:300px"></div>
                <div id="scanStatus" class="status">Estado: inactivo</div>
                <div class="hint">Sugerencia: si tu dispositivo tiene varias cámaras, probá seleccionar la trasera (environment).</div>
            </section>

            <!-- Resultado / registro -->
            <aside class="card">
                <h2 style="margin:0 0 10px">Resultado</h2>
                <div id="toast" class="mt8"></div>
                <div class="mt12">
                    <div class="result" id="lastPayload"><em>Esperando lectura…</em></div>
                    <div class="hint mt8">
                        Al leer un QR se enviará automáticamente a <code>api/qr/registrar.php</code>.
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <script>
        // Espera a que cargue html5-qrcode script
        document.addEventListener('DOMContentLoaded', async () => {
            const readerEl = document.getElementById('reader');
            const cameraSel = document.getElementById('cameraSel');
            const btnStart = document.getElementById('btnStart');
            const btnStop = document.getElementById('btnStop');
            const btnFlash = document.getElementById('btnFlash');
            const scanStatus = document.getElementById('scanStatus');
            const toast = document.getElementById('toast');
            const lastPayload = document.getElementById('lastPayload');

            let html5Qrcode = null;
            let currentCamId = null;
            let scanning = false;
            let flashlightOn = false;
            let coolDown = false;

            // Beep simple (sin archivos)
            function beep(ok = true) {
                try {
                    const ctx = new(window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = ok ? 'sine' : 'square';
                    osc.frequency.value = ok ? 880 : 220; // Hz
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    gain.gain.setValueAtTime(0.001, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + 0.02);
                    osc.start();
                    setTimeout(() => {
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.1);
                        osc.stop(ctx.currentTime + 0.12);
                    }, 120);
                } catch (e) {}
            }

            function showToast(msg, ok = true) {
                toast.className = ok ? 'ok' : 'err';
                toast.textContent = msg;
                if (ok) beep(true);
                else beep(false);
            }

            function setStatus(txt) {
                scanStatus.textContent = 'Estado: ' + txt;
            }

            // Listar cámaras
            async function listCameras() {
                try {
                    const devices = await Html5Qrcode.getCameras();
                    cameraSel.innerHTML = '';
                    if (!devices || devices.length === 0) {
                        cameraSel.innerHTML = '<option value="">No hay cámaras detectadas</option>';
                        btnStart.disabled = true;
                        return;
                    }
                    devices.forEach((d, i) => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.label || `Cámara ${i+1}`;
                        cameraSel.appendChild(opt);
                    });
                    // Heurística: preferir "back"/"environment" si aparece
                    const envIdx = devices.findIndex(d => /back|environment/i.test(d.label));
                    cameraSel.value = envIdx >= 0 ? devices[envIdx].id : devices[0].id;
                    currentCamId = cameraSel.value;
                } catch (err) {
                    showToast('No se pudieron listar cámaras. Revisá permisos.', false);
                }
            }

            cameraSel.addEventListener('change', () => {
                currentCamId = cameraSel.value || null;
                if (scanning) {
                    stopScan().then(startScan);
                }
            });

            async function startScan() {
                if (!currentCamId) {
                    await listCameras();
                }
                if (!currentCamId) return;

                if (!html5Qrcode) {
                    html5Qrcode = new Html5Qrcode(readerEl.id, {
                        verbose: false
                    });
                }

                const config = {
                    fps: 12,
                    qrbox: {
                        width: 300,
                        height: 300
                    },
                    aspectRatio: 1.7778,
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true
                    }
                };

                try {
                    await html5Qrcode.start({
                            deviceId: {
                                exact: currentCamId
                            }
                        },
                        config,
                        onScanSuccess,
                        onScanError
                    );
                    scanning = true;
                    setStatus('escaneando…');
                    btnStart.disabled = true;
                    btnStop.disabled = false;
                    // Intentar habilitar control de flash si está soportado
                    try {
                        const track = html5Qrcode.getState() === Html5QrcodeScannerState.SCANNING ?
                            html5Qrcode.getRunningTrack() :
                            null;
                        btnFlash.disabled = !(track && track.getCapabilities && track.getCapabilities().torch);
                    } catch {
                        btnFlash.disabled = true;
                    }
                } catch (err) {
                    showToast('No se pudo iniciar la cámara. Revisá permisos.', false);
                    setStatus('error');
                }
            }

            async function stopScan() {
                if (html5Qrcode && scanning) {
                    await html5Qrcode.stop();
                }
                scanning = false;
                setStatus('inactivo');
                btnStart.disabled = false;
                btnStop.disabled = true;
            }

            btnStart.addEventListener('click', startScan);
            btnStop.addEventListener('click', stopScan);

            // Flash toggle
            btnFlash.addEventListener('click', async () => {
                try {
                    if (!html5Qrcode) return;
                    const track = html5Qrcode.getRunningTrack && html5Qrcode.getRunningTrack();
                    if (!track) return;
                    const caps = track.getCapabilities && track.getCapabilities();
                    if (!caps || !caps.torch) return;
                    flashlightOn = !flashlightOn;
                    await track.applyConstraints({
                        advanced: [{
                            torch: flashlightOn
                        }]
                    });
                    btnFlash.textContent = flashlightOn ? 'Flash (ON)' : 'Flash';
                } catch (e) {}
            });

            // Manejo de lecturas
            function onScanError(err) {
                /* ignoramos ruido */ }

            async function onScanSuccess(decodedText, decodedResult) {
                if (coolDown) return; // evita spam de lecturas
                coolDown = true;
                setTimeout(() => coolDown = false, 2500);

                lastPayload.textContent = decodedText;

                // Enviar al backend
                try {
                    const res = await fetch('api/qr/registrar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            token: decodedText
                        })
                    });
                    const data = await res.json().catch(() => null);

                    if (res.ok && data && (data.ok === true)) {
                        // Respuesta esperada: { ok:true, message, registro:{nombre,apellido,tipo,fecha,hora} }
                        showToast(data.message || 'Registro OK', true);
                        const r = data.registro || {};
                        lastPayload.innerHTML = `
              <b>Usuario:</b> ${escapeHtml(r.nombre ?? '')} ${escapeHtml(r.apellido ?? '')}<br>
              <b>Tipo:</b> ${escapeHtml(r.tipo ?? '')}<br>
              <b>Fecha/Hora:</b> ${escapeHtml(r.fecha ?? '')} ${escapeHtml(r.hora ?? '')}
            `;
                    } else {
                        const msg = (data && data.message) ? data.message : 'Error al registrar';
                        showToast(msg, false);
                    }
                } catch (e) {
                    showToast('Fallo de red/servidor', false);
                }
            }

            function escapeHtml(s) {
                return (s ?? '').toString()
                    .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;').replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            // Inicial
            await listCameras();
            setStatus('inactivo');
            btnStop.disabled = true;
        });
    </script>
</body>

</html>