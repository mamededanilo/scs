<?php
require_once __DIR__ . '/includes/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: /dashboard.php'); exit;
    }
    $err = 'Credenciais inválidas';
}
?>
<!DOCTYPE html><html lang="pt-br"><head>
<meta charset="UTF-8"><title>Login - SCS</title>
<link rel="stylesheet" href="/assets/css/app.css">
</head><body class="login-body">
<form method="post" class="login-card">
  <h1>SCS</h1>
  <p class="muted">Sistema de Cadastro de Subdomínios</p>
  <?php if ($err): ?><div class="err"><?= $err ?></div><?php endif; ?>
  <label>Usuário</label>
  <input name="username" required autofocus>
  <label>Senha</label>
  <input name="password" type="password" required>
  <button type="submit" class="btn btn-primary">Entrar</button>
</form>
</body></html>
