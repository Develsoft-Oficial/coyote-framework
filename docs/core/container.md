# Container de Injeção de Dependências

O `Container` é o sistema de Injeção de Dependências (DI) do Coyote Framework. Ele gerencia a criação e resolução de objetos, permitindo acoplamento fraco e código testável.

## 📋 Visão Geral

```php
namespace Coyote\Core;

class Container implements ContainerInterface
{
    // Implementação do container PSR-11
}
```

## 🏗️ Conceitos Fundamentais

### Bindings (Vinculações)
Registram como uma abstração (interface ou nome) deve ser resolvida para uma implementação concreta.

### Instâncias
Objetos já criados que são retornados diretamente quando solicitados.

### Singletons
Bindings que retornam a mesma instância em todas as resoluções.

### Contextual Bindings
Bindings que variam dependendo do contexto onde são resolvidos.

## 🔧 Métodos Principais

### Registro de Bindings

#### `bind($abstract, $concrete = null, $shared = false)`
Registra um binding no container.

```php
// Binding básico
$container->bind('logger', \App\Services\FileLogger::class);

// Binding com closure
$container->bind('database', function ($container) {
    return new DatabaseConnection($container->make('config'));
});

// Binding compartilhado (singleton)
$container->bind('cache', \App\Services\RedisCache::class, true);
```

**Parâmetros:**
- `$abstract` (string): Nome da abstração (interface, nome abstrato)
- `$concrete` (mixed): Implementação concreta (classe, closure, null para auto-resolução)
- `$shared` (bool): Se deve ser compartilhado (singleton)

#### `singleton($abstract, $concrete = null)`
Registra um binding compartilhado (singleton).

```php
$container->singleton('config', \Coyote\Core\Config::class);
$container->singleton('router', function ($container) {
    return new Router($container);
});
```

#### `instance($abstract, $instance)`
Registra uma instância existente no container.

```php
$logger = new FileLogger('/path/to/logs');
$container->instance('logger', $logger);

$config = new Config(['debug' => true]);
$container->instance('config', $config);
```

#### `bindIf($abstract, $concrete = null, $shared = false)`
Registra um binding apenas se não existir.

```php
$container->bindIf('mailer', \App\Services\Mailer::class);
// Só registra se 'mailer' não estiver já registrado
```

### Resolução de Dependências

#### `make($abstract, array $parameters = [])`
Resolve uma instância do container.

```php
// Resolver por nome
$logger = $container->make('logger');

// Resolver por classe
$userRepository = $container->make(\App\Repositories\UserRepository::class);

// Com parâmetros adicionais
$user = $container->make(\App\Models\User::class, ['id' => 1]);
```

#### `get($id)`
Implementação do PSR-11 ContainerInterface.

```php
try {
    $service = $container->get('service.name');
} catch (\Psr\Container\NotFoundExceptionInterface $e) {
    // Serviço não encontrado
}
```

#### `has($id)`
Verifica se o container pode resolver um serviço (PSR-11).

```php
if ($container->has('database')) {
    $db = $container->get('database');
}
```

#### `call($callback, array $parameters = [], $defaultMethod = null)`
Chama um callback com injeção de dependências.

```php
// Closure com DI
$result = $container->call(function (Database $db, Logger $logger) {
    return $db->query('SELECT * FROM users');
});

// Método de classe
$controller = new UserController;
$result = $container->call([$controller, 'show'], ['id' => 123]);

// Método estático
$result = $container->call('App\\Services\\Mailer::sendWelcomeEmail', [$user]);
```

### Bindings Contextuais

#### `when($concrete)`
Inicia um binding contextual.

```php
$container->when(\App\Controllers\UserController::class)
    ->needs(\App\Contracts\UserRepository::class)
    ->give(\App\Repositories\DatabaseUserRepository::class);

$container->when(\App\Controllers\ApiController::class)
    ->needs(\App\Contracts\UserRepository::class)
    ->give(\App\Repositories\ApiUserRepository::class);
```

#### `needs($abstract)`
Especifica qual dependência precisa de binding contextual.

```php
$container->when(PhotoController::class)
    ->needs('$maxFileSize')
    ->give(5000);
```

#### `give($implementation)`
Especifica a implementação para o binding contextual.

```php
$container->when(VideoController::class)
    ->needs(Filesystem::class)
    ->give(function () {
        return Storage::disk('videos');
    });
```

### Tags e Grupos

#### `tag($abstracts, $tags)`
Adiciona tags a bindings.

```php
$container->tag([
    \App\Reports\SalesReport::class,
    \App\Reports\InventoryReport::class,
    \App\Reports\PerformanceReport::class,
], 'reports');
```

#### `tagged($tag)`
Resolve todos os bindings com uma tag específica.

```php
$reports = $container->tagged('reports');
foreach ($reports as $report) {
    $report->generate();
}
```

### Extensões

#### `extend($abstract, $closure)`
Estende um binding existente.

