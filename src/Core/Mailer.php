<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private PHPMailer $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        if (SMTP_HOST) {
            $this->mail->isSMTP();
            $this->mail->Host = SMTP_HOST;
            $this->mail->Port = SMTP_PORT;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = SMTP_USER;
            $this->mail->Password = SMTP_PASS;
            $this->mail->SMTPSecure = 'tls';
        } else {
            $this->mail->isMail();
        }

        $this->mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    }

    public static function send(string $to, string $subject, string $body, string $replyTo = null): bool {
        try {
            $mailer = new self();
            $mailer->mail->addAddress($to);
            if ($replyTo) {
                $mailer->mail->addReplyTo($replyTo);
            }
            $mailer->mail->Subject = $subject;
            $mailer->mail->Body = $body;
            $mailer->mail->isHTML(true);
            return $mailer->mail->send();
        } catch (Exception $e) {
            if (DEBUG) {
                error_log($e->getMessage());
            }
            return false;
        }
    }

    public static function resetPassword(string $to, string $resetUrl): bool {
        $html = sprintf(
            '<p>Kliknij <a href="%s">tutaj</a> aby zresetować hasło.</p><p>Link wygaśnie za 24 godziny.</p>',
            $resetUrl
        );
        return self::send($to, 'Reset hasła — ' . APP_NAME, $html);
    }
}
