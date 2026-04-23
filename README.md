# scs
📦 Como instalar (resumo)

    Descompacte o ZIP no seu servidor web (ex.: /var/www/html/scs/ ou htdocs/scs/).
    Crie um banco vazio no PostgreSQL/MySQL/MariaDB:
        PG: CREATE DATABASE scs;
        MySQL: CREATE DATABASE scs CHARACTER SET utf8mb4;
    Acesse no navegador: http://seu-servidor/scs/install/index.php
    O instalador faz tudo automaticamente:
        Verifica requisitos (PHP 7.4+, PDO, cURL)
        Pede dados do banco
        Cria tabelas e o usuário admin / admin
        Gera o config.php
    Faça login em /login.php com admin / admin.
    Pós-instalação: troque a senha do admin e apague a pasta install/.

Teste rápido sem Apache/Nginx

cd scs-php && php -S localhost:8000

Depois acesse http://localhost:8000/install/index.php.
✨ Funcionalidades incluídas

    Login com sessão segura + CSRF
    Dashboard com busca, paginação 10/50/100
    Cadastro/edição de sistemas com ping automático ao salvar + botão manual (HTTP 200 ou "Inacessível")
    Senhas mascaradas com botão mostrar/ocultar
    Gerenciamento de usuários com perfis admin / operador / padrão
    Instalador web com verificação de requisitos
    .htaccess bloqueando acesso ao config.php

Instruções completas estão no INSTALL.md dentro do ZIP.
