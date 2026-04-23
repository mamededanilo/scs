<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/layout.php';

// Configurações de Paginação e Filtro
$perPage = in_array((int)($_GET['per'] ?? 10), [10, 50, 100]) ? (int)$_GET['per'] : 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$q       = isset($_GET['q']) ? trim($_GET['q']) : '';
$offset  = ($page - 1) * $perPage;

$where = '';
$params = [];

if ($q !== '') {
    // Uso do ILIKE para PostgreSQL (Cyber Warfare Lab)
    $where = "WHERE system_name ILIKE ? OR url ILIKE ? OR COALESCE(observation,'') ILIKE ?";
    $params = ["%$q%", "%$q%", "%$q%"];
}

try {
    $db = db(); // Conexão via config.php
    
    // CORREÇÃO DO TOTAL: Pega o número real de linhas para a paginação
    $stTotal = $db->prepare("SELECT COUNT(*) AS c FROM subdomains $where");
    $stTotal->execute($params);
    $totalRes = $stTotal->fetch();
    $total = (int)($totalRes['c'] ?? 0);

    // CONSULTA DOS DADOS: Busca os registros ativos no banco
    $st = $db->prepare("SELECT * FROM subdomains $where ORDER BY system_name ASC LIMIT $perPage OFFSET $offset");
    $st->execute($params);
    $rows = $st->fetchAll();
    
    $pages = max(1, (int)ceil($total / $perPage));

} catch (Exception $e) {
    echo "Erro na consulta: " . $e->getMessage();
    $rows = [];
    $pages = 1;
}
?>

<div class="page-head">
  <h1>Dashboard</h1>
  <?php if (can_edit()): ?><a href="subdomain_form.php" class="btn btn-primary">+ Novo Sistema</a><?php endif; ?>
</div>

<form method="get" class="filters">
  <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nome, URL, observação...">
  <select name="per" onchange="this.form.submit()">
    <?php foreach ([10, 50, 100] as $n): ?>
      <option value="<?= $n ?>" <?= $perPage == $n ? 'selected' : '' ?>><?= $n ?> por página</option>
    <?php endforeach; ?>
  </select>
  <button class="btn">Buscar</button>
</form>

<table class="table">
<thead>
  <tr>
    <th>Sistema</th><th>URL</th><th>Usuário</th><th>Senha</th><th>Status</th><th>Ações</th>
  </tr>
</thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr data-id="<?= $r['id'] ?>">
  <td><?= htmlspecialchars($r['system_name']) ?></td>
  <td><a href="<?= htmlspecialchars($r['url']) ?>" target="_blank"><?= htmlspecialchars($r['url']) ?></a></td>
  <td><?= htmlspecialchars($r['username'] ?? '') ?></td>
  <td>
    <?php if ($r['password']): ?>
      <span class="pwd" data-pwd="<?= htmlspecialchars($r['password']) ?>">••••••</span>
      <button type="button" class="btn-icon toggle-pwd">👁</button>
    <?php endif; ?>
  </td>
  <td>
    <span class="status <?= $r['last_ping_ok'] ? 'ok' : ($r['last_ping_at'] ? 'fail' : 'pending') ?>">
      <?= $r['last_ping_ok'] ? 'HTTP ' . $r['last_ping_status'] : ($r['last_ping_at'] ? 'Inacessível' : '—') ?>
    </span>
    <button type="button" class="btn-icon ping-btn" data-url="<?= htmlspecialchars($r['url']) ?>" data-id="<?= $r['id'] ?>">↻</button>
  </td>
  <td>
    <?php if (can_edit()): ?><a href="subdomain_form.php?id=<?= $r['id'] ?>" class="btn-link">Editar</a><?php endif; ?>
    <?php if (is_admin()): ?>
      <form method="post" action="api/delete_subdomain.php" style="display:inline" onsubmit="return confirm('Excluir?')">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button class="btn-link danger">Excluir</button>
      </form>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="6" class="empty">Nenhum registro encontrado no banco SCS.</td></tr><?php endif; ?>
</tbody>
</table>

<div class="pagination">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a href="?page=<?= $i ?>&per=<?= $perPage ?>&q=<?= urlencode($q) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>