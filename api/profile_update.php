<?php
require_once __DIR__ . '/bootstrap.php';
require_login();

$input = json_input();
$fullName = trim($input['full_name'] ?? '');
$phone = trim($input['phone'] ?? '');

if ($fullName === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Full name is required.']);
    exit;
}

$pdo = get_db();
$stmt = $pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?');
$stmt->execute([$fullName, $phone, current_user_id()]);

echo json_encode(['success' => true]);
