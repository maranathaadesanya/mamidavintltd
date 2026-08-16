<?php
require_once __DIR__ . '/bootstrap.php';
require_customer();
$reference=trim((string)(json_input()['reference'] ?? '')); if (!defined('PAYSTACK_SECRET_KEY') || PAYSTACK_SECRET_KEY==='') { http_response_code(503); echo json_encode(['error'=>'Online payments are not configured yet.']); exit; }
$pdo=get_db(); $stmt=$pdo->prepare('SELECT * FROM orders WHERE payment_reference=? AND user_id=?'); $stmt->execute([$reference,current_user_id()]); $order=$stmt->fetch(); if (!$order) { http_response_code(404); echo json_encode(['error'=>'Payment not found.']); exit; }
$ch=curl_init('https://api.paystack.co/transaction/verify/'.rawurlencode($reference)); curl_setopt_array($ch,[CURLOPT_HTTPHEADER=>['Authorization: Bearer '.PAYSTACK_SECRET_KEY],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30]); $result=json_decode(curl_exec($ch),true); curl_close($ch);
$paid=!empty($result['status']) && ($result['data']['status'] ?? '')==='success' && (int)($result['data']['amount'] ?? 0)===(int)$order['total']*100;
if ($paid) { $pdo->prepare('UPDATE orders SET payment_status="paid",order_status="confirmed" WHERE id=?')->execute([$order['id']]); $pdo->prepare('DELETE FROM cart_items WHERE user_id=?')->execute([current_user_id()]); }
echo json_encode(['success'=>true,'paid'=>$paid,'payment_status'=>$paid?'paid':'pending']);
