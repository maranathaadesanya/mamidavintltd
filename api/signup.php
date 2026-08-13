<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email.php';

function generate_verification_code() {
    return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

function mask_email_for_log($email) {
    $parts = explode('@', $email, 2);
    if (count($parts) !== 2) {
        return '[invalid-recipient]';
    }

    $localPart = $parts[0];
    return substr($localPart, 0, 1) . '***@' . $parts[1];
}

function categorize_mail_error($message) {
    if (preg_match('/connect|connection|network|dns|host/i', $message)) return 'connection';
    if (preg_match('/tls|starttls|ssl|crypto/i', $message)) return 'tls';
    if (preg_match('/auth|login|credential/i', $message)) return 'authentication';
    if (preg_match('/sender|from address|setfrom/i', $message)) return 'sender';
    if (preg_match('/recipient|addaddress|invalid address/i', $message)) return 'recipient';
    return 'phpmailer';
}

function sanitize_mail_error_for_log($message) {
    $message = preg_replace('/(password|smtp_pass|username|smtp_user)\s*(?:=|:|is)?\s*[^\s,;]+/i', '$1=[redacted]', (string) $message);
    $message = preg_replace('/\b\d{6}\b/', '[redacted-code]', $message);
    return substr(preg_replace('/[\r\n]+/', ' ', $message), 0, 500);
}

function log_signup_diagnostic($event, $action, $category = null) {
    $entry = [
        'event' => $event,
        'timestamp' => gmdate('c'),
        'action' => $action,
    ];
    if ($category !== null) {
        $entry['category'] = $category;
    }
    error_log(json_encode($entry));
}

$input = json_input();
$action = strtolower(trim((string) ($input['action'] ?? 'create')));
log_signup_diagnostic('signup_request_received', $action);
$fullName = trim((string) ($input['full_name'] ?? ''));
$email = trim(strtolower((string) ($input['email'] ?? '')));
$phone = trim((string) ($input['phone'] ?? ''));
$password = (string) ($input['password'] ?? '');
$verificationCode = trim((string) ($input['verification_code'] ?? ''));

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    log_signup_diagnostic('signup_validation_failed', $action, 'invalid_email');
    http_response_code(400);
    echo json_encode(['error' => 'Please enter a valid email address.']);
    exit;
}

$pdo = get_db();

if ($action === 'send_code') {
    if ($fullName === '' || $email === '' || $password === '') {
        log_signup_diagnostic('signup_validation_failed', $action, 'missing_required_field');
        http_response_code(400);
        echo json_encode(['error' => 'Full name, email and password are required.']);
        exit;
    }
    if (strlen($password) < 8) {
        log_signup_diagnostic('signup_validation_failed', $action, 'password_length');
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 8 characters.']);
        exit;
    }

    $existingUser = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $existingUser->execute([$email]);
    if ($existingUser->fetch()) {
        log_signup_diagnostic('signup_validation_failed', $action, 'duplicate_email');
        http_response_code(409);
        echo json_encode(['error' => 'An account with that email already exists.']);
        exit;
    }

    log_signup_diagnostic('signup_validation_passed', $action);

    $code = generate_verification_code();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $stmt = $pdo->prepare(
        'INSERT INTO email_verifications (email, full_name, phone, password_hash, verification_code, expires_at, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW()) ' .
        'ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone = VALUES(phone), password_hash = VALUES(password_hash), verification_code = VALUES(verification_code), expires_at = VALUES(expires_at), created_at = NOW(), verified_at = NULL'
    );
    $stmt->execute([$email, $fullName, $phone, $hash, $code, $expiresAt]);
    log_signup_diagnostic('signup_verification_record_created', $action);

    try {
        log_signup_diagnostic('signup_verification_email_send_attempted', $action);
        $mailBody = "Hello {$fullName},\n\nYour Mamidav verification code is: {$code}\n\nThis code is valid for 15 minutes. If you did not request this signup, you can ignore this email.\n";
        $emailAccepted = send_mamidav_email($email, 'Your Mamidav verification code', $mailBody);
        if ($emailAccepted !== true) {
            throw new RuntimeException('PHPMailer did not accept the verification message for sending.');
        }
        log_signup_diagnostic('verification_email_accepted_by_smtp', $action);
    } catch (Throwable $e) {
        try {
            $cleanup = $pdo->prepare('DELETE FROM email_verifications WHERE email = ? AND verification_code = ?');
            $cleanup->execute([$email, $code]);
        } catch (Throwable $cleanupError) {
            error_log(json_encode([
                'event' => 'signup_verification_cleanup_failed',
                'timestamp' => gmdate('c'),
                'recipient' => mask_email_for_log($email),
            ]));
        }

        error_log(json_encode([
            'event' => 'signup_verification_email_failed',
            'timestamp' => gmdate('c'),
            'recipient' => mask_email_for_log($email),
            'category' => categorize_mail_error($e->getMessage()),
            'error' => sanitize_mail_error_for_log($e->getMessage()),
        ]));
        http_response_code(500);
        log_signup_diagnostic('signup_response_sent', $action, 'email_send_failed');
        echo json_encode(['error' => 'We could not send the verification email right now. Please try again later.']);
        exit;
    }

    log_signup_diagnostic('signup_response_sent', $action, 'email_send_succeeded');
    echo json_encode(['success' => true, 'message' => 'Verification code sent successfully.']);
    exit;
}

