<?php
require_once __DIR__ . '/bootstrap.php';
require_login();

$stmt = get_db()->prepare(
    'SELECT type, summary, amount, created_at FROM purchase_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 20'
);
$stmt->execute([current_user_id()]);

echo json_encode(['activity' => $stmt->fetchAll()]);
