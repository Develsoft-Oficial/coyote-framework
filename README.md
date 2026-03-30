# Coyote Framework

[![PHP Version](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Composer](https://img.shields.io/badge/composer-coyote%2Fframework-blue)](https://packagist.org/packages/coyote/framework)

**PHP Micro Framework Leve e Completo** para desenvolvimento rápido de aplicações web modernas.

## 🚀 Instalação

### Via Composer (Recomendado)

```bash
composer require coyote/framework
```

### Instalação de Desenvolvimento

Para desenvolver o framework localmente:

```bash
# Clone o repositório
git clone https://github.com/Develsoft-Oficial/coyote-framework.git
cd framework

# Instale dependências
composer install

# Configure o symlink de desenvolvimento
.\scripts\setup-dev-symlink.bat
# ou
powershell -ExecutionPolicy Bypass -File .\scripts\setup-dev-symlink.ps1
```

## 📦 Estrutura do Pacote

```
src/
├── Core/           # Núcleo do framework
├── Http/           # Camada HTTP (Request, Response, Routing)
├── Database/       # ORM, Query Builder, Migrations
├── Auth/           # Sistema de autenticação
├── Validation/     # Validação de dados
├── View/           # Sistema de templates
├── Config/         # Gerenciamento de configuração
├── Log/            # Sistema de logging
├── Providers/      # Service Providers
├── Routing/        # Sistema de rotas avançado
├── Session/        # Gerenciamento de sessões
├── Forms/          # Form Builder fluente
├── Support/        # Utilitários e helpers
└── ...             # Outros módulos
```

## 🎯 Primeiros Passos

### 1. Criar uma Aplicação

```php
<?php
// index.php
require_once __DIR__ . '/vendor/autoload.php';

use Coyote\Core\Application;

$app = new Application(__DIR__);

// Configurar rotas
$app->router->get('/', function() {
    return 'Olá, Coyote Framework!';
});

// Executar aplicação
$app->run();
```

### 2. Configuração Básica

```php
// config/app.php
return [
    'name' => 'Minha Aplicação',
    'debug' => true,
    'providers' => [
        Coyote\Providers\RoutingServiceProvider::class,
        Coyote\Providers\DatabaseServiceProvider::class,
        // Seus providers personalizados
    ],
];
```

### 3. Exemplo com Banco de Dados

```php
use Coyote\Database\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
}

// Criar usuário
$user = User::create([
    'name' => 'João Silva',
    'email' => 'joao@exemplo.com',
    'password' => password_hash('senha123', PASSWORD_DEFAULT)
]);

// Consultar usuários
$users = User::where('active', true)->get();
```

## 🔧 Funcionalidades Principais

### 🏗️ **Núcleo Modular**
- Container de Injeção de Dependências
- Service Providers para extensibilidade
- Sistema de eventos e hooks

### 🌐 **Camada HTTP Completa**
- Request/Response PSR-7 compatível
- Sistema de rotas RESTful
- Middleware pipeline
- CSRF protection integrada

### 🗄️ **ORM e Banco de Dados**
- Query Builder fluente
- Models com relacionamentos
- Migrations e seeders
- Suporte a múltiplos bancos

### 🔐 **Autenticação Segura**
- Multi-guard (Session, Token, API)
- User providers personalizáveis
- Middleware de proteção
- Password hashing com bcrypt

### ✅ **Validação Avançada**
- 40+ regras de validação
- Form Requests para validação em controllers
- Mensagens personalizáveis
- Validação condicional

### 📝 **Form Builder Fluente**
- API declarativa para formulários
- 20+ tipos de campos
- Validação integrada
- Renderização personalizável

### 👁️ **Sistema de Views**
- Templates com sintaxe Blade-like
- Layouts e componentes
- Seções e stacks
- Compilação e cache

## 📚 Documentação Completa

A documentação completa está disponível em [docs/](docs/README.md) e inclui:

- **🚀 Começando**: Instalação, configuração, primeiros passos
- **🏗️ Núcleo**: Application, Container, Service Providers
- **🌐 HTTP**: Request, Response, Routing, Controllers, Middleware
- **🗄️ Banco de Dados**: Models, Query Builder, Migrations
- **🔐 Autenticação**: Auth Manager, Guards, User Providers
- **✅ Validação**: Validator, Rules, Form Requests
- **📝 Formulários**: Form Builder, Field Types, Validation
- **👁️ Views**: View Factory, Syntax, Layouts
- **🧪 Exemplos**: Tutoriais completos (Blog, API REST, etc.)

## 🧪 Testando

```bash
# Executar testes unitários
composer test

# Executar testes com cobertura
composer test-coverage

# Executar análise estática
composer analyse

# Executar todos os checks
composer check
```

## 🔄 Desenvolvimento

### Fluxo de Desenvolvimento com Symlink

O framework suporta desenvolvimento local com symlink/junction:

1. **Edite os arquivos** em `src/`
2. **As mudanças são refletidas automaticamente** em `test-app/vendor/coyote/framework/`
3. **Teste imediatamente** com a aplicação de teste

### Scripts de Desenvolvimento

- `scripts/setup-dev-symlink.ps1` - PowerShell script para Windows
- `scripts/setup-dev-symlink.bat` - Batch script para Windows
- `test-app/` - Aplicação de teste para validação

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor, leia o [Guia de Contribuição](docs/contributing/guide.md) antes de enviar pull requests.

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/incrivel`)
3. Commit suas mudanças (`git commit -am 'Adiciona feature incrível'`)
4. Push para a branch (`git push origin feature/incrivel`)
5. Abra um Pull Request

## 📄 Licença

O Coyote Framework é licenciado sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 🆘 Suporte

- **Documentação**: [docs/](docs/README.md)
- **Issues**: [GitHub Issues](https://github.com/Develsoft-Oficial/coyote-framework/issues)
- **Email**: dev@develsoft.com.br

---

**Coyote Framework** © 2026 - Desenvolvido com ❤️ pela comunidade PHP brasileira.