# Classe Application

A classe `Application` é o coração do Coyote Framework. Ela atua como container da aplicação, gerenciando o ciclo de vida, injeção de dependências e coordenação de todos os componentes.

## 📋 Visão Geral

```php
namespace Coyote\Core;

use Coyote\Core\Container;
use Coyote\Core\Config;

class Application extends Container
{
    // Propriedades e métodos
}
```

## 🏗️ Instanciação

### Construtor Básico

```php
use Coyote\Core\Application;

// Cria uma instância da aplicação
$app = new Application($basePath);
```

**Parâmetros:**
- `$basePath` (string): Caminho absoluto para o diretório raiz da aplicação

**Exemplo:**
```php
// Na raiz do projeto
$app = new Application(__DIR__);

// Em public/index.php
$app = new Application(dirname(__DIR__));
```

### Construtor com Configurações Personalizadas

```php
$app = new Application($basePath, $paths);
```

**Parâmetros:**
- `$basePath` (string): Caminho base
- `$paths` (array): Configurações de caminhos personalizados

**Exemplo:**
```php
$app = new Application(__DIR__, [
    'app' => 'src',
    'config' => 'settings',
    'storage' => 'var',
    'public' => 'web',
]);
```

## 📁 Propriedades Públicas

### Caminhos da Aplicação

| Propriedade | Tipo | Descrição | Valor Padrão |
|------------|------|-----------|--------------|
| `$basePath` | string | Caminho base da aplicação | Construtor |
| `$appPath` | string | Caminho do diretório `app/` | `$basePath . '/app'` |
| `$configPath` | string | Caminho do diretório `config/` | `$basePath . '/config'` |
| `$storagePath` | string | Caminho do diretório `storage/` | `$basePath . '/storage'` |
| `$publicPath` | string | Caminho do diretório `public/` | `$basePath . '/public'` |
| `$resourcesPath` | string | Caminho do diretório `resources/` | `$basePath . '/resources'` |
| `$databasePath` | string | Caminho do diretório `database/` | `$basePath . '/database'` |

### Configurações da Aplicação

| Propriedade | Tipo | Descrição | Valor Padrão |
|------------|------|-----------|--------------|
| `$environment` | string | Ambiente da aplicação | `'production'` |
| `$booted` | bool | Se a aplicação foi inicializada | `false` |
| `$hasBeenBootstrapped` | bool | Se o bootstrap foi executado | `false` |
| `$deferredServices` | array | Serviços com carregamento diferido | `[]` |

## 🔧 Métodos Públicos

### Métodos de Caminhos

#### `path($path = '')`
Retorna o caminho completo para um diretório ou arquivo.

```php
$app->path(); // Retorna $basePath
$app->path('app'); // Retorna $basePath . '/app'
$app->path('config/app.php'); // Retorna $basePath . '/config/app.php'
```

#### `basePath($path = '')`
Alias para `path()`.

#### `appPath($path = '')`
Retorna caminho dentro do diretório `app/`.

```php
$app->appPath(); // Retorna $appPath
$app->appPath('Controllers/UserController.php'); // Retorna $appPath . '/Controllers/UserController.php'
```

#### `configPath($path = '')`
Retorna caminho dentro do diretório `config/`.

```php
$app->configPath(); // Retorna $configPath
$app->configPath('database.php'); // Retorna $configPath . '/database.php'
```

#### `storagePath($path = '')`
Retorna caminho dentro do diretório `storage/`.

```php
$app->storagePath(); // Retorna $storagePath
$app->storagePath('logs/app.log'); // Retorna $storagePath . '/logs/app.log'
```

#### `publicPath($path = '')`
Retorna caminho dentro do diretório `public/`.

```php
$app->publicPath(); // Retorna $publicPath
$app->publicPath('assets/css/app.css'); // Retorna $publicPath . '/assets/css/app.css'
```

#### `resourcesPath($path = '')`
Retorna caminho dentro do diretório `resources/`.

```php
$app->resourcesPath(); // Retorna $resourcesPath
$app->resourcesPath('views/home.php'); // Retorna $resourcesPath . '/views/home.php'
```

#### `databasePath($path = '')`
Retorna caminho dentro do diretório `database/`.

```php
$app->databasePath(); // Retorna $databasePath
$app->databasePath('migrations'); // Retorna $databasePath . '/migrations'
```

### Métodos de Ambiente

#### `environment()`
Retorna o ambiente atual da aplicação.

```php
$env = $app->environment(); // 'production', 'local', 'testing', etc.
```

#### `environmentFile()`
Retorna o nome do arquivo de ambiente.

```php
$envFile = $app->environmentFile(); // '.env'
```

#### `environmentFilePath()`
Retorna o caminho completo do arquivo de ambiente.

```php
$envPath = $app->environmentFilePath(); // '/path/to/project/.env'
```

#### `isLocal()`
Verifica se a aplicação está no ambiente local.

