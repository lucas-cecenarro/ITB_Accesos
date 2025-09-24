<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

function sendPasswordReset(string $toEmail, string $resetLink): bool
{
    // Armado común del mensaje
    $subject = 'Restablecer contraseña';
    $html = '<p>Para restablecer tu contraseña hacé clic en el siguiente enlace:</p>'
          . '<p><a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '">Restablecer contraseña</a></p>'
          . '<p>Si no solicitaste este cambio, ignorá este mensaje.</p>';
    $text = "Abrí este enlace para restablecer tu contraseña:\n{$resetLink}\n";

    // Si existe PHPMailer, usamos SMTP
    if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet   = 'UTF-8';
            $mail->isSMTP();

            if (APP_ENV === 'local') {
                // DEV: MailHog
                $mail->Host       = '127.0.0.1';
                $mail->Port       = 1025;
                $mail->SMTPAuth   = false;
                $mail->SMTPSecure = false;
            } else {
                // PROD: SMTP real (tomado de config.php)
                $mail->Host       = SMTP_HOST;
                $mail->Port       = SMTP_PORT;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                // 'tls' o 'ssl' según tu proveedor
                if (SMTP_SECURE) { $mail->SMTPSecure = SMTP_SECURE; }
            }

            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($toEmail);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body    = $html;
            $mail->AltBody = $text;

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            // Si falla, seguimos con plan B de logeo local
        }
    }

    // Plan B: log local (si no está PHPMailer o falló SMTP)
    $logDir = __DIR__ . '/../../var';
    if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
    $line = date('Y-m-d H:i:s') . " | TO: {$toEmail} | SUBJ: {$subject} | LINK: {$resetLink}\n";
    @file_put_contents($logDir . '/mail_fallback.log', $line, FILE_APPEND);
    return true;
}
