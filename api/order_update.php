<?php
require_once __DIR__ . '/bootstrap.php'; require_admin(); $in=json_input(); $id=(int)($in['id']??0); $action=$in['action']??''; $notes=trim((string)($in['notes']??''));
if (!$id || !in_array($action,['paid','reject','processing','completed'],true)){http_response_code(400);echo json_encode(['error'=>'Invalid update.']);exit;}
$map=['paid'=>['paid','confirmed'],'reject'=>['failed','cancelled'],'processing'=>[null,'processing'],'completed'=>[null,'completed']];[$payment,$order]=$map[$action];$pdo=get_db();
if($payment!==null){$stmt=$pdo->prepare('UPDATE orders SET payment_status=?,order_status=?,notes=?,verified_by=?,verified_at=NOW() WHERE id=?');$stmt->execute([$payment,$order,$notes,current_user_id(),$id]);}else{$stmt=$pdo->prepare('UPDATE orders SET order_status=?,notes=? WHERE id=?');$stmt->execute([$order,$notes,$id]);}echo json_encode(['success'=>true]);
