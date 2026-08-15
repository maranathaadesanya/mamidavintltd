<?php
require_once __DIR__ . '/bootstrap.php';

// Logs a completed cart checkout for the sales record (CSV export via
// export_purchases.php). Deliberately does NOT require login — guest
// checkout must keep working. Best-effort: the mailto order flow in
// cart.js still completes even if this fails.

$catalog = require __DIR__ . '/catalog.php';
$input = json_input();
$items = is_array($input['items'] ?? null) ? $input['items'] : [];

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'No items to log.']);
    exit;
}

$lines = [];
$total = 0;

foreach ($items as $entry) {
    $id = trim((string) ($entry['id'] ?? ''));
    $qty = max(1, (int) ($entry['qty'] ?? 1));
    if (!isset($catalog[$id])) {
        continue;
    }
    $item = $catalog[$id];
    $subtotal = $item['price'] * $qty;
    $total += $subtotal;
    $lines[] = $item['name'] . ' x' . $qty . ' = ' . number_format($subtotal);
}

if (empty($lines)) {
    http_response_code(400);
    echo json_encode(['error' => 'No valid items to log.']);
    exit;
}

$summary = implode('; ', $lines);

$customerName = null;
$customerEmail = null;
$customerPhone = null;
$userId = current_user_id();

$pdo = get_db();

if ($userId) {
    $stmt = $pdo->prepare('SELECT full_name, email, phone FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if ($user) {
        $customerName = $user['full_name'];
        $customerEmail = $user['email'];
        $customerPhone = $user['phone'];
    }
}

$insert = $pdo->prepare(
    'INSERT INTO purchase_log (type, customer_name, customer_email, customer_phone, summary, amount, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$insert->execute(['order', $customerName, $customerEmail, $customerPhone, $summary, $total, $userId]);

echo json_encode(['success' => true]);
