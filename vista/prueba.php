<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Esto hace que busque desde la carpeta raíz


try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'vypsoporte30@gmail.com';
    $mail->Password = 'muga akta guef hfyf'; // O la contraseña de aplicación
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('vypsoporte30@gmail.com', 'Alertas Flota');
    $mail->addAddress('erickpenaasencio15@gmail.com', 'Destinatario'); // Cambia el destinatario

    $mail->isHTML(true);
    $mail->Subject = 'Prueba de envío';
    $mail->Body    = 'Este es un correo de prueba';

    $mail->send();
    echo 'Correo enviado correctamente!';
} catch (Exception $e) {
    echo 'Error al enviar el correo: ', $e->getMessage();
}
