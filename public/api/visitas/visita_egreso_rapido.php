<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/session.php';
requireRole([ROLE_SEGURIDAD]);
require_once __DIR__ . '/../../../app/controllers/VisitasController.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $accesoId = (int)($body['acceso_id'] ?? 0);
    if ($accesoId <= 0) {
        throw new RuntimeException('acceso_id inválido');
    }

    // ID del operador desde la sesión (seguridad logueado)
    $operadorId = (int)($_SESSION['user_id'] ?? 0);
    if ($operadorId <= 0) {
        throw new RuntimeException('Sesión inválida');
    }

    $ctrl = new VisitasController();
    $res = $ctrl->egresoRapido($accesoId, $operadorId);
    echo json_encode($res);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