```php
$container->extend('cache', function ($cache, $container) {
    return new CacheDecorator($cache);
});
```

## 🎯 Exemplos Completos

### Exemplo 1: Configuração Básica do Container

```php
<?php
// bootstrap/container.php

use Coyote\Core\Container;

$container = new Container;

// Registrar serviços essenciais
$container->singleton('config', function ($container) {
    return new Config(require __DIR__ . '/../config/app.php');
});

$container->singleton('database', function ($container) {
    $config = $container->make('config')->get('database');
    return new DatabaseConnection($config);
});

$container->bind('logger', \App\Services\FileLogger::class);

$container->singleton('mailer', function ($container) {
    $config = $container->make('config')->get('mail');
    return new Mailer($config);
});

return $container;
```

### Exemplo 2: Controller com Injeção de Dependências

```php
<?php
// app/Controllers/UserController.php

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Services\Mailer;
use Coyote\Http\Controllers\Controller;

class UserController extends Controller
{
    protected $users;
    protected $mailer;
    
    // As dependências são injetadas automaticamente
    public function __construct(UserRepository $users, Mailer $mailer)
    {
        $this->users = $users;
        $this->mailer = $mailer;
    }
    
    public function register(Request $request)
    {
        $user = $this->users->create($request->all());
        
        // Enviar e-mail de boas-vindas
        $this->mailer->sendWelcomeEmail($user);
        
        return redirect('/dashboard');
    }
}

// O container resolve automaticamente:
// $controller = $container->make(UserController::class);
```

### Exemplo 3: Bindings Contextuais

```php
<?php
// Em um Service Provider

public function register()
{
    // Binding padrão
    $this->app->bind(
        \App\Contracts\PaymentGateway::class,
        \App\Services\StripeGateway::class
    );
    
    // Binding contextual para testes
    $this->app->when(\Tests\Feature\CheckoutTest::class)
        ->needs(\App\Contracts\PaymentGateway::class)
        ->give(\App\Services\MockPaymentGateway::class);
    
    // Binding contextual para ambiente
    if ($this->app->environment('local')) {
        $this->app->bind(
            \App\Contracts\PaymentGateway::class,
            \App\Services\SandboxPaymentGateway::class
        );
    }
}
```

### Exemplo 4: Factory com Parâmetros

```php
<?php
// app/Services/ReportFactory.php

namespace App\Services;

class ReportFactory
{
    protected $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    public function create($type, array $params = [])
    {
        // Mapear tipo para classe
        $map = [
            'sales' => \App\Reports\SalesReport::class,
            'inventory' => \App\Reports\InventoryReport::class,
            'performance' => \App\Reports\PerformanceReport::class,
        ];
        
        if (!isset($map[$type])) {
            throw new \InvalidArgumentException("Tipo de relatório inválido: $type");
        }
        
        // Criar com parâmetros adicionais
        return $this->container->make($map[$type], $params);
    }
}

// Uso
$factory = $container->make(ReportFactory::class);
$report = $factory->create('sales', ['year' => 2024]);
```

### Exemplo 5: Decorator Pattern com Container

```php
<?php
// app/Services/CacheDecorator.php

namespace App\Services;

class CacheDecorator implements UserRepository
{
    protected $repository;
    protected $cache;
    
    public function __construct(UserRepository $repository, Cache $cache)
    {
        $this->repository = $repository;
        $this->cache = $cache;
    }
    
    public function find($id)
    {
        $key = "user.{$id}";
        
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }
        
        $user = $this->repository->find($id);
        $this->cache->put($key, $user, 3600);
        
        return $user;
    }
}

// Configuração no container
$container->extend(\App\Contracts\UserRepository::class, function ($repository, $container) {
    return new CacheDecorator($repository, $container->make('cache'));
});
```

## 🔍 Resolução Automática

O container pode resolver automaticamente classes sem binding explícito:

```php
class OrderService
{
    protected $paymentGateway;
    protected $mailer;
    protected $logger;
    
    // O container resolve automaticamente
    public function __construct(
        PaymentGateway $paymentGateway,
        Mailer $mailer,
        Logger $logger
    ) {
        $this->paymentGateway = $paymentGateway;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }
}

// Funciona mesmo sem binding explícito
$service = $container->make(OrderService::class);
```

### Limitações da Auto-resolução

A auto-resolução não funciona para:
- Interfaces (precisa de binding)
- Tipos primitivos (string, int, array)
- Classes abstratas
- Parâmetros com valores padrão complexos

## ⚙️ Configuração Avançada

### Binding de Interfaces

```php
// Registrar interface com implementação
$container->bind(
    \App\Contracts\UserRepository::class,
    \App\Repositories\DatabaseUserRepository::class
);

// Em qualquer lugar que precise da interface
class UserController
{
    public function __construct(\App\Contracts\UserRepository $users)
    {
        // Recebe DatabaseUserRepository automaticamente
    }
}
```

### Binding com Parâmetros Nomeados

