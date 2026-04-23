<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
csrf_check();
db()->prepare("DELETE FROM subdomains WHERE id=?")->execute([(int)$_POST['id']]);
header('Location: /dashboard.php');
