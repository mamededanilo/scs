<?php
session_start();
$rootCfg = dirname(__DIR__) . '/config.php';
if (file_exists($rootCfg) && empty($_GET['force'])) {
    die('Sistema já instalado. Remova config.php para reinstalar.');
}
$step = (int)($_GET['step'] ?? 1);
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        $cfg = [
            'driver'   => $_POST['driver'] === 'mysql' ? 'mysql' : 'pgsql',
            'host'     => $_POST['host'] ?: 'localhost',
            'port'     => (int)($_POST['port'] ?: ($_POST['driver']==='mysql'?3306:5432)),
            'database' => $_POST['database'],
            'username' => $_POST['username'],
            'password' => $_POST['password'],
            'app_key'  => bin2hex(random_bytes(16)),
        ];
        try {
            $dsn = $cfg['driver']==='pgsql'
                ? "pgsql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']}"
                : "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Schema
            if ($cfg['driver'] === 'pgsql') {
                $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                    id SERIAL PRIMARY KEY,
                    username VARCHAR(64) UNIQUE NOT NULL,
                    password_hash TEXT NOT NULL,
                    role VARCHAR(16) NOT NULL DEFAULT 'padrao',
                    created_at TIMESTAMP DEFAULT NOW()
                )");
                $pdo->exec("CREATE TABLE IF NOT EXISTS subdomains (
                    id SERIAL PRIMARY KEY,
                    system_name VARCHAR(255) NOT NULL,
                    url TEXT NOT NULL,
                    username VARCHAR(255),
                    password TEXT,
                    observation TEXT,
                    last_ping_status INT,
                    last_ping_ok BOOLEAN,
                    last_ping_at TIMESTAMP,
                    created_at TIMESTAMP DEFAULT NOW(),
                    updated_at TIMESTAMP DEFAULT NOW()
                )");
            } else {
                $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(64) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    role VARCHAR(16) NOT NULL DEFAULT 'padrao',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB");
                $pdo->exec("CREATE TABLE IF NOT EXISTS subdomains (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    system_name VARCHAR(255) NOT NULL,
                    url TEXT NOT NULL,
                    username VARCHAR(255),
                    password TEXT,
                    observation TEXT,
                    last_ping_status INT,
                    last_ping_ok TINYINT(1),
                    last_ping_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB");
            }

            // Admin padrão
            $st = $pdo->prepare("SELECT id FROM users WHERE username='admin'");
            $st->execute();
            if (!$st->fetch()) {
                $hash = password_hash('admin', PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES ('admin', ?, 'admin')")
                    ->execute([$hash]);
            }

            $php = "<?php\nreturn " . var_export($cfg, true) . ";\n";
            if (!file_put_contents($rootCfg, $php)) {
                throw new Exception("Não foi possível escrever config.php (verifique permissões)");
            }
            header('Location: index.php?step=3'); exit;
        } catch (Throwable $e) {
            $err = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html><html lang="pt-br"><head>
<meta charset="UTF-8"><title>Instalador SCS</title>
<style>
body{font-family:system-ui;max-width:640px;margin:40px auto;padding:0 20px;background:#0f172a;color:#e2e8f0}
.card{background:#1e293b;padding:32px;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.3)}
h1{margin-top:0;color:#60a5fa}
label{display:block;margin:14px 0 4px;font-size:14px;font-weight:500}
input,select{width:100%;padding:10px;background:#0f172a;border:1px solid #334155;border-radius:6px;color:#e2e8f0;font-size:14px;box-sizing:border-box}
button{margin-top:20px;padding:12px 24px;background:#3b82f6;color:white;border:none;border-radius:6px;font-size:15px;cursor:pointer;font-weight:500}
button:hover{background:#2563eb}
.err{background:#7f1d1d;padding:12px;border-radius:6px;margin:12px 0}
.ok{background:#14532d;padding:12px;border-radius:6px;margin:12px 0}
.steps{display:flex;gap:8px;margin-bottom:24px}
.step{flex:1;padding:8px;text-align:center;background:#334155;border-radius:6px;font-size:13px}
.step.active{background:#3b82f6}
ul{line-height:1.8}
code{background:#0f172a;padding:2px 6px;border-radius:4px;color:#fbbf24}
</style></head><body>
<div class="card">
<h1>🔧 Instalador SCS</h1>
<div class="steps">
  <div class="step <?= $step==1?'active':'' ?>">1. Requisitos</div>
  <div class="step <?= $step==2?'active':'' ?>">2. Banco</div>
  <div class="step <?= $step==3?'active':'' ?>">3. Pronto</div>
</div>

<?php if ($err): ?><div class="err">❌ <?= htmlspecialchars($err) ?></div><?php endif; ?>

<?php if ($step === 1):
    $checks = [
        'PHP >= 7.4' => version_compare(PHP_VERSION, '7.4', '>='),
        'PDO'        => extension_loaded('pdo'),
        'PDO PostgreSQL ou MySQL' => extension_loaded('pdo_pgsql') || extension_loaded('pdo_mysql'),
        'cURL'       => extension_loaded('curl'),
        'config.php gravável' => is_writable(dirname(__DIR__)),
    ];
?>
<h3>Verificação de requisitos</h3>
<ul>
<?php foreach ($checks as $name => $ok): ?>
  <li><?= $ok ? '✅' : '❌' ?> <?= $name ?></li>
<?php endforeach; ?>
</ul>
<?php if (!in_array(false, $checks, true)): ?>
  <a href="?step=2"><button>Continuar →</button></a>
<?php else: ?>
  <p style="color:#fca5a5">Resolva os itens acima antes de continuar.</p>
<?php endif; ?>

<?php elseif ($step === 2): ?>
<h3>Configuração do banco de dados</h3>
<form method="post">
  <label>Tipo de banco</label>
  <select name="driver" id="driver" onchange="document.getElementById('port').value=this.value==='mysql'?3306:5432">
    <?php if (extension_loaded('pdo_pgsql')): ?><option value="pgsql">PostgreSQL</option><?php endif; ?>
    <?php if (extension_loaded('pdo_mysql')): ?><option value="mysql">MySQL / MariaDB</option><?php endif; ?>
  </select>
  <label>Host</label><input name="host" value="localhost" required>
  <label>Porta</label><input name="port" id="port" value="5432" required>
  <label>Nome do banco (deve existir)</label><input name="database" required>
  <label>Usuário</label><input name="username" required>
  <label>Senha</label><input name="password" type="password">
  <button type="submit">Instalar →</button>
</form>

<?php elseif ($step === 3): ?>
<div class="ok">✅ Instalação concluída!</div>
<p>Credenciais padrão:</p>
<ul>
  <li>Usuário: <code>admin</code></li>
  <li>Senha: <code>admin</code></li>
</ul>
<p style="color:#fbbf24">⚠️ Altere a senha do admin após o primeiro login e <strong>remova a pasta <code>install/</code></strong> do servidor.</p>
<a href="/login.php"><button>Ir para o login</button></a>
<?php endif; ?>
</div>
</body></html>
