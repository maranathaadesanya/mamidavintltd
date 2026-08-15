<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/config.local.php';

function get_db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function json_input() {
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function require_login() {
    if (!current_user_id()) {
        http_response_code(401);
        echo json_encode(['error' => 'Not logged in.']);
        exit;
    }
}

function is_admin() {
    $userId = current_user_id();
    if (!$userId) {
        return false;
    }
    $stmt = get_db()->prepare('SELECT is_admin FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user && (int) $user['is_admin'] === 1;
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        echo json_encode(['error' => 'You do not have access to this.']);
        exit;
    }
}
