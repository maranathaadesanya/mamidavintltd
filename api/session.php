<?php
require_once __DIR__ . '/bootstrap.php';

$userId = current_user_id();
if (!$userId) {
    echo json_encode(['logged_in' => false]);
    exit;
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT full_name, email, phone, is_admin FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['logged_in' => false]);
    exit;
}

echo json_encode([
    'logged_in' => true,
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'phone' => $user['phone'],
    'is_admin' => (int) $user['is_admin'] === 1,
]);
