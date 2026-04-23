<?php
if (!file_exists(__DIR__ . '/config.php')) { header('Location: install/index.php'); exit; }
header('Location: dashboard.php');
