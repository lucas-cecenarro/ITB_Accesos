<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/session.php';
requireRole([ROLE_SEGURIDAD]);
require_once __DIR__ . '/../../../app/controllers/VisitasController.php';

// Evitar cache del navegador/proxy
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

try {
    $ctrl = new VisitasController();
    $rows = $ctrl->listarVisitasEnCurso();

    if (!$rows) {
        echo '<tr><td colspan="6" style="color:#94a3b8">Sin visitas en curso</td></tr>';
        exit;
    }

    foreach ($rows as $v) {
        $acc   = (int)$v['Acceso_ID'];
        $nom   = htmlspecialchars($v['Nombre']  ?? '', ENT_QUOTES, 'UTF-8');
        $ape   = htmlspecialchars($v['Apellido']?? '', ENT_QUOTES, 'UTF-8');
        $doc   = htmlspecialchars((string)$v['Num_Documento'] ?? '', ENT_QUOTES, 'UTF-8');
        $tdId  = (int)($v['TipoDocId'] ?? 1);
        $tdTxt = htmlspecialchars($v['TipoDocNombre'] ?? '—', ENT_QUOTES, 'UTF-8');
        $fhIn  = htmlspecialchars($v['FechaHora_Entrada'] ?? '—', ENT_QUOTES, 'UTF-8');
        $mot   = htmlspecialchars($v['Motivo'] ?? '—', ENT_QUOTES, 'UTF-8');

        echo <<<HTML
<tr
  data-acceso="{$acc}"
  data-nombre="{$nom}"
  data-apellido="{$ape}"
  data-dni="{$doc}"
  data-tipodocid="{$tdId}"
>
  <td>{$nom} {$ape}</td>
  <td>{$doc}</td>
  <td>{$tdTxt}</td>
  <td>{$fhIn}</td>
  <td>{$mot}</td>
  <td class="actions">
    <button class="btn btn-auto" title="Autocompletar formulario">Autocompletar</button>
    <button class="btn btn-egreso success" title="Registrar egreso rápido">Egreso rápido</button>
  </td>
</tr>
HTML;
    }
} catch (Throwable $e) {
    // En caso de error, devolvemos una fila con el mensaje (para no romper la tabla)
    $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    echo '<tr><td colspan="6" style="color:#fde68a">Error al listar: '.$msg.'</td></tr>';
}
