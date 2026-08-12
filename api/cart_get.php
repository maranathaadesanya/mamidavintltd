<?php
require_once __DIR__ . '/bootstrap.php';
require_login();

$pdo = get_db();
$stmt = $pdo->prepare('SELECT item_id AS id, item_name AS name, price, category, qty FROM cart_items WHERE user_id = ? ORDER BY id');
$stmt->execute([current_user_id()]);

echo json_encode(['items' => $stmt->fetchAll()]);
