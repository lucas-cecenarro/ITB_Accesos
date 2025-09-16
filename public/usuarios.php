<?php
require_once __DIR__ . '/../app/session.php';
requireRole([ROLE_SUPERUSUARIO]);
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/models/usuario.php';
$pdo = DB::conn();

$usrNombre   = $_SESSION['nombre']   ?? '';
$usrApellido = $_SESSION['apellido'] ?? '';
$usrRolId    = (int)($_SESSION['rol_id'] ?? 0);
$usrRolTxt   = $_SESSION['rol']      ?? '';

$model    = new UsuarioBusqueda();                 
$filtros  = UsuarioBusqueda::leerFiltros($_GET);  
$rows     = $model->buscarAccesos($filtros);      
$operadores = $model->operadoresActivos();        
?>


<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Movimientos de Usuarios</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.2rem;
        }

        h1 {
            color: #e5e7eb;
            margin: 0 0 1rem;
        }

        .filters {
            display: flex;
            gap: .8rem;
            align-items: center;
            flex-wrap: wrap;
            margin: 1rem 0;
        }

        .filters input,
        .filters select {
            background: #111827;
            color: #e5e7eb;
            border: 1px solid #374151;
            border-radius: .5rem;
            padding: .6rem .8rem;
        }

        .btn {
            background: #22c55e;
            color: #052e16;
            border: none;
            border-radius: .6rem;
            padding: .6rem 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn.sec {
            background: #374151;
            color: #e5e7eb;
        }

        /* --- TABLA --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: #1e293b;
            /* un fondo uniforme */
            border-radius: .6rem;
            overflow: hidden;
        }

        thead {
            background: #111827;
        }

        thead th {
            color: #f1f5f9;
            font-weight: 600;
            padding: 0.8rem;
            text-align: left;
            border-bottom: 2px solid #374151;
        }

        tbody tr {
            border-bottom: 1px solid #374151;
        }

        tbody tr:nth-child(even) {
            background: #0f172a;
            /* alternancia */
        }

        tbody td {
            padding: 0.8rem;
            color: #e5e7eb;
        }

        tbody tr:hover {
            background: #1d4ed8;
            /* azul de hover */

        }
    </style>
</head>

<body>
    <header>
        <div class="navbar">
            <h1>Panel de Control - Busqueda Usuarios</h1>
        </div>
        <div class="user-navbar">
            <span><?= htmlspecialchars("$usrNombre $usrApellido", ENT_QUOTES, 'UTF-8') ?></span>
            <span class="badge-navbar">
                <?= htmlspecialchars($usrRolTxt ?: ($usrRolId === 1 ? 'superusuario' : 'seguridad'), ENT_QUOTES, 'UTF-8') ?></span>
            <a href="dashboard.php" class="pill" style="padding:6px 10px">Regresar</a>
            <a class="pill" href="logout.php" style="padding:6px 10px">Cerrar sesión</a>
        </div>
    </header>
    <main class="container">
        <h1>Movimientos de Usuarios</h1>

        <?php
        // Helper de escape (evita warnings y sanitiza)
        function e($v)
        {
            return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        }

        // Normalizo variables para que nunca estén indefinidas
        $nombre      = $nombre      ?? '';
        $apellido    = $apellido    ?? '';
        $dni         = $dni         ?? '';
        $desdeStr    = $desdeStr    ?? ''; // lo que venga por GET
        $hastaStr    = $hastaStr    ?? '';
        $operadorId  = $operadorId  ?? '';

        // Si venían en dd/mm/aaaa, los convierto a yyyy-mm-dd para mostrarlos en <input type="date">
        $desdeVal = (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $desdeStr))
            ? DateTime::createFromFormat('d/m/Y', $desdeStr)->format('Y-m-d')
            : $desdeStr;
        $hastaVal = (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $hastaStr))
            ? DateTime::createFromFormat('d/m/Y', $hastaStr)->format('Y-m-d')
            : $hastaStr;

        // Para el <select>
        $operadorIdStr = (string)$operadorId;
        ?>

        <form class="filters" method="get" action="usuarios.php" autocomplete="off">
            <!-- Orden: Nombre - Apellido - DNI - Desde - Hasta - Operador -->

            <!-- Solo letras y espacios (con acentos/ñ). Máx 60 -->
            <input
                type="text"
                name="nombre"
                placeholder="Nombre"
                value="<?= e($nombre ?? '') ?>"
                pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]{0,60}"
                title="Solo letras y espacios"
                inputmode="text"
                maxlength="60">

            <input
                type="text"
                name="apellido"
                placeholder="Apellido"
                value="<?= e($apellido ?? '') ?>"
                pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]{0,60}"
                title="Solo letras y espacios"
                inputmode="text"
                maxlength="60">

            <!-- Solo números. Máx 12. Filtra al tipear -->
            <input
                type="number"
                name="dni"
                placeholder="DNI"
                value="<?= e($dni ?? '') ?>"
                pattern="\d{1,12}"
                title="Solo números (hasta 12 dígitos)"
                inputmode="numeric"
                maxlength="12"
                oninput="this.value=this.value.replace(/\D/g,'')">

            <!-- Calendario -->
            <input
                type="date"
                name="desde"
                value="<?= e($desdeVal) ?>">

            <input
                type="date"
                name="hasta"
                value="<?= e($hastaVal) ?>">

            <select name="operador">
                <option value="" <?= $operadorIdStr === '' ? 'selected' : '' ?>>Operador (todos)</option>
                <?php foreach ($operadores as $op):
                    $id  = (string)(int)$op['operador_id'];
                    $lbl = e(($op['nombre'] ?? '') . ' ' . ($op['apellido'] ?? ''));
                    $sel = ($operadorIdStr === $id) ? 'selected' : '';
                ?>
                    <option value="<?= e($id) ?>" <?= $sel ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>

            <button class="btn" type="submit">Buscar</button>
            <a class="btn sec" href="usuarios.php">Limpiar</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>DNI</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Operador</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="7">Sin resultados para los filtros aplicados.</td>
                    </tr>
                    <?php else: foreach ($rows as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['nombre']) ?></td>
                            <td><?= htmlspecialchars($r['apellido']) ?></td>
                            <td><?= htmlspecialchars($r['nro_documento']) ?></td>
                            <td><?= htmlspecialchars($r['tipo'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['fecha']) ?></td>
                            <td><?= htmlspecialchars($r['hora']) ?></td>
                            <td><?= htmlspecialchars($r['operador'] ?? '') ?></td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </main>
</body>

</html>