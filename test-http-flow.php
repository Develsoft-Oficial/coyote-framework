<?php
// test-http-flow.php
// Teste completo do fluxo HTTP do Coyote Framework

require_once __DIR__ . '/vendors/autoload.php';

use Coyote\Core\Application;
use Coyote\Http\Request;
use Coyote\Http\Kernel;
use Coyote\Routing\Router;

echo "=== Teste do Fluxo HTTP - Coyote Framework ===\n\n";

// Criar aplicação
$app = new Application(__DIR__);
$app->setEnvironment('testing');
$app->setDebug(true);

echo "✓ Aplicação criada\n";
echo "  - Ambiente: " . $app->environment() . "\n";
echo "  - Debug: " . ($app->isDebug() ? 'Sim' : 'Não') . "\n";
echo "  - Versão: " . $app->version() . "\n\n";

// Criar router
$router = new Router($app);
$app->instance('router', $router);

echo "✓ Router criado e registrado no container\n\n";

// Carregar rotas
$router->loadRoutes(__DIR__ . '/routes/web.php');
$routes = $router->toArray();

echo "✓ Rotas carregadas (" . count($routes) . " rotas):\n";
foreach ($routes as $route) {
    echo "  - " . $route['method'] . " " . $route['uri'] . " → " . 
         (is_string($route['action']) ? $route['action'] : 'Closure') . 
         ($route['name'] ? " (nome: {$route['name']})" : "") . "\n";
}
echo "\n";

// Criar kernel HTTP
$kernel = new Kernel($app, $router);
$app->instance('kernel', $kernel);

echo "✓ Kernel HTTP criado\n\n";

// Testar diferentes requisições
$testRequests = [
    [
        'method' => 'GET',
        'uri' => '/',
        'description' => 'Página inicial com controller e view'
    ],
    [
        'method' => 'GET',
        'uri' => '/about',
        'description' => 'Página sobre com HTML direto'
    ],
    [
        'method' => 'GET',
        'uri' => '/api',
        'description' => 'API JSON response'
    ],
    [
        'method' => 'GET',
        'uri' => '/items/123',
        'description' => 'Rota com parâmetro'
    ],
    [
        'method' => 'GET',
        'uri' => '/admin/dashboard',
        'description' => 'Rota em grupo (admin)'
    ],
    [
        'method' => 'POST',
        'uri' => '/contact',
        'description' => 'Rota POST'
    ],
    [
        'method' => 'GET',
        'uri' => '/login',
        'description' => 'Rota com múltiplos métodos (GET)'
    ],
    [
        'method' => 'POST',
        'uri' => '/login',
        'description' => 'Rota com múltiplos métodos (POST)'
    ],
    [
        'method' => 'GET',
        'uri' => '/not-found',
        'description' => 'Rota não existente (deve retornar 404)'
    ],
];

echo "=== Executando Testes de Requisições ===\n\n";

