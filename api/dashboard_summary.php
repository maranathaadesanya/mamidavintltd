<?php
require_once __DIR__ . '/bootstrap.php';
require_admin();

$pdo = get_db();

$counts = ['order' => 0, 'investment_inquiry' => 0, 'event_booking' => 0, 'consultation_request' => 0];
$counts['order'] = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$stmt = $pdo->query('SELECT type, COUNT(*) AS n FROM purchase_log GROUP BY type');
while ($row = $stmt->fetch()) {
    $counts[$row['type']] = (int) $row['n'];
}

$revenueStmt = $pdo->query(
    "SELECT COALESCE((SELECT SUM(total) FROM orders WHERE payment_status = 'paid'), 0) + COALESCE((SELECT SUM(amount) FROM purchase_log WHERE type = 'event_booking'), 0) AS total"
);
$totalRevenue = (int) $revenueStmt->fetch()['total'];

$activityPage = max(1, (int)($_GET['page'] ?? 1));
$activityLimit = 10;
$activityTotal = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$activityPages = max(1, (int)ceil($activityTotal / $activityLimit));
$activityPage = min($activityPage, $activityPages);
$activityOffset = ($activityPage - 1) * $activityLimit;
$recentStmt = $pdo->query("SELECT 'order' AS type, customer_name, CONCAT(reference, ' - ', payment_status) AS summary, total AS amount, created_at FROM orders ORDER BY created_at DESC LIMIT $activityLimit OFFSET $activityOffset");
$recent = $recentStmt->fetchAll();

echo json_encode([
    'total_orders' => $counts['order'],
    'total_event_bookings' => $counts['event_booking'],
    'total_investment_inquiries' => $counts['investment_inquiry'],
    'total_consultation_requests' => $counts['consultation_request'],
    'total_revenue' => $totalRevenue,
    'recent' => $recent,
    'recent_page' => $activityPage,
    'recent_pages' => $activityPages,
    'recent_total' => $activityTotal,
]);
