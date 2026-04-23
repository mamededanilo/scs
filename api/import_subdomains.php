<?php
require_once __DIR__ . '/../includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    // Validação de segurança CSRF
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== csrf_token()) {
        die("Erro de segurança: CSRF inválido.");
    }

    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");
    $db = db();

    // Lê a primeira linha (cabeçalho) e detecta se o separador é vírgula ou ponto-e-vírgula
    $header = fgetcsv($handle, 1000, ",");
    if (count($header) < 2) { 
        rewind($handle);
        $header = fgetcsv($handle, 1000, ";");
        $separator = ";";
    } else {
        $separator = ",";
    }

    $count = 0;
    while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
        if (empty($data[0])) continue; // Pula linhas vazias

        try {
            $st = $db->prepare("INSERT INTO subdomains 
                (system_name, url, username, password, observation, last_ping_ok, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, false, NOW(), NOW())");
            
            $st->execute([
                $data[0] ?? '', // system_name
                $data[1] ?? '', // url
                $data[2] ?? '', // username
                $data[3] ?? '', // password
                $data[4] ?? ''  // observation
            ]);
            $count++;
        } catch (Exception $e) {
            continue; // Pula erros de inserção
        }
    }

    fclose($handle);
    header("Location: ../dashboard.php?msg=Sucesso: $count sistemas importados!");
    exit;
}