```php
if ($app->isLocal()) {
    // Executar apenas em desenvolvimento local
    debug_log('Modo desenvolvimento ativo');
}
```

#### `isProduction()`
Verifica se a aplicação está no ambiente de produção.

```php
if ($app->isProduction()) {
    // Otimizações para produção
    $app->configure('cache');
}
```

#### `isTesting()`
Verifica se a aplicação está no ambiente de teste.

```php
if ($app->isTesting()) {
    // Configurações específicas para testes
    $app->instance('db', new MockDatabase);
}
```

#### `runningInConsole()`
Verifica se a aplicação está sendo executada via CLI.

```php
if ($app->runningInConsole()) {
    // Comportamento específico para console
    $output = new ConsoleOutput;
}
```

#### `runningUnitTests()`
Verifica se a aplicação está executando testes unitários.

```php
if ($app->runningUnitTests()) {
    // Configurar mocks e fakes
}
```

### Métodos de Inicialização

#### `bootstrapWith(array $bootstrappers)`
Executa os bootstrappers da aplicação.

```php
$app->bootstrapWith([
    \Coyote\Bootstrap\LoadConfiguration::class,
    \Coyote\Bootstrap\HandleExceptions::class,
    \Coyote\Bootstrap\RegisterFacades::class,
    \Coyote\Bootstrap\RegisterProviders::class,
    \Coyote\Bootstrap\BootProviders::class,
]);
```

#### `bootstrapPath($path = '')`
Retorna caminho do diretório de bootstrap.

```php
$bootstrapPath = $app->bootstrapPath(); // $basePath . '/bootstrap'
$cachePath = $app->bootstrapPath('cache.php'); // $basePath . '/bootstrap/cache.php'
```

#### `boot()`
Inicializa a aplicação.

```php
$app->boot();
```

#### `booted($callback)`
Registra um callback para ser executado após a inicialização.

```php
$app->booted(function ($app) {
    // Executar após a aplicação estar totalmente inicializada
    $app->make('router')->getRoutes()->refreshNameLookups();
});
```

#### `hasBeenBootstrapped()`
Verifica se a aplicação foi inicializada.

```php
if (!$app->hasBeenBootstrapped()) {
    $app->bootstrapWith($bootstrappers);
}
```

### Métodos de Service Providers

#### `register($provider, $force = false)`
Registra um service provider.

```php
// Registrar por classe
$app->register(\App\Providers\AppServiceProvider::class);

// Registrar por instância
$app->register(new \App\Providers\EventServiceProvider($app));

// Forçar registro mesmo se já registrado
$app->register($provider, true);
```

#### `registerDeferredProvider($provider, $service = null)`
Registra um provider com carregamento diferido.

```php
$app->registerDeferredProvider(\App\Providers\RouteServiceProvider::class);
```

#### `getProviders($provider)`
Obtém os providers registrados.

```php
$providers = $app->getProviders(\App\Providers\AppServiceProvider::class);
```

#### `resolveProvider($provider)`
Resolve uma instância do provider.

```php
$provider = $app->resolveProvider(\App\Providers\AppServiceProvider::class);
```

### Métodos de Configuração

#### `configure($name)`
Carrega um arquivo de configuração.

```php
$app->configure('app'); // Carrega config/app.php
$app->configure('database'); // Carrega config/database.php
```

#### `configurationIsCached()`
Verifica se a configuração está em cache.

```php
if ($app->configurationIsCached()) {
    $config = require $app->getCachedConfigPath();
} else {
    $config = $app->configure('app');
}
```

#### `getCachedConfigPath()`
Retorna o caminho do arquivo de configuração em cache.

```php
$cachedConfigPath = $app->getCachedConfigPath(); // $storagePath . '/framework/config.php'
```

#### `getCachedServicesPath()`
Retorna o caminho do arquivo de serviços em cache.

```php
$cachedServicesPath = $app->getCachedServicesPath(); // $storagePath . '/framework/services.php'
```

#### `getCachedPackagesPath()`
Retorna o caminho do arquivo de pacotes em cache.

```php
$cachedPackagesPath = $app->getCachedPackagesPath(); // $storagePath . '/framework/packages.php'
```

### Métodos de Execução

#### `run()`
Executa a aplicação.

```php
// Em public/index.php
$app->run();
```

#### `handle($request)`
Processa uma requisição HTTP.

```php
$response = $app->handle($request);
```

#### `terminate($request, $response)`
Executa terminadores após a resposta ser enviada.

```php
$app->terminate($request, $response);
```

#### `call($callback, array $parameters = [], $defaultMethod = null)`
Chama um callback com injeção de dependências.

```php
$result = $app->call([$controller, 'index'], $parameters);
$result = $app->call(function (Database $db) {
    return $db->query('SELECT * FROM users');
});
```

## 🎯 Exemplos de Uso

### Exemplo 1: Aplicação Básica

