<?php

declare(strict_types=1);
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/controllers/ReportesController.php';
require_once __DIR__ . '/../app/session.php';
requireRole([ROLE_SUPERUSUARIO]);

$usrNombre   = $_SESSION['nombre']   ?? '';
$usrApellido = $_SESSION['apellido'] ?? '';
$usrRolId    = (int)($_SESSION['rol_id'] ?? 0);
$usrRolTxt   = $_SESSION['rol']      ?? '';

$pdo  = DB::conn();
$ctrl = new ReportesController($pdo);
$ctrl->assertSuperusuario();

$ctrl = new ReportesController($pdo);
$ctrl->assertSuperusuario();

$f = $ctrl->filtros($_GET);
$errores = $ctrl->validar($f);

$roles = $ctrl->roles();
$rows = $resumen = [];
$total = 0;

if (!$errores) {
    $total   = $ctrl->contar($f);
    $rows    = $ctrl->listar($f);
    $resumen = $ctrl->resumen($f);
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reportes de Accesos</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
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
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .2)
        }

        /* ===== FILTROS ===== */
        .filters {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 12px
        }

        .field {
            grid-column: span 3;
            display: flex;
            flex-direction: column;
            min-width: 0
        }

        .span-2 {
            grid-column: span 2
        }

        .span-3 {
            grid-column: span 3
        }

        .span-4 {
            grid-column: span 4
        }

        .field label {
            font-size: .9rem;
            color: var(--text-dim);
            margin: 0 0 6px 2px
        }

        .control {
            width: 100%;
            height: 44px;
            padding: 0 12px;
            border-radius: 10px;
            border: 1px solid var(--muted);
            background: #0f172a;
            color: var(--text);
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .control:focus {
            border-color: #64748b;
            box-shadow: 0 0 0 3px rgba(100, 116, 139, .25)
        }

        /* uniformar datetime-local */
        input[type="datetime-local"].control {
            padding-right: 8px
        }

        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: .85
        }

        /* botones */
        .btnbar {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 12px;
            margin-top: 6px
        }

        .btn {
            grid-column: span 12;
            height: 46px;
            border-radius: 10px;
            border: 1px solid var(--muted);
            background: #0f172a;
            color: var(--text);
            cursor: pointer
        }

        .btn:hover {
            background: #0b1326
        }

        .btn:active {
            transform: scale(.99)
        }

        /* tabla/resumen */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px
        }

        .table th,
        .table td {
            padding: 10px;
            border-bottom: 1px solid var(--border);
            text-align: left
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            background: #1f2937;
            margin-right: 6px
        }

        .alert {
            background: #1f2937;
            border: 1px solid #374151;
            padding: 12px;
            border-radius: 10px;
            margin: 12px 0
        }

        .pager {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 10px
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .field {
                grid-column: span 6
            }

            .btn {
                grid-column: span 12
            }
        }

        @media (max-width: 640px) {
            .field {
                grid-column: span 12
            }
        }
    </style>

</head>

<body>
    <header>
        <div class="navbar">
            <h1>Panel de Control - Reportes</h1>
        </div>
        <div class="user-navbar">
            <span><?= htmlspecialchars("$usrNombre $usrApellido", ENT_QUOTES, 'UTF-8') ?></span>
            <span class="badge-navbar"><?= htmlspecialchars($usrRolTxt ?: ($usrRolId === 1 ? 'superusuario' : 'seguridad'), ENT_QUOTES, 'UTF-8') ?></span>
            <a href="dashboard.php" class="pill" style="padding:6px 10px">Regresar</a>
            <a class="pill" href="logout.php" style="padding:6px 10px">Cerrar sesión</a>
        </div>
    </header>
    <div class="container">
        <h2>Reportes de Accesos</h2>

        <div class="card">
            <form method="get" action="">
                <div class="filters">
                    <div class="field span-3">
                        <label>Desde</label>
                        <input class="control" type="datetime-local" name="desde"
                            value="<?= htmlspecialchars(str_replace(' ', 'T', substr($f['desde'], 0, 16))) ?>">
                    </div>

                    <div class="field span-3">
                        <label>Hasta</label>
                        <input class="control" type="datetime-local" name="hasta"
                            value="<?= htmlspecialchars(str_replace(' ', 'T', substr($f['hasta'], 0, 16))) ?>">
                    </div>

                    <div class="field span-2">
                        <label>Tipo</label>
                        <select class="control" name="tipo">
                            <?php foreach (['Ambos', 'INGRESO', 'EGRESO'] as $t): ?>
                                <option value="<?= $t ?>" <?= $f['tipo'] == strtoupper($t) ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field span-2">
                        <label>Categoria</label>
                        <select class="control" name="rol_id">
                            <option value="0" <?= $f['rol_id'] == 0 ? 'selected' : '' ?>>Todos</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['rol_id'] ?>" <?= $f['rol_id'] == (int)$r['rol_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field span-2">
                        <label>DNI</label>
                        <input class="control" type=number name="dni" value="<?= htmlspecialchars($f['dni']) ?>">
                    </div>

                    <div class="field span-3">
                        <label>Nombre</label>
                        <input class="control" type=text name="nombre" value="<?= htmlspecialchars($f['nombre']) ?>">
                    </div>

                    <div class="field span-3">
                        <label>Apellido</label>
                        <input class="control" type=text name="apellido" value="<?= htmlspecialchars($f['apellido']) ?>">
                    </div>
                </div>

                <div class="btnbar">
                    <button class="btn" type="submit">Previsualizar</button>
                    <button class="btn" type="submit" formaction="reportes/accesos_csv.php">Exportar CSV</button>
                    <button class="btn" type="submit" formaction="reportes/accesos_pdf.php">Exportar PDF</button>
                </div>
            </form>
        </div>

        <?php if ($errores): ?>
            <div class="alert">
                <?php foreach ($errores as $e) echo "<div>• " . htmlspecialchars($e) . "</div>"; ?>
            </div>
        <?php else: ?>
            <div class="card" style="margin-top:14px">
                <div>
                    <span class="badge">Total: <?= $resumen['total'] ?? 0 ?></span>
                    <span class="badge">Ingresos: <?= $resumen['in'] ?? $resumen['ingresos'] ?? 0 ?></span>
                    <span class="badge">Egresos: <?= $resumen['egresos'] ?? 0 ?></span>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha Hora</th>
                            <th>Tipo</th>
                            <th>DNI</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars((($r['fecha'] ?? '') . ' ' . ($r['hora'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($r['tipo']     ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($r['dni']      ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($r['nombre']   ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($r['apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($r['categoria'] ?? 'Invitado'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($r['estado']   ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php
                $pages = max(1, (int)ceil(($total ?: 0) / 100));
                $page  = (int)$f['page'];
                ?>
                <div class="pager">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($f, ['page' => $page - 1])) ?>"><button>&laquo; Anterior</button></a>
                    <?php endif; ?>
                    <div class="badge">Página <?= $page ?> / <?= $pages ?></div>
                    <?php if ($page < $pages): ?>
                        <a href="?<?= http_build_query(array_merge($f, ['page' => $page + 1])) ?>"><button>Siguiente &raquo;</button></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>