<?php
require_once __DIR__ . '/bootstrap.php';
require_customer();
$input = json_input(); $reference = trim((string)($input['reference'] ?? '')); $method = $input['method'] ?? 'paystack';
if (!in_array($method, ['paystack','ussd'], true)) { http_response_code(400); echo json_encode(['error'=>'Unsupported payment method.']); exit; }
if (!defined('PAYSTACK_SECRET_KEY') || PAYSTACK_SECRET_KEY === '') { http_response_code(503); echo json_encode(['error'=>'Online payments are not configured yet.']); exit; }
$pdo=get_db(); $stmt=$pdo->prepare('SELECT * FROM orders WHERE reference=? AND user_id=? AND payment_status="pending"'); $stmt->execute([$reference,current_user_id()]); $order=$stmt->fetch();
if (!$order) { http_response_code(404); echo json_encode(['error'=>'Pending order not found.']); exit; }
$paymentRef = 'MILPAY-' . strtoupper(bin2hex(random_bytes(8)));
$scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$callbackUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/payments.html?order=' . rawurlencode($order['reference']);
$payload=['email'=>$order['customer_email'],'amount'=>(int)$order['total']*100,'reference'=>$paymentRef,'metadata'=>['order_reference'=>$order['reference']],'callback_url'=>$callbackUrl];
if ($method==='ussd') $payload['channels']=['ussd'];
$ch=curl_init('https://api.paystack.co/transaction/initialize'); curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_HTTPHEADER=>['Authorization: Bearer '.PAYSTACK_SECRET_KEY,'Content-Type: application/json'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30]); $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); $result=json_decode($raw,true);
if ($status<200 || $status>=300 || empty($result['status'])) { http_response_code(502); echo json_encode(['error'=>'Unable to initialize Paystack payment.']); exit; }
$pdo->prepare('UPDATE orders SET payment_method=?, payment_reference=? WHERE id=?')->execute([$method,$paymentRef,$order['id']]);
echo json_encode(['success'=>true,'authorization_url'=>$result['data']['authorization_url'] ?? null,'payment_reference'=>$paymentRef]);
