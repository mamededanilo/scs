<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/layout.php';

$perPage = in_array((int)($_GET['per'] ?? 10), [10, 50, 100]) ? (int)$_GET['per'] : 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$q       = isset($_GET['q']) ? trim($_GET['q']) : '';
$offset  = ($page - 1) * $perPage;

$where = "";
$params = [];

if ($q !== '') {
    // Voltando para a sintaxe sem aspas que funcionou no seu psql
    $where = "WHERE system_name ILIKE ? OR url ILIKE ? OR COALESCE(observation,'') ILIKE ?";
    $params = ["%$q%", "%$q%", "%$q%"];
}

try {
    $db = db();
    
    // Contagem Total
    $stTotal = $db->prepare("SELECT COUNT(*) AS c FROM subdomains $where");
    $stTotal->execute($params);
    $resTotal = $stTotal->fetch(PDO::FETCH_ASSOC);
    $total = (int)($resTotal['c'] ?? 0);

    // Consulta de todos os campos
    $sql = "SELECT * FROM subdomains $where ORDER BY system_name ASC LIMIT $perPage OFFSET $offset";
    $st = $db->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    $pages = max(1, (int)ceil($total / $perPage));

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
    $rows = [];
    $pages = 1;
}
?>

<div class="page-head">
    <h1>Dashboard</h1>
    <?php if (can_edit()): ?>
        <a href="subdomain_form.php" class="btn btn-primary">+ Novo Sistema</a>
    <?php endif; ?>
</div>

<form method="get" class="filters">
    <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar...">
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
        <th>Sistema</th>
        <th>URL</th>
        <th>Usuário</th>
        <th>Senha</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>
</thead>
<tbody>
<?php if (empty($rows)): ?>
    <tr><td colspan="6" class="empty">Nenhum registro encontrado (Total no banco: <?= $total ?>)</td></tr>
<?php else: ?>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td>
            <strong><?= htmlspecialchars($r['system_name'] ?? '') ?></strong>
            <?php if (!empty($r['observation'])): ?>
                <div style="font-size: 0.8em; color: #666; margin-top: 4px;">
                    <?= htmlspecialchars($r['observation']) ?>
                </div>
            <?php endif; ?>
        </td>
        <td><a href="<?= htmlspecialchars($r['url'] ?? '') ?>" target="_blank"><?= htmlspecialchars($r['url'] ?? '') ?></a></td>
        <td><?= htmlspecialchars($r['username'] ?? '—') ?></td>
        <td>
            <?php if (!empty($r['password'])): ?>
                <span class="pwd" style="cursor:help" title="<?= htmlspecialchars($r['password']) ?>">••••••</span>
            <?php else: ?>
                —
            <?php endif; ?>
        </td>
        <td>
            <span class="status <?= ($r['last_ping_ok'] ?? false) ? 'ok' : 'fail' ?>">
                <?= ($r['last_ping_ok'] ?? false) ? 'Online' : 'Offline' ?>
            </span>
        </td>
        <td>
            <a href="subdomain_form.php?id=<?= $r['id'] ?>" class="btn-link">Editar</a>
        </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>

<div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?page=<?= $i ?>&per=<?= $perPage ?>&q=<?= urlencode($q) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
