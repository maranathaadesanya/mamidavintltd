<?php
require_once __DIR__ . '/bootstrap.php';
require_customer();
$reference = trim((string)(json_input()['reference'] ?? ''));
$pdo=get_db(); $stmt=$pdo->prepare('UPDATE orders SET payment_method="bank_transfer", payment_reference=reference WHERE reference=? AND user_id=? AND payment_status="pending"');
$stmt->execute([$reference,current_user_id()]);
if (!$stmt->rowCount()) { http_response_code(404); echo json_encode(['error'=>'Pending order not found.']); exit; }
echo json_encode(['success'=>true]);
