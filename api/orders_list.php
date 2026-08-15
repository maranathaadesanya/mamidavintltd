<?php
require_once __DIR__ . '/bootstrap.php'; require_admin();
$input = $_GET; $page=max(1,(int)($input['page']??1)); $limit=10; $offset=($page-1)*$limit; $where=[]; $args=[];
foreach (['payment_status','order_status','order_source','payment_method'] as $field) { if (!empty($input[$field])) {$where[]="$field = ?"; $args[]=$input[$field];} }
if (!empty($input['date_from'])) {$where[]='created_at >= ?'; $args[]=$input['date_from'].' 00:00:00';}
if (!empty($input['date_to'])) {$where[]='created_at <= ?'; $args[]=$input['date_to'].' 23:59:59';}
if (!empty($input['q'])) {$where[]='(reference LIKE ? OR customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ? OR payment_reference LIKE ?)'; for($i=0;$i<5;$i++)$args[]='%'.$input['q'].'%';}
$sqlWhere=$where?' WHERE '.implode(' AND ',$where):''; $pdo=get_db(); $count=$pdo->prepare('SELECT COUNT(*) FROM orders'.$sqlWhere);$count->execute($args);$total=(int)$count->fetchColumn();
$stmt=$pdo->prepare('SELECT id,reference,customer_name,customer_email,customer_phone,subtotal,discount,total,order_source,payment_method,payment_reference,payment_status,order_status,notes,created_at,updated_at,verified_by,verified_at FROM orders'.$sqlWhere.' ORDER BY created_at DESC LIMIT '.$limit.' OFFSET '.$offset);$stmt->execute($args);
echo json_encode(['orders'=>$stmt->fetchAll(),'total'=>$total,'page'=>$page,'pages'=>max(1,(int)ceil($total/$limit))]);
