<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email.php';

function generate_verification_code() {
    return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

$input = json_input();
$action = strtolower(trim((string) ($input['action'] ?? 'create')));
$fullName = trim((string) ($input['full_name'] ?? ''));
$email = trim(strtolower((string) ($input['email'] ?? '')));
$phone = trim((string) ($input['phone'] ?? ''));
$password = (string) ($input['password'] ?? '');
$verificationCode = trim((string) ($input['verification_code'] ?? ''));

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please enter a valid email address.']);
    exit;
}

$pdo = get_db();

if ($action === 'send_code') {
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

    $code = generate_verification_code();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $stmt = $pdo->prepare(
        'INSERT INTO email_verifications (email, full_name, phone, password_hash, verification_code, expires_at, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW()) ' .
        'ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone = VALUES(phone), password_hash = VALUES(password_hash), verification_code = VALUES(verification_code), expires_at = VALUES(expires_at), created_at = NOW(), verified_at = NULL'
    );
    $stmt->execute([$email, $fullName, $phone, $hash, $code, $expiresAt]);

    try {
        $mailBody = "Hello {$fullName},\n\nYour Mamidav verification code is: {$code}\n\nThis code is valid for 15 minutes. If you did not request this signup, you can ignore this email.\n";
        send_mamidav_email($email, 'Your Mamidav verification code', $mailBody);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'We could not send the verification email right now. Please try again later.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'A verification code has been sent to your email.']);
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
        http_response_code(400);
        echo json_encode(['error' => 'The verification code is invalid or has expired. Please request a new code.']);
        exit;
    }

    $mark = $pdo->prepare('UPDATE email_verifications SET verified_at = NOW() WHERE id = ?');
    $mark->execute([$verification['id']]);

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

echo json_encode(['full_name' => $fullName, 'email' => $email, 'phone' => $phone]);
