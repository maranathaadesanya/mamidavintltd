<?php
require_once __DIR__ . '/bootstrap.php';
echo json_encode([
  'paystack_available' => defined('PAYSTACK_SECRET_KEY') && PAYSTACK_SECRET_KEY !== '',
  'ussd_available' => defined('PAYSTACK_SECRET_KEY') && PAYSTACK_SECRET_KEY !== '',
  'bank_transfer_available' => true,
]);
