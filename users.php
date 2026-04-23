<?php
$pageTitle = 'Usuários';
require_once __DIR__ . '/includes/layout.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $action = $_POST['action'];
    if ($action==='create') {
        $st = db()->prepare("INSERT INTO users (username,password_hash,role) VALUES (?,?,?)");
        $st->execute([$_POST['username'], password_hash($_POST['password'],PASSWORD_DEFAULT), $_POST['role']]);
    } elseif ($action==='update_role') {
        db()->prepare("UPDATE users SET role=? WHERE id=?")->execute([$_POST['role'],$_POST['id']]);
    } elseif ($action==='reset_pwd') {
        db()->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([password_hash($_POST['password'],PASSWORD_DEFAULT),$_POST['id']]);
    } elseif ($action==='delete') {
        db()->prepare("DELETE FROM users WHERE id=? AND username<>'admin'")->execute([$_POST['id']]);
    }
    header('Location: /users.php'); exit;
}
$users = db()->query("SELECT * FROM users ORDER BY username")->fetchAll();
?>
<div class="page-head"><h1>Usuários</h1></div>

<details class="form-card"><summary>+ Novo usuário</summary>
<form method="post" class="form">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <input type="hidden" name="action" value="create">
  <label>Usuário</label><input name="username" required>
  <label>Senha</label><input name="password" type="password" required>
  <label>Perfil</label>
  <select name="role"><option value="padrao">Padrão</option><option value="operador">Operador</option><option value="admin">Admin</option></select>
  <button class="btn btn-primary">Criar</button>
</form></details>

<table class="table">
<thead><tr><th>Usuário</th><th>Perfil</th><th>Ações</th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?>
<tr>
  <td><?= htmlspecialchars($u['username']) ?></td>
  <td>
    <form method="post" style="display:inline">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="update_role">
      <input type="hidden" name="id" value="<?= $u['id'] ?>">
      <select name="role" onchange="this.form.submit()" <?= $u['username']==='admin'?'disabled':'' ?>>
        <?php foreach (['padrao','operador','admin'] as $r): ?>
          <option value="<?= $r ?>" <?= $u['role']==$r?'selected':'' ?>><?= $r ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </td>
  <td>
    <form method="post" style="display:inline" onsubmit="return confirm('Resetar senha?')">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="reset_pwd">
      <input type="hidden" name="id" value="<?= $u['id'] ?>">
      <input name="password" placeholder="nova senha" required>
      <button class="btn-link">Resetar</button>
    </form>
    <?php if ($u['username']!=='admin'): ?>
    <form method="post" style="display:inline" onsubmit="return confirm('Excluir?')">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" value="<?= $u['id'] ?>">
      <button class="btn-link danger">Excluir</button>
    </form>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php require __DIR__ . '/includes/footer.php';
