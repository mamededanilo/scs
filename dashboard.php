<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/layout.php';

try {
    $db = db();
    
    // DEBUG 1: Vamos ver em qual banco o PHP realmente está logado
    $stDb = $db->query("SELECT current_database(), current_user");
    $dbInfo = $stDb->fetch(PDO::FETCH_ASSOC);
    $currentDb = $dbInfo['current_database'];

    // DEBUG 2: Tentar ler a tabela usando aspas duplas (padrão rigoroso do Postgres)
    $st = $db->prepare('SELECT * FROM "subdomains" ORDER BY "system_name" ASC');
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    $total = count($rows);

} catch (Exception $e) {
    die("Erro fatal: " . $e->getMessage());
}
?>

<div class="page-head">
    <h1>Dashboard (Debug Mode)</h1>
    <p>Conectado ao banco: <strong><?= $currentDb ?></strong> | Registros encontrados: <strong><?= $total ?></strong></p>
</div>

<table class="table">
    <thead>
        <tr><th>Sistema</th><th>URL</th><th>Ações</th></tr>
    </thead>
    <tbody>
        <?php if ($total > 0): ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['system_name']) ?></td>
                    <td><?= htmlspecialchars($r['url']) ?></td>
                    <td><a href="subdomain_form.php?id=<?= $r['id'] ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3">O banco está vazio ou a tabela não existe neste database.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>
