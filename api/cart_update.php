<?php
require_once __DIR__ . '/bootstrap.php';
require_customer();

$input = json_input();
$id = trim($input['id'] ?? '');
$qty = max(1, (int)($input['qty'] ?? 1));

if ($id === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing item id.']);
    exit;
}

$pdo = get_db();
$stmt = $pdo->prepare('UPDATE cart_items SET qty = ? WHERE user_id = ? AND item_id = ?');
$stmt->execute([$qty, current_user_id(), $id]);

echo json_encode(['success' => true]);
