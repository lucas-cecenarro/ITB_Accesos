<?php
// public/reportes/accesos_pdf.php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/controllers/ReportesController.php';
require_once __DIR__ . '/../../app/lib/fpdf/fpdf.php';

$pdo  = DB::conn();
$ctrl = new ReportesController($pdo);
$ctrl->assertSuperusuario();

$f   = $ctrl->filtros($_GET + $_POST);
$err = $ctrl->validar($f);
if ($err) { http_response_code(400); echo implode(" | ", $err); exit; }

$stmt = $ctrl->exportCursor($f);

// Contaremos filas para el log
$rows = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = $r;
}
$cnt = count($rows);

// (Opcional) Log antes de enviar el PDF, por si el cliente corta la descarga
$ctrl->logReporte($f, 'PDF', $cnt, 'ok', null);

// PDF en horizontal A4
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,8, utf8_decode('ITB - Reporte de Accesos'),0,1,'L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6, utf8_decode('Rango: '.$f['desde'].' a '.$f['hasta'].' | Tipo: '.$f['tipo'].' | Categoría: '.$f['rol_id']),0,1,'L');
$pdf->Ln(2);

// Encabezados
$pdf->SetFont('Arial','B',10);
$headers = ['Fecha Hora','Tipo','DNI','Nombre','Apellido','Rol','Estado'];
$widths  = [45,25,30,40,40,35,25];
for ($i=0; $i<count($headers); $i++) {
    $pdf->Cell($widths[$i],8,$headers[$i],1,0,'C');
}
$pdf->Ln();

// Filas
$pdf->SetFont('Arial','',9);
foreach ($rows as $row) {
    // Compatibilidad: si viene 'fecha' y 'hora' los concatenamos; si viene 'fecha_hora', lo usamos.
    $fechaHora = trim(($row['fecha'] ?? '').' '.($row['hora'] ?? ''));
    if ($fechaHora === '' && isset($row['fecha_hora'])) {
        $fechaHora = $row['fecha_hora'];
    }

    // Estado ya viene como texto desde estado_acceso
    $estadoTxt = (string)($row['estado'] ?? '');

    $vals = [
        $fechaHora,
        (string)($row['tipo']     ?? ''),
        (string)($row['dni']      ?? ''),
        (string)($row['nombre']   ?? ''),
        (string)($row['apellido'] ?? ''),
        (string)($row['rol']      ?? ''),
        $estadoTxt,
    ];

    // FPDF usa ISO-8859-1: convertimos strings
    foreach ($vals as $i => $v) {
        if (is_string($v)) $vals[$i] = utf8_decode($v);
    }

    for ($i=0; $i<count($vals); $i++) {
        $pdf->Cell($widths[$i],7,$vals[$i],1,0,'L');
    }
    $pdf->Ln();
}

// Descargar
$filename = 'reporte_accesos_'.date('Ymd_His').'.pdf';
$pdf->Output('D', $filename);
