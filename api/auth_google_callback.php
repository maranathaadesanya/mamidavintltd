<?php
require_once __DIR__ . '/bootstrap.php';

function google_login_fail($reason) {
    error_log(json_encode([
        'event' => 'google_login_failed',
        'reason' => $reason,
        'timestamp' => gmdate('c'),
    ]));
    header('Location: /login.html?error=google');
    exit;
}

if (
    !defined('GOOGLE_CLIENT_ID') || GOOGLE_CLIENT_ID === '' ||
    !defined('GOOGLE_CLIENT_SECRET') || GOOGLE_CLIENT_SECRET === ''
) {
    google_login_fail('not_configured');
}

$state = $_GET['state'] ?? '';
$expectedState = $_SESSION['google_oauth_state'] ?? '';
unset($_SESSION['google_oauth_state']);

if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
    google_login_fail('bad_state');
}

$code = $_GET['code'] ?? '';
if ($code === '') {
    google_login_fail('no_code');
}

// Exchange the authorization code for an access token.
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
]);
$tokenRaw = curl_exec($ch);
$tokenStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$tokenResult = json_decode($tokenRaw, true);

if ($tokenStatus < 200 || $tokenStatus >= 300 || empty($tokenResult['access_token'])) {
    google_login_fail('token_exchange');
}

// Fetch the signed-in Google account's profile.
$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $tokenResult['access_token']],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
]);
$profileRaw = curl_exec($ch);
$profileStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$profile = json_decode($profileRaw, true);

if ($profileStatus < 200 || $profileStatus >= 300 || empty($profile['sub']) || empty($profile['email'])) {
    google_login_fail('userinfo');
}

if (empty($profile['email_verified'])) {
    google_login_fail('email_unverified');
}

$googleId = $profile['sub'];
$email = strtolower(trim($profile['email']));
$fullName = trim((string) ($profile['name'] ?? '')) ?: $email;

$pdo = get_db();

// 1. Already linked to this Google account.
$stmt = $pdo->prepare('SELECT id FROM users WHERE google_id = ?');
$stmt->execute([$googleId]);
$user = $stmt->fetch();

if (!$user) {
    // 2. Existing password account with the same email - link Google to it.
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $pdo->prepare('UPDATE users SET google_id = ? WHERE id = ?')->execute([$googleId, $user['id']]);
    } else {
        // 3. Brand new account. They will never use password login, but the
        // column is NOT NULL, so give it an unguessable, unusable hash.
        $randomPasswordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        $insert = $pdo->prepare('INSERT INTO users (full_name, email, phone, password_hash, google_id) VALUES (?, ?, ?, ?, ?)');
        $insert->execute([$fullName, $email, '', $randomPasswordHash, $googleId]);
        $user = ['id' => (int) $pdo->lastInsertId()];
    }
}

$_SESSION['user_id'] = (int) $user['id'];
session_regenerate_id(true);

$next = $_SESSION['google_oauth_next'] ?? 'profile.html';
unset($_SESSION['google_oauth_next']);
header('Location: /' . $next);
exit;