```php
<?php
// public/index.php

require_once __DIR__ . '/../vendor/autoload.php';

use Coyote\Core\Application;

// Criar aplicação
$app = new Application(dirname(__DIR__));

// Configurar provedores
$app->register(\Coyote\Providers\ConfigServiceProvider::class);
$app->register(\Coyote\Providers\ViewServiceProvider::class);
$app->register(\Coyote\Providers\EventServiceProvider::class);

// Executar
$app->run();
```

### Exemplo 2: Aplicação com Configurações Personalizadas

```php
<?php
// bootstrap/app.php

use Coyote\Core\Application;

$app = new Application(
    dirname(__DIR__),
    [
        'app' => 'src',
        'config' => 'config',
        'storage' => 'var/storage',
        'public' => 'public',
        'resources' => 'resources',
    ]
);

// Configurar ambiente
$app->instance('env', getenv('APP_ENV') ?: 'production');

// Registrar provedores
$app->register(\App\Providers\AppServiceProvider::class);
$app->register(\App\Providers\AuthServiceProvider::class);
$app->register(\App\Providers\RouteServiceProvider::class);

return $app;
```

### Exemplo 3: Aplicação em Console

```php
<?php
// artisan

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

// Configurar para modo console
$app->instance('console', true);

// Executar comando
$kernel = $app->make(\Coyote\Console\Kernel::class);

$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput,
    new Symfony\Component\Console\Output\ConsoleOutput
);

$kernel->terminate($input, $status);

exit($status);
```

### Exemplo 4: Testes Unitários

```php
<?php
// tests/TestCase.php

namespace Tests;

use Coyote\Core\Application;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $app;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app = $this->createApplication();
    }
    
    protected function createApplication()
    {
        $app = new Application(
            dirname(__DIR__),
            ['env' => 'testing']
        );
        
        $app->register(\App\Providers\AppServiceProvider::class);
        
        return $app;
    }
    
    protected function tearDown(): void
    {
        $this->app = null;
        parent::tearDown();
    }
}
```

## 🔍 Métodos Protegidos

### `registerBaseServiceProviders()`
Registra os service providers base.

```php
protected function registerBaseServiceProviders()
{
    $this->register(new \Coyote\Providers\ConfigServiceProvider($this));
    $this->register(new \Coyote\Providers\EventServiceProvider($this));
    // ...
}
```

### `registerBaseBindings()`
Registra os bindings base.

```php
protected function registerBaseBindings()
{
    static::setInstance($this);
    $this->instance('app', $this);
    $this->instance(Container::class, $this);
    // ...
}
```

### `bootProviders()`
Inicializa todos os service providers registrados.

```php
protected function bootProviders()
{
    foreach ($this->serviceProviders as $provider) {
        $this->bootProvider($provider);
    }
}
```

## ⚠️ Tratamento de Exceções

### Exceções Comuns

| Exceção | Causa | Solução |
|---------|-------|---------|
| `ApplicationException` | Erro geral na aplicação | Verificar logs e configurações |
| `ContainerException` | Erro no container de DI | Verificar bindings e dependências |
| `ConfigException` | Erro na configuração | Verificar arquivos config/ |

### Exemplo de Tratamento

```php
try {
    $app = new Application(__DIR__);
    $app->register(\App\Providers\AppServiceProvider::class);
    $app->run();
} catch (\Coyote\Core\Exceptions\ApplicationException $e) {
    error_log('Erro na aplicação: ' . $e->getMessage());
    http_response_code(500);
    echo 'Erro interno do servidor';
} catch (\Exception $e) {
    error_log('Erro inesperado: ' . $e->getMessage());
    http_response_code(500);
    echo 'Erro inesperado';
}
```

## 🔄 Ciclo de Vida da Aplicação

1. **Instanciação** → `new Application()`
2. **Registro de Bindings** → `registerBaseBindings()`
3. **Registro de Providers** → `registerBaseServiceProviders()`
4. **Bootstrap** → `bootstrapWith()`
5. **Registro de Rotas** → Providers registram rotas
6. **Execução** → `run()` ou `handle()`
7. **Resposta** → Processamento da requisição
8. **Terminação** → `terminate()`

## 🎨 Padrões de Uso Avançados

### Singleton Application

```php
class App
{
    private static $instance;
    
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Application(__DIR__);
        }
        
        return self::$instance;
    }
    
    public static function __callStatic($method, $args)
    {
        return self::getInstance()->$method(...$args);
    }
}

// Uso
App::path('config');
App::make('router');
```

### Application com Contexto

```php
class ContextualApplication extends Application
{
    protected $context;
    
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }
    
    public function getContext()
    {
        return $this->context;
    }
    
    public function runningInApiContext()
    {
        return $this->context === 'api';
    }
}

// Uso
$app = new ContextualApplication(__DIR__);
$app->setContext('api');

if ($app->runningInApiContext()) {
    // Configurações específicas para API
}
```

## 📊 Performance Considerations

### Cache de Configuração

```php
// Gerar cache de configuração
php vendor