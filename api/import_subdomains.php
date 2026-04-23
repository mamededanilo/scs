<?php
require_once __DIR__ . '/../includes/layout.php'; // Ajuste o caminho conforme sua estrutura

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    // Validação de CSRF
    if ($_POST['csrf'] !== $_SESSION['csrf_token']) {
        die("CSRF inválido");
    }

    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");
    $db = db();

    // Pular a primeira linha (cabeçalho) se houver
    fgetcsv($handle, 1000, ",");

    $importados = 0;

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Mapeamento das colunas do CSV:
        // $data[0] = nome, $data[1] = url, $data[2] = usuario, $data[3] = senha, $data[4] = observacao
        try {
            $st = $db->prepare("INSERT INTO subdomains (system_name, url, username, password, observation, created_at, updated_at) 
                                VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $st->execute([
                $data[0], // system_name
                $data[1], // url
                $data[2], // username
                $data[3], // password
                $data[4]  // observation
            ]);
            $importados++;
        } catch (Exception $e) {
            // Opcional: logar erros de linhas específicas
            continue;
        }
    }

    fclose($handle);
    header("Location: ../dashboard.php?msg=Importados $importados sistemas com sucesso!");
    exit;
}