# Sistema de Gestão para Vendedores Autônomos de Alimentos

Sistema web responsivo desenvolvido para vendedores autônomos de alimentos (doces, trufas, salgados, bolos). O objetivo é centralizar a gestão de produtos, insumos, fichas técnicas, estoque, pedidos, cupons e finanças.

## 🏗️ Arquitetura do Sistema

| Camada | Tecnologia | Observação |
|--------|------------|------------|
| Backend | PHP 8+ (OOP, MVC leve) | Rotas controladas por $_GET ou micro-roteador. Sessões seguras. |
| Frontend | HTML5 + CSS3 + JS (Vanilla) | Sem frameworks pesados. CSS Variables, Flexbox/Grid, fetch() para AJAX. |
| Banco de Dados | MySQL 8 / MariaDB | Schema compartilhado com vendedor_id (multi-tenant lógico). |
| Mobile | PWA (manifest.json + sw.js) | Cache estático, instalação nativa, pronto para empacotar como app. |

## 📋 Requisitos

- PHP 8.0 ou superior
- MySQL 8.0 ou MariaDB 10.4+
- Composer (opcional, para OAuth do Google)
- Servidor web (Apache/Nginx) com mod_rewrite

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone <repository-url>
cd workspace
```

### 2. Configure o banco de dados

Crie um banco de dados e importe o schema:

```bash
mysql -u root -p -e "CREATE DATABASE gestao_vendedores CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p gestao_vendedores < config/database.sql
```

### 3. Configure as variáveis de ambiente

Copie o arquivo de exemplo e ajuste as configurações:

```bash
cp .env.example .env
```

Edite o arquivo `.env` com suas credenciais:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=gestao_vendedores
DB_USER=root
DB_PASS=sua_senha

APP_URL=http://localhost
APP_ENV=development

GOOGLE_CLIENT_ID=seu_client_id
GOOGLE_CLIENT_SECRET=seu_client_secret
```

### 4. Configure o servidor web

#### Apache

Crie um arquivo `.htaccess` na pasta `public/`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

#### Nginx

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/workspace/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### 5. Acesse o sistema

- **URL:** http://localhost
- **Login moderador:** admin@sistema.com / admin123 (troque após o primeiro login!)

## 📁 Estrutura de Diretórios

```
workspace/
├── config/                 # Configurações do sistema
│   ├── database.php       # Configuração do banco de dados
│   ├── database.sql       # Schema completo do banco
│   └── app.php            # Configurações gerais
├── public/                # Diretório público (web root)
│   ├── assets/
│   │   ├── css/          # Folhas de estilo
│   │   └── js/           # Scripts JavaScript
│   ├── auth/             # Páginas de autenticação
│   ├── admin/            # Painel administrativo
│   └── uploads/          # Arquivos enviados
├── src/
│   ├── Controllers/      # Controladores da aplicação
│   ├── Models/           # Modelos de dados
│   └── Middleware/       # Middlewares de autenticação
└── logs/                 # Logs da aplicação
```

## 🔐 Segurança

O sistema implementa as seguintes proteções:

- **SQL Injection:** PDO com prepared statements e `ATTR_EMULATE_PREPARES = false`
- **XSS:** `htmlspecialchars()` em toda saída HTML
- **CSRF:** Token por sessão em todos os formulários
- **Senhas:** `password_hash()` com Argon2id
- **Sessão:** `session_regenerate_id()`, httponly, secure, samesite=Strict
- **Acesso Indevido:** Middleware valida usuário e permissões antes de cada rota
- **Auditoria:** Logs de todas as ações críticas

## 🎯 Funcionalidades Principais

### Para Vendedores

- ✅ Gestão de Produtos (CRUD completo)
- ✅ Gestão de Insumos e Estoque
- ✅ Fichas Técnicas de Produção
- ✅ Pedidos e Acompanhamento
- ✅ Cupons de Desconto
- ✅ Controle Financeiro (Receitas e Despesas)
- ✅ Relatórios Gerenciais

### Para Moderadores (Admin)

- ✅ Gestão de Vendedores
- ✅ Ativação/Desativação de Assinaturas
- ✅ Logs de Auditoria Globais
- ✅ Relatórios Gerenciais Consolidados
- ✅ RBAC (Role-Based Access Control)

## 🔄 Fluxos Principais

### 1. Cadastro & Ativação (SaaS)

1. Usuário se cadastra ou faz login com Google
2. Assinatura criada com status "pendente"
3. Moderador aprova manualmente
4. Vendedor acessa funcionalidades completas

### 2. Venda & Baixa de Estoque

1. Cliente adiciona ao carrinho → aplica cupom
2. Pedido criado com status "pendente"
3. Ao mudar para "enviado/entregue" → baixa automática do estoque
4. Movimentação registrada em log

### 3. Produção & Insumos

1. Vendedor seleciona produto + quantidade
2. Sistema verifica disponibilidade de insumos
3. Baixa automática dos insumos utilizados
4. Entrada do produto acabado no estoque

## 📱 PWA (Progressive Web App)

Para habilitar funcionalidades PWA:

1. Crie `public/manifest.json`
2. Crie `public/sw.js` (Service Worker)
3. Adicione links no `<head>` das páginas

## 🛠️ Desenvolvimento

### Adicionando novos modelos

```php
class NovoModel extends Model {
    protected string $table = 'nova_tabela';
    protected array $fillable = ['campo1', 'campo2'];
    protected array $rules = [
        'campo1' => 'required|min:3|max:100',
        'campo2' => 'email'
    ];
}
```

### Adicionando novas rotas

Crie o arquivo na estrutura adequada em `public/` e inclua os middlewares necessários:

```php
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
AuthMiddleware::requireAuth();
```

## 📝 Licença

Este projeto é parte de um sistema SaaS proprietário.

## 🤝 Suporte

Para dúvidas ou suporte, entre em contato com a equipe de desenvolvimento.

---

**Versão:** 1.0.0  
**Última atualização:** 2024
