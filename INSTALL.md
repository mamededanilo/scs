# SCS — Sistema de Cadastro de Subdomínios (PHP)

Versão PHP standalone do SCS, compatível com **PostgreSQL**, **MySQL** e **MariaDB**.

## ✅ Requisitos

- PHP 7.4 ou superior (8.x recomendado)
- Extensões PHP: `pdo`, `pdo_pgsql` **ou** `pdo_mysql`, `curl`
- Servidor web (Apache, Nginx ou `php -S`)
- Um banco de dados criado e vazio (PostgreSQL, MySQL ou MariaDB)
- Permissão de escrita na pasta raiz (para gerar `config.php`)

## 🚀 Instalação rápida

### 1. Baixe e descompacte

Coloque os arquivos em algum diretório servido pelo seu servidor web, por exemplo:
- Apache/Nginx: `/var/www/html/scs/`
- XAMPP: `C:\xampp\htdocs\scs\`

### 2. Crie o banco de dados (vazio)

**PostgreSQL:**
```sql
CREATE DATABASE scs;
```

**MySQL/MariaDB:**
```sql
CREATE DATABASE scs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

> Não precisa criar tabelas — o instalador faz isso.

### 3. Acesse pelo navegador

Abra: `http://seu-servidor/scs/install/index.php`

O instalador irá:
1. **Verificar requisitos** (PHP, extensões, permissões)
2. **Pedir os dados de conexão** com o banco
3. **Criar tabelas** e o usuário admin padrão
4. **Gerar o `config.php`** automaticamente

### 4. Faça login

Acesse `http://seu-servidor/scs/login.php` com:
- Usuário: `admin`
- Senha: `admin`

### 5. ⚠️ Pós-instalação (importante!)

1. **Altere a senha do admin** em "Usuários"
2. **Apague a pasta `install/`** do servidor
3. Garanta que `config.php` **não tem permissão pública** (o `.htaccess` já bloqueia no Apache; no Nginx adicione `location ~ /config\.php { deny all; }`)

## 🔧 Teste rápido com PHP embutido

Se quiser testar sem Apache/Nginx:
```bash
cd scs/
php -S localhost:8000
```
Depois acesse `http://localhost:8000/install/index.php`.

## 👥 Perfis de acesso

| Perfil | Visualizar | Cadastrar/Editar | Excluir / Gerenciar usuários |
|---|---|---|---|
| `admin` | ✅ | ✅ | ✅ |
| `operador` | ✅ | ✅ | ❌ |
| `padrao` | ✅ | ❌ | ❌ |

## 📁 Estrutura

```
scs/
├── install/          ← Instalador (apague após instalar)
├── api/              ← Endpoints (ping, delete)
├── assets/           ← CSS e JS
├── includes/         ← Núcleo (db, auth, ping, layout)
├── pages: dashboard.php, subdomain_form.php, users.php, login.php
├── config.php        ← Gerado pelo instalador (NÃO versionar)
└── .htaccess         ← Bloqueia acesso direto a config.php
```

## ❓ Problemas comuns

- **"config.php não gravável"**: dê permissão de escrita à pasta raiz (`chmod 755` e dono = usuário do PHP).
- **"could not find driver"**: instale `php-pgsql` ou `php-mysql` (`apt install php-pgsql` no Debian/Ubuntu).
- **Ping sempre falha**: verifique se o servidor tem saída HTTP/HTTPS para a internet.
