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
    
    <style>
        /* Configuração de Cores (CSS Variables) */
        :root {
            --bg-body: #f4f7f6;
            --bg-container: #ffffff;
            --text-main: #333333;
            --topbar-bg: #0b132b; /* Azul marinha escuro original */
            --border-color: #dddddd;
            --card-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Definição do Modo Dark */
        [data-theme="dark"] {
            --bg-body: #0b0e14;    /* Fundo estilo terminal/cyber */
            --bg-container: #161b22;
            --text-main: #c9d1d9;
            --topbar-bg: #010409;
            --border-color: #30363d;
            --card-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            transition: background 0.3s, color 0.3s;
            margin: 0;
            font-family: sans-serif;
            /* Se quiser uma imagem de fundo fixa, descomente a linha abaixo: */
            /* background-image: url('/assets/img/background.jpg'); background-size: cover; */
        }

        .topbar { background-color: var(--topbar-bg) !important; }
        .container { 
            background-color: var(--bg-container); 
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
        }
        
        /* Ajuste para tabelas no modo dark */
        .table { color: var(--text-main); }
        .table th { border-bottom: 2px solid var(--border-color); }
        .table td { border-bottom: 1px solid var(--border-color); }

        .theme-toggle {
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 20px;
            border: 1px solid #5bc0de;
            background: transparent;
            color: #5bc0de;
            margin-right: 15px;
            font-size: 0.8em;
        }
    </style>
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
    <button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙 Modo Dark</button>
    
    <span><?= htmlspecialchars($u['username']) ?> (<?= $u['role'] ?>)</span>
    <a href="/logout.php" class="btn btn-ghost">Sair</a>
  </div>
</header>

<script>
    // Função para trocar o tema
    function toggleTheme() {
        const html = document.documentElement;
        const btn = document.getElementById('themeBtn');
        
        if (html.getAttribute('data-theme') === 'dark') {
            html.removeAttribute('data-theme');
            btn.innerHTML = '🌙 Modo Dark';
            localStorage.setItem('scs-theme', 'light');
        } else {
            html.setAttribute('data-theme', 'dark');
            btn.innerHTML = '☀️ Modo Claro';
            localStorage.setItem('scs-theme', 'dark');
        }
    }

    // Aplica o tema salvo ao carregar a página
    if (localStorage.getItem('scs-theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.getElementById('themeBtn').innerHTML = '☀️ Modo Claro';
    }
</script>

<main class="container">
