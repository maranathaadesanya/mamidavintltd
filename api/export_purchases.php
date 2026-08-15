<?php
require_once __DIR__ . '/bootstrap.php';
require_admin();

// Override bootstrap.php's default JSON content type - this endpoint
// downloads a CSV file instead.
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="mamidav-purchases-' . date('Y-m-d') . '.csv"');

$pdo = get_db();
$stmt = $pdo->query(
    'SELECT created_at, type, customer_name, customer_email, customer_phone, summary, amount FROM purchase_log ORDER BY created_at DESC'
);

$out = fopen('php://output', 'w');

// Excel-friendly UTF-8 byte-order mark, so the Naira sign and any accented
// characters in the summary render correctly when opened directly.
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, ['Date', 'Type', 'Customer Name', 'Email', 'Phone', 'Summary', 'Amount (NGN)']);

while ($row = $stmt->fetch()) {
    fputcsv($out, [
        $row['created_at'],
        $row['type'],
        $row['customer_name'],
        $row['customer_email'],
        $row['customer_phone'],
        $row['summary'],
        $row['amount'],
    ]);
}

fclose($out);
