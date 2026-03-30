# Estrutura do Projeto

Esta página descreve a organização padrão de diretórios e arquivos em uma aplicação Coyote Framework.

## 📁 Visão Geral da Estrutura

```
meu-projeto/
├── app/                    # Código da aplicação
├── config/                 # Arquivos de configuração
├── docs/                   # Documentação do projeto
├── modules/                # Módulos personalizados
├── public/                 # Arquivos públicos (web root)
├── resources/              # Recursos da aplicação
├── routes/                 # Definições de rotas
├── storage/                # Armazenamento de arquivos
├── tests/                  # Testes automatizados
├── vendors/                # Dependências (incluindo o framework)
└── arquivos de configuração
```

## 📋 Detalhamento dos Diretórios

### `app/` - Código da Aplicação

Diretório principal contendo a lógica de negócio da aplicação.

```
app/
├── Controllers/           # Controladores HTTP
│   ├── HomeController.php
│   ├── UserController.php
│   └── ...
├── Models/                # Modelos de dados
│   ├── User.php
│   ├── Product.php
│   └── ...
├── Providers/             # Service Providers
│   ├── AppServiceProvider.php
│   └── ...
├── Services/              # Serviços de negócio
│   ├── PaymentService.php
│   ├── NotificationService.php
│   └── ...
├── Repositories/          # Repositórios de dados
│   ├── UserRepository.php
│   └── ...
├── Exceptions/            # Exceções personalizadas
│   ├── ValidationException.php
│   └── ...
├── Middleware/            # Middleware personalizado
│   ├── Authenticate.php
│   ├── CheckRole.php
│   └── ...
└── Console/               # Comandos CLI personalizados
    ├── Commands/
    │   ├── GenerateReport.php
    │   └── ...
    └── Kernel.php
```

### `config/` - Configurações

Arquivos de configuração da aplicação.

```
config/
├── app.php               # Configurações gerais da aplicação
├── database.php          # Configurações de banco de dados
├── cache.php             # Configurações de cache
├── session.php           # Configurações de sessão
├── mail.php              # Configurações de e-mail
├── services.php          # Configurações de serviços externos
├── auth.php              # Configurações de autenticação
├── view.php              # Configurações de views/templates
└── ...
```

### `public/` - Raiz Web

Diretório acessível publicamente pelo servidor web.

```
public/
├── index.php            # Ponto de entrada da aplicação
├── .htaccess            # Configurações Apache (opcional)
├── assets/              # Assets estáticos
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── images/
│       └── logo.png
└── uploads/             # Arquivos enviados pelos usuários
    ├── profiles/
    └── documents/
```

### `resources/` - Recursos

Arquivos não-PHP como views, traduções, assets fonte.

```
resources/
├── views/               # Templates de views
│   ├── layouts/
│   │   ├── app.php
│   │   └── admin.php
│   ├── pages/
│   │   ├── home.php
│   │   └── about.php
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   └── components/      # Componentes reutilizáveis
│       ├── header.php
│       ├── footer.php
│       └── ...
├── lang/                # Arquivos de tradução
│   ├── pt_BR/
│   │   ├── messages.php
│   │   └── validation.php
│   └── en/
│       ├── messages.php
│       └── validation.php
├── assets/              # Assets fonte (SCSS, JS fonte)
│   ├── scss/
│   │   └── app.scss
│   └── js/
│       └── app.js
└── docs/                # Documentação interna
    └── api.md
```

### `routes/` - Definições de Rotas

Arquivos contendo as definições de rotas da aplicação.

```
routes/
├── web.php              # Rotas web (com sessão, CSRF, etc.)
├── api.php              # Rotas API (stateless, com throttle)
├── console.php          # Comandos CLI
└── channels.php         # Canais de broadcast (WebSockets)
```

### `storage/` - Armazenamento

Arquivos gerados pela aplicação em tempo de execução.

```
storage/
├── app/                 # Armazenamento da aplicação
│   ├── public/          # Arquivos públicos gerados
│   └── private/         # Arquivos privados
├── framework/           # Arquivos do framework
│   ├── cache/           # Cache de views, rotas, etc.
│   ├── sessions/        # Arquivos de sessão
│   ├── views/           # Views compiladas
│   └── testing/         # Arquivos de teste
├── logs/                # Arquivos de log
│   ├── app.log
│   ├── error.log
│   └── ...
├── database/            # Banco de dados (SQLite)
│   └── database.sqlite
└── backups/             # Backups automáticos
    └── ...
```

### `tests/` - Testes Automatizados

```
tests/
├── Unit/                # Testes unitários
│   ├── Models/
│   │   ├── UserTest.php
│   │   └── ...
│   └── Services/
│       ├── PaymentServiceTest.php
│       └── ...
├── Feature/             # Testes de funcionalidade
│   ├── AuthTest.php
│   ├── UserRegistrationTest.php
│   └── ...
├── Browser/             # Testes de navegador (opcional)
│   └── ...
└── TestCase.php         # Classe base para testes
```

### `vendors/` - Dependências

```
vendors/
└── coyote/              # Framework Coyote
    ├── Core/            # Núcleo do framework
    ├── Http/            # Camada HTTP
    ├── Database/        # Camada de banco de dados
    ├── Forms/           # Sistema de formulários
    ├── Auth/            # Sistema de autenticação
    ├── Validation/      # Sistema de validação
    ├── Session/         # Gerenciamento de sessão
    ├── View/            # Sistema de views
    ├── Routing/         # Sistema de roteamento
    ├── Providers/       # Service Providers
    └── ...
```

