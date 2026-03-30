# Instalação do Coyote Framework

Este guia explica como instalar e configurar o Coyote Framework em seu ambiente de desenvolvimento.

## 📋 Pré-requisitos

Antes de instalar o Coyote Framework, certifique-se de que seu sistema atende aos seguintes requisitos:

- **PHP 8.1** ou superior
- **Composer** (gerenciador de dependências PHP)
- **Extensões PHP recomendadas:**
  - `mbstring` (para manipulação de strings multibyte)
  - `openssl` (para criptografia e HTTPS)
  - `pdo` (para acesso ao banco de dados)
  - `json` (para manipulação de JSON)
  - `xml` (para parsing XML)

## 📦 Instalação via Composer

A maneira mais simples de instalar o Coyote Framework é através do Composer:

```bash
composer require coyote/framework
```

Ou, se você estiver iniciando um novo projeto:

```bash
composer create-project coyote/framework my-project
```

## 🚀 Instalação Manual

Se preferir instalar manualmente:

1. **Baixe o framework:**
   ```bash
   git clone https://github.com/coyote/framework.git
   ```

2. **Copie os arquivos para seu projeto:**
   ```bash
   cp -r framework/vendors/coyote ./vendors/
   ```

3. **Configure o autoloader no seu `composer.json`:**
   ```json
   {
       "autoload": {
           "psr-4": {
               "Coyote\\": "vendors/coyote/"
           }
       }
   }
   ```

4. **Execute o autoloader:**
   ```bash
   composer dump-autoload
   ```

## 🏗️ Estrutura do Projeto

Após a instalação, sua estrutura de diretórios deve ser semelhante a:

```
meu-projeto/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   └── Providers/
├── config/
│   ├── app.php
│   ├── database.php
│   └── ...
├── public/
│   └── index.php
├── resources/
│   ├── views/
│   └── assets/
├── routes/
│   └── web.php
├── storage/
│   ├── cache/
│   ├── logs/
│   └── sessions/
├── vendors/
│   └── coyote/      # Framework core
└── composer.json
```

## ⚙️ Configuração Inicial

### 1. Configurar o Front Controller

Crie ou edite o arquivo `public/index.php`:

```php
<?php
// public/index.php

require_once __DIR__ . '/../vendor/autoload.php';

use Coyote\Core\Application;

// Criar instância da aplicação
$app = new Application(dirname(__DIR__));

// Configurar provedores de serviço
$app->register(\Coyote\Providers\ConfigServiceProvider::class);
$app->register(\Coyote\Providers\LogServiceProvider::class);
$app->register(\Coyote\Providers\ViewServiceProvider::class);
$app->register(\Coyote\Providers\EventServiceProvider::class);

// Executar a aplicação
$app->run();
```

### 2. Configurar o Ambiente

Crie o arquivo de configuração `config/app.php`:

```php
<?php
// config/app.php

return [
    'name' => 'Minha Aplicação',
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'America/Sao_Paulo',
    'locale' => 'pt_BR',
    'key' => env('APP_KEY', 'base64:your-secret-key-here'),
];
```

### 3. Configurar Variáveis de Ambiente

Crie um arquivo `.env` na raiz do projeto:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=base64:your-secret-key-here

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coyote_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 4. Gerar Chave da Aplicação

Execute o comando para gerar uma chave segura:

```bash
php vendor/bin/coyote key:generate
```

Ou manualmente via PHP:

```php
<?php
$key = base64_encode(random_bytes(32));
echo "APP_KEY=base64:$key";
```

## 🔧 Configuração do Servidor Web

### Apache

Adicione ao seu `.htaccess` no diretório `public/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

### Nginx

Configure seu servidor Nginx:

```nginx
server {
    listen 80;
    server_name localhost;
    root /caminho/para/projeto/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Servidor de Desenvolvimento PHP

Para desenvolvimento rápido, use o servidor embutido do PHP:

```bash
php -S localhost:8000 -t public/
```

## 📊 Verificação da Instalação

Para verificar se a instalação foi bem-sucedida:

1. **Teste o servidor:** Acesse `http://localhost:8000`
2. **Verifique os requisitos:** Crie um arquivo `test.php`:

```php
<?php
// test.php
require_once 'vendor/autoload.php';

use Coyote\Core\Application;

try {
    $app = new Application(__DIR__);
    echo "✅ Coyote Framework instalado com sucesso!";
    echo "<br>Versão PHP: " . PHP_VERSION;
    echo "<br>Timezone: " . date_default_timezone_get();
} catch (Exception $e) {
    echo "❌ Erro na instalação: " . $e->getMessage();
}
```

## 🐛 Solução de Problemas Comuns

### Erro: "Class not found"
- Execute `composer dump-autoload`
- Verifique se o namespace está correto no `composer.json`

### Erro: "Permission denied" no storage
- Configure permissões: `chmod -R 775 storage/`
- No Windows, garanta que o usuário do servidor web tenha acesso de escrita

### Erro: "No application encryption key has been specified"
- Execute `php vendor/bin/coyote key:generate`
- Ou defina manualmente no arquivo `.env`

### Servidor não encontra rotas
- Verifique se o mod_rewrite está habilitado (Apache)
- Confira a configuração do Nginx
- Use o servidor PHP embutido para testes

## 🚀 Próximos Passos

Após a instalação bem-sucedida, você pode:

1. **[Configurar o banco de dados](database/connection.md)**
2. **[Criar sua primeira rota](http/routing.md)**
3. **[Explorar os exemplos práticos](../examples/blog-tutorial.md)**
4. **[Consultar a referência da API](../api/class-index.md)**

---

**Nota:** Para ambientes de produção, certifique-se de:
- Definir `APP_ENV=production` e `APP_DEBUG=false`
- Configurar permissões de arquivo adequadas
- Implementar HTTPS
- Configurar backup regular dos dados