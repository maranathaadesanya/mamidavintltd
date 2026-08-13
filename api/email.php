<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function mamidav_smtp_config() {
    $config = [
        'host' => getenv('SMTP_HOST') ?: 'smtp.hostinger.com',
        'port' => getenv('SMTP_PORT') ?: '587',
        'username' => getenv('SMTP_USER') ?: 'no-reply@mamidavintltd.com',
        'password' => getenv('SMTP_PASS') ?: '',
        'secure' => getenv('SMTP_SECURE') ?: 'tls',
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@mamidavintltd.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'Mamidav Website',
    ];

    if (file_exists(__DIR__ . '/email_config.local.php')) {
        include __DIR__ . '/email_config.local.php';
        if (defined('SMTP_HOST') && SMTP_HOST) $config['host'] = SMTP_HOST;
        if (defined('SMTP_PORT') && SMTP_PORT) $config['port'] = (string) SMTP_PORT;
        if (defined('SMTP_USER') && SMTP_USER) $config['username'] = SMTP_USER;
        if (defined('SMTP_PASS') && SMTP_PASS) $config['password'] = SMTP_PASS;
        if (defined('SMTP_SECURE') && SMTP_SECURE) $config['secure'] = SMTP_SECURE;
        if (defined('MAIL_FROM_ADDRESS') && MAIL_FROM_ADDRESS) $config['from_address'] = MAIL_FROM_ADDRESS;
        if (defined('MAIL_FROM_NAME') && MAIL_FROM_NAME) $config['from_name'] = MAIL_FROM_NAME;
    }

    return $config;
}

function send_mamidav_email($to, $subject, $body, $replyTo = null) {
    $config = mamidav_smtp_config();

    if (empty($config['password'])) {
        throw new RuntimeException('SMTP password is not configured.');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['secure'];
    $mail->Port = (int) $config['port'];
    $mail->setFrom($config['from_address'], $config['from_name']);
    $mail->addAddress($to);
    if ($replyTo) {
        $mail->addReplyTo($replyTo);
    }
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->isHTML(false);
    $mail->CharSet = 'UTF-8';

    return $mail->send();
}
