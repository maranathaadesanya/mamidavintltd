<?php
require_once __DIR__ . '/bootstrap.php';

if (!defined('GOOGLE_CLIENT_ID') || GOOGLE_CLIENT_ID === '') {
    http_response_code(503);
    echo 'Google sign-in is not configured yet.';
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

// Where to send the user once login completes.
$next = $_GET['next'] ?? 'profile.html';
$allowedNext = ['profile.html', 'cart.html', 'payments.html'];
$_SESSION['google_oauth_next'] = in_array($next, $allowedNext, true) ? $next : 'profile.html';

$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'prompt' => 'select_account',
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
