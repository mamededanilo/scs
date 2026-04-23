<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/layout.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $db = db();
    $sql = "SELECT * FROM subdomains";
    if ($q !== '') {
        $sql .= " WHERE system_name ILIKE ? OR url ILIKE ? OR observation ILIKE ?";
        $st = $db->prepare($sql);
        $st->execute(["%$q%", "%$q%", "%$q%"]);
    } else {
        $st = $db->query($sql);
    }
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    $rows = [];
}
?>

<div class="page-head">
    <h1>Dashboard</h1>
    <a href="subdomain_form.php" class="btn btn-primary">+ Novo Sistema</a>
</div>

<form method="get" class="filters" style="margin-bottom: 20px;">
    <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar sistemas...">
    <button class="btn">Buscar</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Sistema / Observação</th>
            <th>URL</th>
            <th>Usuário</th>
            <th>Senha</th>
            <th>Status</th>
            <th style="width: 120px;">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="6" class="empty">Nenhum registro encontrado no banco SCS.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($r['system_name'] ?? 'Sem nome') ?></strong>
                        <?php if (!empty($r['observation'])): ?>
                            <div style="font-size: 0.85em; color: #555; margin-top: 5px; background: #f9f9f9; padding: 2px 5px; border-radius: 3px;">
                                📝 <?= htmlspecialchars($r['observation']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank">
                            <?= htmlspecialchars($r['url']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($r['username'] ?? '—') ?></td>
                    <td>
                        <?php if (!empty($r['password'])): ?>
                            <span title="<?= htmlspecialchars($r['password']) ?>" style="cursor: pointer;">••••••</span>
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
                        
                        <br> <form action="api/delete_subdomain.php" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este sistema?');" style="display:inline;">
                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn-link danger" style="color: #dc3545; border: none; background: none; padding: 0; font-size: inherit; cursor: pointer; text-decoration: underline;">
                            Remover
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>
