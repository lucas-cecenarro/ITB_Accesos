<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$m = new PHPMailer(true);
$m->isSMTP();
$m->Host = '127.0.0.1';
$m->Port = 1025;
$m->SMTPAuth = false;
$m->CharSet = 'UTF-8';

$m->setFrom('no-reply@itb.local', 'Prueba ITB');
$m->addAddress('seguridad@itb.com');
$m->Subject = 'Test MailHog';
$m->Body = 'Hola! Esto es una prueba directa a MailHog.';
$m->AltBody = 'Hola! Esto es una prueba directa a MailHog.';

$m->send();
echo 'OK';
