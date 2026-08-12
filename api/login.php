<?php
require_once __DIR__ . '/bootstrap.php';

$input = json_input();
$email = trim(strtolower($input['email'] ?? ''));
$password = $input['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required.']);
    exit;
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT id, full_name, email, phone, password_hash, failed_login_attempts, locked_until FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Same generic error whether the email exists or not, so we don't leak which emails are registered.
$genericError = 'Incorrect email or password.';

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => $genericError]);
    exit;
}

if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
    http_response_code(423);
    echo json_encode(['error' => 'Too many failed attempts. Please try again in a few minutes.']);
    exit;
}

if (!password_verify($password, $user['password_hash'])) {
    $attempts = $user['failed_login_attempts'] + 1;
    $lockUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 300) : null;

    $stmt = $pdo->prepare('UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?');
    $stmt->execute([$attempts, $lockUntil, $user['id']]);

    http_response_code(401);
    echo json_encode(['error' => $genericError]);
    exit;
}

$stmt = $pdo->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?');
$stmt->execute([$user['id']]);

$_SESSION['user_id'] = (int)$user['id'];
session_regenerate_id(true);

echo json_encode(['full_name' => $user['full_name'], 'email' => $user['email'], 'phone' => $user['phone']]);
