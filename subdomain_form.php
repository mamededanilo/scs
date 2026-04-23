<?php
$pageTitle = 'Sistema';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/ping.php';
require_role(['admin','operador']);

$id = (int)($_GET['id'] ?? 0);
$row = ['system_name'=>'','url'=>'','username'=>'','password'=>'','observation'=>''];
if ($id) {
    $st = db()->prepare("SELECT * FROM subdomains WHERE id=?");
    $st->execute([$id]); $row = $st->fetch() ?: $row;
}
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $data = [
        'system_name' => trim($_POST['system_name']),
        'url'         => trim($_POST['url']),
        'username'    => $_POST['username'] ?: null,
        'password'    => $_POST['password'] ?: null,
        'observation' => $_POST['observation'] ?: null,
    ];
    $ping = ping_url($data['url']);
    if ($id) {
        $st = db()->prepare("UPDATE subdomains SET system_name=?,url=?,username=?,password=?,observation=?,last_ping_status=?,last_ping_ok=?,last_ping_at=NOW(),updated_at=NOW() WHERE id=?");
        $st->execute([$data['system_name'],$data['url'],$data['username'],$data['password'],$data['observation'],$ping['status'],$ping['ok']?1:0,$id]);
    } else {
        $st = db()->prepare("INSERT INTO subdomains (system_name,url,username,password,observation,last_ping_status,last_ping_ok,last_ping_at) VALUES (?,?,?,?,?,?,?,NOW())");
        $st->execute([$data['system_name'],$data['url'],$data['username'],$data['password'],$data['observation'],$ping['status'],$ping['ok']?1:0]);
    }
    header('Location: /dashboard.php'); exit;
}
?>
<div class="page-head"><h1><?= $id ? 'Editar' : 'Novo' ?> Sistema</h1></div>
<form method="post" class="form">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <label>Nome do sistema *</label>
  <input name="system_name" required value="<?= htmlspecialchars($row['system_name']) ?>">
  <label>URL *</label>
  <input name="url" required value="<?= htmlspecialchars($row['url']) ?>">
  <label>Usuário</label>
  <input name="username" value="<?= htmlspecialchars($row['username']??'') ?>">
  <label>Senha</label>
  <div class="pwd-wrap">
    <input id="pw" name="password" type="password" value="<?= htmlspecialchars($row['password']??'') ?>">
    <button type="button" onclick="var p=document.getElementById('pw');p.type=p.type==='password'?'text':'password'">👁</button>
  </div>
  <label>Observação</label>
  <textarea name="observation"><?= htmlspecialchars($row['observation']??'') ?></textarea>
  <div class="actions">
    <a href="/dashboard.php" class="btn">Cancelar</a>
    <button class="btn btn-primary">Salvar (com ping)</button>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php';
