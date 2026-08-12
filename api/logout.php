<?php
require_once __DIR__ . '/bootstrap.php';

$_SESSION = [];
session_destroy();

echo json_encode(['success' => true]);
