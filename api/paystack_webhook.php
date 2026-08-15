<?php
// Configure this URL in the Paystack dashboard. This endpoint deliberately
// trusts only Paystack's signed server-to-server event, never browser input.
require_once __DIR__ . '/bootstrap.php';
$raw = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$secret = defined('PAYSTACK_WEBHOOK_SECRET') ? PAYSTACK_WEBHOOK_SECRET : '';
if ($secret === '' || !hash_equals(hash_hmac('sha512', $raw, $secret), $signature)) { http_response_code(401); echo json_encode(['error'=>'Invalid signature.']); exit; }
$event=json_decode($raw,true); if (($event['event'] ?? '') !== 'charge.success') { echo json_encode(['success'=>true]); exit; }
$data=$event['data'] ?? []; $reference=$data['reference'] ?? ''; $amount=(int)($data['amount'] ?? 0);
$pdo=get_db(); $stmt=$pdo->prepare('SELECT id,total FROM orders WHERE payment_reference=?'); $stmt->execute([$reference]); $order=$stmt->fetch();
if (!$order || $amount !== (int)$order['total']*100) { http_response_code(400); echo json_encode(['error'=>'Order mismatch.']); exit; }
$pdo->prepare('UPDATE orders SET payment_status="paid",order_status="confirmed" WHERE id=?')->execute([$order['id']]);
echo json_encode(['success'=>true]);
