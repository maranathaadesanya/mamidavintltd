<?php
// Called right after signup/login to fold a guest's localStorage cart into
// their new account cart. Prices/names are re-looked-up from the server
// catalog, never trusted from the client.
require_once __DIR__ . '/bootstrap.php';
require_login();

$catalog = require __DIR__ . '/catalog.php';
$input = json_input();
$items = is_array($input['items'] ?? null) ? $input['items'] : [];

$pdo = get_db();
$stmt = $pdo->prepare('INSERT INTO cart_items (user_id, item_id, item_name, price, category, qty)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)');

foreach ($items as $entry) {
    $id = trim($entry['id'] ?? '');
    $qty = max(1, (int)($entry['qty'] ?? 1));
    if (!isset($catalog[$id])) continue;
    $item = $catalog[$id];
    $stmt->execute([current_user_id(), $id, $item['name'], $item['price'], $item['category'], $qty]);
}

echo json_encode(['success' => true]);
