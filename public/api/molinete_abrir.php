<?php
declare(strict_types=1);

// Buffer para capturar cualquier warning/notice/espacio antes del JSON
ob_start();

require_once __DIR__ . '/../../app/session.php';
require_once __DIR__ . '/../../app/lib/fpdf/molinete.php';

// No mostrar errores al output (se loguean abajo)
@ini_set('display_errors', '0');

$rid = (int)($_SESSION['rol_id'] ?? 0);
if (!in_array($rid, [1, 2], true)) {
    http_response_code(403);
    // Limpio cualquier ruido y respondo JSON
    ob_get_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Sin permiso.']);
    exit;
}

try {
    $res = MolineteSim::abrir(5); // segundos abierto
    $noise = ob_get_clean();      // recupero ruido (si hubo)

    header('Content-Type: application/json; charset=utf-8');
    if ($noise !== '') {
        // Log del ruido para depurar
        $logDir = __DIR__ . '/../../app/tmp';
        if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
        @file_put_contents($logDir . '/molinete_api.log',
            '['.date('Y-m-d H:i:s')."] ruido previo:\n".$noise."\n\n",
            FILE_APPEND
        );
    }

    echo json_encode($res, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    ob_get_clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error de servidor']);
}
