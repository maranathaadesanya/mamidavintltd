<?php
require_once __DIR__ . '/bootstrap.php';
require_login();

$pdo = get_db();
$userId = current_user_id();
$user = $pdo->prepare('SELECT full_name, email, phone FROM users WHERE id = ?');
$user->execute([$userId]);
$customer = $user->fetch();
$itemsStmt = $pdo->prepare('SELECT item_id, item_name, price, qty FROM cart_items WHERE user_id = ?');
$itemsStmt->execute([$userId]);
$items = $itemsStmt->fetchAll();
if (!$items) { http_response_code(400); echo json_encode(['error' => 'Your cart is empty.']); exit; }

$total = 0;
foreach ($items as $item) { $total += (int)$item['price'] * (int)$item['qty']; }
$reference = 'MIL-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
$pdo->beginTransaction();
try {
  $order = $pdo->prepare('INSERT INTO orders (reference,user_id,customer_name,customer_email,customer_phone,subtotal,total,order_source,payment_status,order_status) VALUES (?,?,?,?,?,?,?,?,?,?)');
  $order->execute([$reference,$userId,$customer['full_name'],$customer['email'],$customer['phone'],$total,$total,'online','pending','pending_payment']);
  $orderId = (int)$pdo->lastInsertId();
  $line = $pdo->prepare('INSERT INTO order_items (order_id,item_id,item_name,unit_price,qty,subtotal) VALUES (?,?,?,?,?,?)');
  foreach ($items as $item) { $line->execute([$orderId,$item['item_id'],$item['item_name'],$item['price'],$item['qty'],(int)$item['price']*(int)$item['qty']]); }
  $pdo->commit();
  echo json_encode(['success'=>true,'order_id'=>$orderId,'reference'=>$reference,'total'=>$total]);
} catch (Throwable $e) { $pdo->rollBack(); http_response_code(500); echo json_encode(['error'=>'Could not create checkout.']); }
