<?php
require_once __DIR__ . '/bootstrap.php';
require_customer();

$input = json_input();
$id = trim($input['id'] ?? '');

$pdo = get_db();
$stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND item_id = ?');
$stmt->execute([current_user_id(), $id]);

echo json_encode(['success' => true]);
