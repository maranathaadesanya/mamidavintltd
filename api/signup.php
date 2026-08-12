<?php
require_once __DIR__ . '/bootstrap.php';

$input = json_input();
$fullName = trim($input['full_name'] ?? '');
$email = trim(strtolower($input['email'] ?? ''));
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';

if ($fullName === '' || $email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Full name, email and password are required.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please enter a valid email address.']);
    exit;
}
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 8 characters.']);
    exit;
}

$pdo = get_db();

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'An account with that email already exists.']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
$stmt->execute([$fullName, $email, $phone, $hash]);

$_SESSION['user_id'] = (int)$pdo->lastInsertId();
session_regenerate_id(true);

echo json_encode(['full_name' => $fullName, 'email' => $email, 'phone' => $phone]);
