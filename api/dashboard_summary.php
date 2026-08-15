<?php
require_once __DIR__ . '/bootstrap.php';
require_admin();

$pdo = get_db();

$counts = ['order' => 0, 'investment_inquiry' => 0, 'event_booking' => 0, 'consultation_request' => 0];
$stmt = $pdo->query('SELECT type, COUNT(*) AS n FROM purchase_log GROUP BY type');
while ($row = $stmt->fetch()) {
    $counts[$row['type']] = (int) $row['n'];
}

$revenueStmt = $pdo->query(
    "SELECT COALESCE(SUM(amount), 0) AS total FROM purchase_log WHERE type IN ('order', 'event_booking')"
);
$totalRevenue = (int) $revenueStmt->fetch()['total'];

$recentStmt = $pdo->query(
    'SELECT type, customer_name, summary, amount, created_at FROM purchase_log ORDER BY created_at DESC LIMIT 10'
);
$recent = $recentStmt->fetchAll();

echo json_encode([
    'total_orders' => $counts['order'],
    'total_event_bookings' => $counts['event_booking'],
    'total_investment_inquiries' => $counts['investment_inquiry'],
    'total_consultation_requests' => $counts['consultation_request'],
    'total_revenue' => $totalRevenue,
    'recent' => $recent,
]);