## ⚙️ Arquivos de Configuração na Raiz

### `composer.json`

```json
{
    "name": "vendor/project",
    "type": "project",
    "require": {
        "php": "^8.1",
        "coyote/framework": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Coyote\\": "vendors/coyote/"
        }
    },
    "scripts": {
        "post-autoload-dump": [
            "Coyote\\Foundation\\ComposerScripts::postAutoloadDump"
        ]
    }
}
```

### `.env` (Arquivo de Ambiente)

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=base64:your-secret-key-here

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coyote_app
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

### Outros Arquivos Importantes

- `.env.example` - Template do arquivo de ambiente
- `.gitignore` - Arquivos a serem ignorados pelo Git
- `phpunit.xml` - Configuração do PHPUnit
- `README.md` - Documentação do projeto
- `LICENSE` - Licença do projeto

## 🏗️ Estrutura para Diferentes Tipos de Projetos

### Aplicação Web Tradicional (MVC)

```
project/
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Views/           # Views podem estar aqui ou em resources/views
├── public/
└── ...
```

### API RESTful

```
project/
├── app/
│   ├── Controllers/Api/
│   ├── Models/
│   ├── Transformers/    # Transformadores de dados
│   ├── Requests/        # Form Requests de validação
│   └── Resources/       # API Resources
├── routes/api.php
└── ...
```

### Aplicação Modular

```
project/
├── app/
│   └── Modules/         # Módulos da aplicação
│       ├── Blog/
│       │   ├── Controllers/
│       │   ├── Models/
│       │   ├── Views/
│       │   └── Routes/
│       ├── Shop/
│       │   ├── Controllers/
│       │   ├── Models/
│       │   ├── Views/
│       │   └── Routes/
│       └── ...
└── ...
```

## 🔧 Personalização da Estrutura

### Alterar Caminhos Padrão

No arquivo `config/app.php`:

```php
return [
    'paths' => [
        'app' => env('APP_PATH', 'app'),
        'config' => env('CONFIG_PATH', 'config'),
        'public' => env('PUBLIC_PATH', 'public'),
        'storage' => env('STORAGE_PATH', 'storage'),
        'resources' => env('RESOURCES_PATH', 'resources'),
        'views' => env('VIEWS_PATH', 'resources/views'),
    ],
];
```

### Criar Estrutura Personalizada

Para criar uma estrutura personalizada, você pode:

1. **Modificar o bootstrap da aplicação** em `public/index.php`:

```php
$app = new Application(__DIR__ . '/../', [
    'app' => 'src',
    'config' => 'settings',
    'views' => 'templates',
]);
```

2. **Usar constantes de ambiente** no `.env`:

```env
APP_PATH=src
CONFIG_PATH=settings
VIEWS_PATH=templates
```

## 📊 Boas Práticas de Organização

### 1. Separação por Responsabilidade
- **Controllers**: Apenas lógica de roteamento e resposta HTTP
- **Models**: Apenas lógica de dados e regras de negócio
- **Services**: Lógica de negócio complexa
- **Repositories**: Acesso a dados abstrato

### 2. Convenções de Nomenclatura
- **Controllers**: `UserController.php`, `ProductController.php`
- **Models**: `User.php`, `Product.php` (singular)
- **Views**: `users/index.php`, `products/show.php` (minúsculas, separadas por ponto)
- **Migrations**: `2026_03_30_000001_create_users_table.php`

### 3. Organização de Assets
- CSS/JS fonte em `resources/assets/`
- CSS/JS compilado em `public/assets/`
- Imagens em `public/images/` ou CDN

### 4. Versionamento
- Incluir no `.gitignore`:
  - `storage/logs/*` (exceto `.gitignore`)
  - `storage/framework/cache/*`
  - `storage/framework/sessions/*`
  - `storage/framework/views/*`
  - `.env` (criar `.env.example`)

## 🚀 Scripts de Inicialização

### Criar Estrutura Completa

```bash
# Usando o comando do framework
php vendor/bin/coyote new meu-projeto

# Ou manualmente
mkdir -p meu-projeto/{app/{Controllers,Models,Providers},config,public/{assets/{css,js,images},uploads},resources/{views/{layouts,pages,auth,components},lang/{pt_BR,en},assets/{scss,js}},routes,storage/{app/{public,private},framework/{cache,sessions,views,testing},logs,database,backups},tests/{Unit,Feature,Browser},modules,vendors}
```

### Verificar Estrutura

```bash
# Verificar permissões
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;

# Verificar arquivos de configuração
cp .env.example .env
php vendor/bin/coyote key:generate
```

## ❓ Perguntas Frequentes

### Posso mudar a estrutura completamente?
Sim, o framework é flexível. Basta ajustar os caminhos na inicialização da aplicação.

### Onde colocar helpers/utilitários?
Recomendado: `app/Helpers/` ou `app/Support/`

### E se eu quiser usar uma estrutura flat?
Você pode organizar tudo em diretórios funcionais:
```
project/
├── users/
│   ├── UserController.php
│   ├── User.php
│   ├── user-list.php
│   └── user-form.php
├── products/
│   ├── ProductController.php
│   ├── Product.php
│   ├── product-list.php
│   └── product-form.php
└── ...
```

### Como organizar uma aplicação grande?
Considere:
- Domain-Driven Design (DDD)
- Hexagonal Architecture
- Modules/Plugins
- Microservices (com múltiplas aplicações)

---

**Próximo:** [Configuração da Aplicação](../core/config.md) ou [Primeira Aplicação](../getting-started/quickstart.md)