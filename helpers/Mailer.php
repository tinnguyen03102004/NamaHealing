<?php
namespace helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PDO;

class Mailer {
    public static function queue(PDO $pdo, string $to, string $subject, string $html, string $text=''): void {
        $sql = "INSERT INTO email_queue(to_email,subject,body_html,body_text,status,attempts,created_at)
                VALUES(:to_email,:subject,:body_html,:body_text,'pending',0,NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':to_email' => $to,
            ':subject' => $subject,
            ':body_html' => $html,
            ':body_text' => $text,
        ]);
    }

    // Worker thực thi: lấy pending -> gửi -> update status
    public static function sendNow(array $row): bool {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USER');
        $mail->Password = getenv('SMTP_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)(getenv('SMTP_PORT') ?: 587);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME') ?: 'NamaHealing');
        $mail->addAddress($row['to_email']);
        $mail->isHTML(true);
        $mail->Subject = $row['subject'];
        $mail->Body = $row['body_html'];
        $mail->AltBody = $row['body_text'] ?: strip_tags($row['body_html']);
        return $mail->send();
    }
}
