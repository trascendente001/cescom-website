<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/assets/vendor/phpmailer/Exception.php';
require_once __DIR__ . '/assets/vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/assets/vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Honeypot: bots fill hidden fields, humans don't
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true]);
    exit;
}

// Sanitize and validate
$nombre   = str_replace(["\r", "\n"], ' ', trim(htmlspecialchars($_POST['nombre']   ?? '', ENT_QUOTES, 'UTF-8')));
$emailRaw = trim($_POST['email'] ?? '');
$email    = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
$tel      = trim(htmlspecialchars($_POST['telefono'] ?? '', ENT_QUOTES, 'UTF-8'));
$servicio = str_replace(["\r", "\n"], ' ', trim(htmlspecialchars($_POST['servicio'] ?? '', ENT_QUOTES, 'UTF-8')));
$mensaje  = trim(htmlspecialchars($_POST['mensaje']  ?? '', ENT_QUOTES, 'UTF-8'));

if (empty($nombre)) {
    echo json_encode(['ok' => false, 'error' => 'El nombre es requerido.']);
    exit;
}
if (!$email) {
    echo json_encode(['ok' => false, 'error' => 'El correo electrónico no es válido.']);
    exit;
}

// Build email body
$body = '
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Nuevo contacto</title></head>
<body style="font-family:Arial,sans-serif;color:#191c1d;max-width:600px;margin:0 auto;padding:24px">
  <h2 style="color:#006192;border-bottom:2px solid #006192;padding-bottom:8px">Nuevo mensaje de contacto</h2>
  <table style="width:100%;border-collapse:collapse">
    <tr>
      <td style="padding:10px 0;font-weight:bold;width:160px;vertical-align:top">Nombre:</td>
      <td style="padding:10px 0">' . $nombre . '</td>
    </tr>
    <tr style="background:#f8f9fa">
      <td style="padding:10px 0;font-weight:bold;vertical-align:top">Correo:</td>
      <td style="padding:10px 0"><a href="mailto:' . $email . '">' . $email . '</a></td>
    </tr>
    <tr>
      <td style="padding:10px 0;font-weight:bold;vertical-align:top">Teléfono:</td>
      <td style="padding:10px 0">' . ($tel ?: '—') . '</td>
    </tr>
    <tr style="background:#f8f9fa">
      <td style="padding:10px 0;font-weight:bold;vertical-align:top">Servicio:</td>
      <td style="padding:10px 0">' . ($servicio ?: '—') . '</td>
    </tr>
    <tr>
      <td style="padding:10px 0;font-weight:bold;vertical-align:top">Mensaje:</td>
      <td style="padding:10px 0;white-space:pre-wrap">' . ($mensaje ?: '—') . '</td>
    </tr>
  </table>
  <p style="color:#94a3b8;font-size:12px;margin-top:32px">Enviado desde el formulario de contacto de cescom.cl</p>
</body>
</html>
';

$asunto = 'Nuevo contacto: ' . $nombre . ($servicio ? ' — ' . $servicio : '');

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->AuthType   = 'LOGIN';
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress(MAIL_TO);
    $mail->addReplyTo($email, $nombre);

    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body    = $body;
    $mail->AltBody = "Nombre: $nombre\nCorreo: $email\nTeléfono: $tel\nServicio: $servicio\nMensaje: $mensaje";

    $mail->send();

    // Confirmación al cliente
    $mail->clearAddresses();
    $mail->clearReplyTos();
    $mail->addAddress($email, $nombre);
    $mail->addReplyTo(MAIL_TO, 'Cescom Spa');
    $mail->Subject = 'Recibimos tu mensaje — Cescom Spa';
    $mail->Body    = '
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Confirmación</title></head>
<body style="font-family:Arial,sans-serif;color:#191c1d;max-width:600px;margin:0 auto;padding:24px">
  <h2 style="color:#006192;border-bottom:2px solid #006192;padding-bottom:8px">¡Gracias por contactarnos!</h2>
  <p>Hola <strong>' . $nombre . '</strong>, hemos recibido tu mensaje correctamente.</p>
  <p>Nuestro equipo lo revisará y se pondrá en contacto contigo a la brevedad.</p>
  <hr style="border:none;border-top:1px solid #e1e3e4;margin:24px 0">
  <p style="color:#40484f;font-size:14px">Si tienes alguna consulta urgente puedes escribirnos directamente a <a href="mailto:info@cescom.cl" style="color:#006192">info@cescom.cl</a> o por WhatsApp al <a href="https://wa.me/56940430949" style="color:#006192">+56 9 4043 0949</a>.</p>
  <p style="color:#94a3b8;font-size:12px;margin-top:32px">Cescom Spa — Ingeniería y Construcción</p>
</body>
</html>
';
    $mail->AltBody = "Hola $nombre, hemos recibido tu mensaje. Nuestro equipo se pondrá en contacto contigo a la brevedad.\n\nCescom Spa — info@cescom.cl";
    $mail->send();

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Error al enviar. Intente nuevamente o contáctenos por WhatsApp.']);
}
