<?php
require_once __DIR__ . '/auth.php';
require_login();
$u = current_user();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'SCS') ?></title>
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<header class="topbar">
  <div class="brand"><a href="/dashboard.php">SCS</a></div>
  <nav>
    <a href="/dashboard.php">Dashboard</a>
    <?php if (can_edit()): ?><a href="/subdomain_form.php">Novo Sistema</a><?php endif; ?>
    <?php if (is_admin()): ?><a href="/users.php">Usuários</a><?php endif; ?>
  </nav>
  <div class="user">
    <span><?= htmlspecialchars($u['username']) ?> (<?= $u['role'] ?>)</span>
    <a href="/logout.php" class="btn btn-ghost">Sair</a>
  </div>
</header>
<main class="container">