```php
$container->bind('database', function ($container) {
    $config = $container->make('config');
    
    return new DatabaseConnection(
        host: $config->get('database.host'),
        database: $config->get('database.name'),
        username: $config->get('database.username'),
        password: $config->get('database.password'),
        port: $config->get('database.port', 3306)
    );
});
```

### Binding de Factory

```php
$container->bind('user.factory', function ($container) {
    return function ($attributes = []) use ($container) {
        return $container->make(\App\Models\User::class, $attributes);
    };
});

// Uso
$factory = $container->make('user.factory');
$user = $factory(['name' => 'John', 'email' => 'john@example.com']);
```

## 🔄 Ciclo de Vida

### 1. Registro
Bindings são registrados no container (geralmente em Service Providers).

### 2. Resolução
Quando um objeto é solicitado (`make()` ou `get()`), o container:
1. Verifica se há binding explícito
2. Tenta auto-resolução
3. Instancia o objeto com suas dependências
4. Chama métodos `__construct()` com injeção

### 3. Compartilhamento
Para singletons, a instância é armazenada e reutilizada.

### 4. Destruição
O container não gerencia destruição. Use `unset()` ou deixe o garbage collector agir.

## 🎨 Padrões de Design

### Strategy Pattern

```php
// Interface
interface PaymentStrategy
{
    public function pay($amount);
}

// Implementações
class CreditCardPayment implements PaymentStrategy { /* ... */ }
class PayPalPayment implements PaymentStrategy { /* ... */ }
class BankTransferPayment implements PaymentStrategy { /* ... */ }

// Factory com container
class PaymentFactory
{
    protected $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    public function create($method)
    {
        $map = [
            'credit_card' => CreditCardPayment::class,
            'paypal' => PayPalPayment::class,
            'bank_transfer' => BankTransferPayment::class,
        ];
        
        return $this->container->make($map[$method]);
    }
}
```

### Repository Pattern

```php
// Interface do repositório
interface ProductRepository
{
    public function all();
    public function find($id);
    public function create(array $data);
}

// Implementação
class EloquentProductRepository implements ProductRepository
{
    protected $model;
    
    public function __construct(Product $model)
    {
        $this->model = $model;
    }
    
    // Implementação dos métodos
}

// Binding
$container->bind(ProductRepository::class, EloquentProductRepository::class);

// Uso no controller
class ProductController
{
    public function __construct(ProductRepository $products)
    {
        $this->products = $products;
    }
}
```

## ⚠️ Erros Comuns

### Circular Dependency

```php
// ERRO: Dependência circular
class ServiceA
{
    public function __construct(ServiceB $b) { }
}

class ServiceB
{
    public function __construct(ServiceA $a) { }
}

// SOLUÇÃO: Use setter injection ou refatore
class ServiceA
{
    protected $b;
    
    public function setServiceB(ServiceB $b)
    {
        $this->b = $b;
    }
}
```

### Binding Não Encontrado

```php
// ERRO: Tentar resolver interface sem binding
interface MailerInterface { }
class SendGridMailer implements MailerInterface { }

// Sem binding
$mailer = $container->make(MailerInterface::class); // Erro!

// SOLUÇÃO: Registrar binding
$container->bind(MailerInterface::class, SendGridMailer::class);
```

### Parâmetros Primitivos

```php
// ERRO: Container não sabe resolver string
class Config
{
    public function __construct(string $configPath) { }
}

// SOLUÇÃO: Usar binding contextual
$container->when(Config::class)
    ->needs('$configPath')
    ->give('/path/to/config');
```

## 📊 Performance Considerations

### Cache de Resolução

Para melhor performance em produção:

```php
// Gerar cache de serviços
php vendor/bin/coyote optimize

// O cache armazena:
// - Mapeamento de bindings
// - Reflexão de dependências
// - Singletons instanciados
```

### Lazy Loading

Use `bind()` em vez de `instance()` para serviços pesados:

```php
// Carregamento preguiçoso (lazy)
$container->bind('heavyService', function ($container) {
    return new HeavyService(); // Só instanciado quando usado
});

// vs. Carregamento imediato
$container->instance('heavyService', new HeavyService()); // Instanciado imediatamente
```

## 🔧 Debugging

### Listar Bindings Registrados

```php
// Método helper para debug
function debugContainer(Container $container)
{
    $bindings = [];
    
    // Usar reflexão para acessar propriedades privadas
    $reflection = new \ReflectionClass($container);
    $bindingsProperty = $reflection->getProperty('bindings');
    $bindingsProperty->setAccessible(true);
    
    return $bindingsProperty->getValue($container);
}

// Ou usar var_dump em contexto de desenvolvimento
if (env('APP_DEBUG')) {
    var_dump($container->getBindings());
}
```

### Verificar Dependências

```php
// Verificar se uma classe pode ser resolvida
function canResolve(Container $container, $class)
{
    try {
        $container->make($class);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

// Listar dependências de uma classe
function getDependencies($class)
{