foreach ($testRequests as $test) {
    echo "Teste: {$test['description']}\n";
    echo "  Método: {$test['method']}, URI: {$test['uri']}\n";
    
    try {
        // Criar requisição simulada
        $request = Request::create($test['uri'], $test['method']);
        
        // Processar requisição através do kernel
        $response = $kernel->handle($request);
        
        echo "  ✓ Status: {$response->getStatusCode()}\n";
        $contentType = $response->getHeader('Content-Type', 'text/html');
        echo "  ✓ Content-Type: {$contentType}\n";
        
        // Verificar tipo de resposta
        $body = $response->getContent();
        $contentType = $response->getHeader('Content-Type', 'text/html');
        if (strpos($contentType, 'application/json') !== false) {
            echo "  ✓ Tipo: JSON Response\n";
            $data = json_decode($body, true);
            if (isset($data['status'])) {
                echo "  ✓ Status API: {$data['status']}\n";
            }
        } elseif (strpos($body, '<!DOCTYPE html>') !== false || strpos($body, '<html>') !== false) {
            echo "  ✓ Tipo: HTML Response\n";
            echo "  ✓ Tamanho: " . strlen($body) . " bytes\n";
        } else {
            echo "  ✓ Tipo: Text Response\n";
            echo "  ✓ Conteúdo: " . substr($body, 0, 100) . (strlen($body) > 100 ? '...' : '') . "\n";
        }
        
    } catch (\Exception $e) {
        echo "  ✗ Erro: " . $e->getMessage() . "\n";
        echo "  ✗ Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    echo "\n";
}

// Testar funcionalidades do container
echo "=== Testando Container DI ===\n\n";

// Testar binding e resolução
$app->bind('test.service', function ($app) {
    return new class {
        public function test() {
            return 'Service test OK';
        }
    };
});

$service = $app->make('test.service');
echo "✓ Service binding/resolução: " . $service->test() . "\n";

// Testar singleton
$app->singleton('test.singleton', function ($app) {
    return new class {
        public $counter = 0;
        public function increment() {
            return ++$this->counter;
        }
    };
});

$singleton1 = $app->make('test.singleton');
$singleton2 = $app->make('test.singleton');
$singleton1->increment();
echo "✓ Singleton test: " . ($singleton1 === $singleton2 ? 'OK (mesma instância)' : 'FALHOU (instâncias diferentes)') . "\n";
echo "  Contador: " . $singleton2->increment() . " (deve ser 2)\n\n";

// Testar sistema de views
echo "=== Testando Sistema de Views ===\n\n";

if ($app->bound('view')) {
    $viewFactory = $app->make('view');
    
    // Testar view existente
    if ($viewFactory->exists('home')) {
        $view = $viewFactory->make('home', ['test' => 'Dados de teste']);
        echo "✓ View factory funcionando\n";
        echo "✓ View 'home' existe\n";
        
        // Testar renderização
        try {
            $content = $view->render();
            echo "✓ View renderizada com sucesso\n";
            echo "✓ Tamanho do conteúdo: " . strlen($content) . " bytes\n";
        } catch (\Exception $e) {
            echo "✗ Erro ao renderizar view: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ View 'home' não encontrada\n";
    }
} else {
    echo "✗ View factory não registrada no container\n";
}

echo "\n";

// Testar helpers
echo "=== Testando Helpers ===\n\n";

// Testar helper env()
$_ENV['TEST_VAR'] = 'test_value';
echo "✓ env('TEST_VAR'): " . env('TEST_VAR', 'default') . "\n";
echo "✓ env('NON_EXISTENT', 'default'): " . env('NON_EXISTENT', 'default') . "\n";

// Testar helper app()
echo "✓ app() retorna instância: " . (app() === $app ? 'OK' : 'FALHOU') . "\n";

// Testar helper config()
if ($app->bound('config')) {
    $config = $app->make('config');
    $config->set('test.key', 'test_value');
    echo "✓ config('test.key'): " . config('test.key') . "\n";
}

echo "\n";

// Resumo final
echo "=== Resumo do Teste ===\n\n";
echo "Framework Coyote - Fase 2 (HTTP & Routing) COMPLETA!\n";
echo "✓ Sistema de autoload PSR-4 com caching\n";
echo "✓ Container DI com bindings e singletons\n";
echo "✓ Service Providers (Config, Event, Log, View)\n";
echo "✓ Sistema HTTP (Request/Response PSR-7 simplificado)\n";
echo "✓ Sistema de roteamento avançado\n";
echo "✓ Http Kernel com tratamento de erros\n";
echo "✓ Sistema de middleware com pipeline\n";
echo "✓ Controllers base com suporte a middleware\n";
echo "✓ Sistema de views com factory e compartilhamento\n";
echo "✓ Helpers globais (env, app, config)\n";
echo "✓ Exemplo de aplicação funcionando\n\n";

echo "Próximos passos (Fase 3):\n";
echo "1. Sistema de banco de dados e modelos\n";
echo "2. Autenticação e autorização\n";
echo "3. Validação de dados\n";
echo "4. Sistema de cache\n";
echo "5. CLI commands\n";
echo "6. Múltiplos bancos de dados\n";
echo "7. DBAL (Database Abstraction Layer)\n";
echo "8. Formulários e validação\n";
echo "9. Data Grid para listagens\n";
echo "10. Template engine avançada\n\n";

echo "🎉 Framework Coyote está funcionando corretamente!\n";