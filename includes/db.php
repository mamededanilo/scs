<?php
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $cfgFile = __DIR__ . '/../config.php';
    if (!file_exists($cfgFile)) {
        header('Location: install/index.php'); exit;
    }
    $c = require $cfgFile;
    if ($c['driver'] === 'pgsql') {
        $dsn = "pgsql:host={$c['host']};port={$c['port']};dbname={$c['database']}";
    } else {
        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4";
    }
    $pdo = new PDO($dsn, $c['username'], $c['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

function driver(): string {
    $c = require __DIR__ . '/../config.php';
    return $c['driver'];
}
