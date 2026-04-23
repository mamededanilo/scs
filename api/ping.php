<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/ping.php';
require_login();
header('Content-Type: application/json');
$body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$url = $body['url'] ?? '';
$id  = (int)($body['id'] ?? 0);
$res = ping_url($url);
if ($id) {
    db()->prepare("UPDATE subdomains SET last_ping_status=?, last_ping_ok=?, last_ping_at=NOW() WHERE id=?")
        ->execute([$res['status'], $res['ok']?1:0, $id]);
}
echo json_encode($res);
