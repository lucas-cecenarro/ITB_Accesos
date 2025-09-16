<?php
// public/reportes/accesos_csv.php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/controllers/ReportesController.php';

$pdo  = DB::conn();
$ctrl = new ReportesController($pdo);
$ctrl->assertSuperusuario();

$f   = $ctrl->filtros($_GET + $_POST);
$err = $ctrl->validar($f);
if ($err) {
  header('Content-Type: text/plain; charset=UTF-8');
  http_response_code(400);
  echo implode(" | ", $err);
  exit;
}

$stmt      = $ctrl->exportCursor($f);
$filename  = 'reporte_accesos_'.date('Ymd_His').'.csv';
$delimiter = ';'; // para Excel (config regional con coma decimal)

// Evitar cualquier salida previa
if (ob_get_level()) { ob_end_clean(); }

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');
// BOM UTF-8 para que Excel respete tildes/ñ
fwrite($out, "\xEF\xBB\xBF");

// Encabezados
fputcsv($out, ['fecha_hora','tipo','dni','nombre','apellido','categoria','estado'], $delimiter);

// Filas
$cnt = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Compatibilidad: si vienen 'fecha' y 'hora', los uno; si existiera 'fecha_hora', lo uso.
    $fechaHora = trim(($row['fecha'] ?? '').' '.($row['hora'] ?? ''));
    if ($fechaHora === '' && isset($row['fecha_hora'])) {
        $fechaHora = (string)$row['fecha_hora'];
    }

    // Estado textual (viene de estado_acceso.Nombre en el controller)
    $estadoTxt = (string)($row['estado'] ?? '');

    fputcsv(
        $out,
        [
            $fechaHora,
            (string)($row['tipo']     ?? ''),
            (string)($row['dni']      ?? ''),
            (string)($row['nombre']   ?? ''),
            (string)($row['apellido'] ?? ''),
            (string)($row['categoria'] ?? 'Invitado'),
            $estadoTxt
        ],
        $delimiter
    );
    $cnt++;
}
fclose($out);

// Log en DB
$ctrl->logReporte($f, 'CSV', $cnt, 'ok', null);
exit;
