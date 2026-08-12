<?php
require_once __DIR__ . '/bootstrap.php';
require_login();

$catalog = require __DIR__ . '/catalog.php';
$input = json_input();
$id = trim($input['id'] ?? '');
$qty = max(1, (int)($input['qty'] ?? 1));

if (!isset($catalog[$id])) {
    http_response_code(400);
    echo json_encode(['error' => 'Unknown item.']);
    exit;
}
$item = $catalog[$id];

$pdo = get_db();
$stmt = $pdo->prepare('INSERT INTO cart_items (user_id, item_id, item_name, price, category, qty)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)');
$stmt->execute([current_user_id(), $id, $item['name'], $item['price'], $item['category'], $qty]);

echo json_encode(['success' => true]);
