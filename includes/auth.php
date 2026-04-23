<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

function current_user() {
    if (empty($_SESSION['user_id'])) return null;
    $st = db()->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $st->execute([$_SESSION['user_id']]);
    return $st->fetch() ?: null;
}

function require_login() {
    if (!current_user()) { header('Location: /login.php'); exit; }
}

function require_role(array $roles) {
    $u = current_user();
    if (!$u || !in_array($u['role'], $roles, true)) {
        http_response_code(403);
        die('Acesso negado.');
    }
}

function can_edit(): bool {
    $u = current_user();
    return $u && in_array($u['role'], ['admin','operador'], true);
}
function is_admin(): bool {
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

function login($username, $password): bool {
    $st = db()->prepare("SELECT * FROM users WHERE username = ?");
    $st->execute([$username]);
    $u = $st->fetch();
    if ($u && password_verify($password, $u['password_hash'])) {
        $_SESSION['user_id'] = $u['id'];
        return true;
    }
    return false;
}

function logout() {
    $_SESSION = [];
    session_destroy();
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_check() {
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
        http_response_code(400); die('CSRF inválido');
    }
}
