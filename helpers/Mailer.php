<?php
namespace NamaHealing\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Wrapper around PHPMailer for sending transactional mails.
 */
class Mailer {
    public static function send(string $to, string $subject, string $htmlBody): void {
        if (!class_exists(PHPMailer::class)) {
            return;
        }

        $mail = new PHPMailer(true);
        try {
            // Read mail configuration from environment variables. Support both
            // MAIL_* (preferred) and legacy SMTP_* keys for backward compatibility.
            $host = $_ENV['MAIL_HOST']        ?? $_ENV['SMTP_HOST'] ?? 'localhost';
            $user = $_ENV['MAIL_USERNAME']    ?? $_ENV['SMTP_USER'] ?? '';
            $pass = $_ENV['MAIL_PASSWORD']    ?? $_ENV['SMTP_PASS'] ?? '';
            $enc  = strtolower($_ENV['MAIL_ENCRYPTION'] ?? ($_ENV['SMTP_ENCRYPTION'] ?? 'tls'));
            $port = (int)($_ENV['MAIL_PORT'] ?? $_ENV['SMTP_PORT'] ?? ($enc === 'tls' ? 587 : 465));
            $from = $_ENV['MAIL_FROM_ADDRESS'] ?? $_ENV['SMTP_FROM'] ?? 'no-reply@namahealing.local';
            $name = $_ENV['MAIL_FROM_NAME']    ?? 'NamaHealing';

            $mail->isSMTP();
            $mail->Host     = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $pass;
            if ($enc === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port     = $port;
            $mail->CharSet  = 'UTF-8';

            $mail->setFrom($from, $name);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->send();
        } catch (Exception $e) {
            // In production you may want to log this error.
        }
    }
}