if ($action === 'verify_code') {
    if ($email === '' || $verificationCode === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Email and verification code are required.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT id, email, verification_code, expires_at FROM email_verifications WHERE email = ? AND verification_code = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1'
    );
    $stmt->execute([$email, $verificationCode]);
    $verification = $stmt->fetch();

    if (!$verification) {
        log_signup_diagnostic('signup_validation_failed', $action, 'invalid_or_expired_code');
        http_response_code(400);
        echo json_encode(['error' => 'The verification code is invalid or has expired. Please request a new code.']);
        exit;
    }

    $mark = $pdo->prepare('UPDATE email_verifications SET verified_at = NOW() WHERE id = ?');
    $mark->execute([$verification['id']]);

    log_signup_diagnostic('signup_verification_code_validated', $action);
    log_signup_diagnostic('signup_response_sent', $action, 'code_validated');
    echo json_encode(['success' => true, 'message' => 'Email verified successfully.']);
    exit;
}

if ($fullName === '' || $email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Full name, email and password are required.']);
    exit;
}
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 8 characters.']);
    exit;
}

$existingUser = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$existingUser->execute([$email]);
if ($existingUser->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'An account with that email already exists.']);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, password_hash, verification_code, expires_at, verified_at FROM email_verifications WHERE email = ? ORDER BY created_at DESC LIMIT 1'
);
$stmt->execute([$email]);
$pending = $stmt->fetch();

if (!$pending) {
    http_response_code(400);
    echo json_encode(['error' => 'Please request a verification code before creating your account.']);
    exit;
}

if (strtotime($pending['expires_at']) < time()) {
    http_response_code(400);
    echo json_encode(['error' => 'Your verification code has expired. Please request a new one.']);
    exit;
}

if ($pending['verified_at'] === null && empty($verificationCode)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please verify your email before creating the account.']);
    exit;
}

if (!empty($verificationCode) && $pending['verification_code'] !== $verificationCode) {
    http_response_code(400);
    echo json_encode(['error' => 'The verification code does not match.']);
    exit;
}

if ($pending['verified_at'] === null) {
    $mark = $pdo->prepare('UPDATE email_verifications SET verified_at = NOW() WHERE id = ?');
    $mark->execute([$pending['id']]);
}

log_signup_diagnostic('signup_verification_code_validated', $action);

$hash = $pending['password_hash'];
if ($hash === '' || !password_get_info($hash)['algo']) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
}

$insert = $pdo->prepare('INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
$insert->execute([$fullName, $email, $phone, $hash]);

$stmt = $pdo->prepare('DELETE FROM email_verifications WHERE email = ?');
$stmt->execute([$email]);

$_SESSION['user_id'] = (int) $pdo->lastInsertId();
session_regenerate_id(true);

log_signup_diagnostic('signup_response_sent', $action, 'account_created');
echo json_encode(['success' => true, 'full_name' => $fullName, 'email' => $email, 'phone' => $phone]);
