<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../app/session.php';
requireRole([ROLE_SEGURIDAD]);

require_once __DIR__ . '/../../../app/controllers/VisitasController.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  header('Location: ' . (BASE_URL . '/../../visita.php'));
  exit;
}

$ctrl = new VisitasController();

// Ejecuta la lógica existente del controller
[$mensaje, $warning, $error] = $ctrl->registrar($_POST);

// Guardamos mensajes “flash” en sesión para mostrarlos al volver
$_SESSION['flash'] = [
  'mensaje' => $mensaje,
  'warning' => $warning,
  'error'   => $error,
];

// Redirigimos a la pantalla principal
header('Location: ' . (BASE_URL . '/../../visita.php'));
exit;
