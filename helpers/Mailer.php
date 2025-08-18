<?php
// helpers/Mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private static function env(string $k, $default = null) {
        if (isset($_ENV[$k]) && $_ENV[$k] !== '') return $_ENV[$k];
        $v = getenv($k);
        return ($v !== false && $v !== '') ? $v : $default;
    }

    /**
     * Gửi email HTML. Trả về true nếu gửi thành công, false nếu thất bại.
     */
    public static function send(string $to, string $subject, string $html, ?string $toName = null): bool
    {
        if (!class_exists(PHPMailer::class)) {
            error_log('[Mailer] PHPMailer is not loaded (composer autoload missing?)');
            return false;
        }

        $host       = self::env('MAIL_HOST', self::env('SMTP_HOST', 'localhost'));
        $user       = self::env('MAIL_USERNAME', self::env('SMTP_USER', ''));
        $pass       = self::env('MAIL_PASSWORD', self::env('SMTP_PASS', ''));
        $enc        = strtolower((string) self::env('MAIL_ENCRYPTION', self::env('SMTP_ENCRYPTION', 'tls')));
        $port       = (int) self::env('MAIL_PORT', self::env('SMTP_PORT', $enc === 'ssl' ? 465 : 587));
        $from       = self::env('MAIL_FROM_ADDRESS', self::env('SMTP_FROM', $user ?: 'no-reply@namahealing.com'));
        $fromName   = self::env('MAIL_FROM_NAME', 'NamaHealing');
        $debugFlag  = (int) self::env('MAIL_DEBUG', 0); // 1 để ghi debug vào error_log

        // Nếu from không phải email hợp lệ, fallback về username
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $from = $user;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;

            if ($enc === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                if ($port === 465) { $port = 587; } // tránh nhầm cổng
            }
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';

            if ($debugFlag) {
                $mail->SMTPDebug  = 2;
                $mail->Debugoutput = function ($str, $level) {
                    error_log('[SMTP] ' . trim($str));
                };
            }

            $mail->setFrom($from, $fromName);
            $mail->addAddress($to, $toName ?? $to);
            $mail->addReplyTo($from, $fromName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = strip_tags($html);

            $mail->send();
            if ($debugFlag) error_log("[Mailer] Sent to {$to}");
            return true;
        } catch (Exception $e) {
            error_log('[Mailer] Send failed: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
