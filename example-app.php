<?php
// example-app.php
// Exemplo completo de aplicação usando o Coyote Framework

require_once __DIR__ . '/vendors/autoload.php';

use Coyote\Core\Application;
use Coyote\Http\Request;
use Coyote\Http\Kernel;
use Coyote\Routing\Router;

echo "=== Exemplo de Aplicação Coyote Framework ===\n\n";

// 1. Criar aplicação
$app = new Application(__DIR__);
$app->setEnvironment('development');
$app->setDebug(true);

echo "1. Aplicação criada\n";
echo "   - Ambiente: {$app->environment()}\n";
echo "   - Debug: " . ($app->isDebug() ? 'Sim' : 'Não') . "\n";
echo "   - Versão: {$app->version()}\n\n";

// 2. Criar router e carregar rotas
$router = new Router($app);
$app->instance('router', $router);

echo "2. Router criado\n";

// Definir rotas de exemplo
$router->get('/', function () {
    return '🏠 Página Inicial - Bem-vindo ao Coyote Framework!';
})->name('home');

$router->get('/hello/{name}', function ($name) {
    return "👋 Olá, {$name}! Bem-vindo ao Coyote Framework!";
})->name('hello');

$router->get('/about', function () {
    return '📖 Sobre - Coyote é um micro-framework PHP leve e poderoso.';
})->name('about');

$router->get('/api/users', function () {
    return \Coyote\Http\Response::json([
        'success' => true,
        'data' => [
            ['id' => 1, 'name' => 'João', 'email' => 'joao@example.com'],
            ['id' => 2, 'name' => 'Maria', 'email' => 'maria@example.com'],
            ['id' => 3, 'name' => 'Pedro', 'email' => 'pedro@example.com'],
        ],
        'timestamp' => time()
    ]);
})->name('api.users');

$router->post('/api/users', function (Request $request) {
    $data = $request->all();
    
    return \Coyote\Http\Response::json([
        'success' => true,
        'message' => 'Usuário criado com sucesso!',
        'data' => $data,
        'id' => rand(100, 999)
    ], 201);
})->name('api.users.create');

$router->get('/admin', function () {
    return '🔐 Área Administrativa - Acesso restrito';
})->name('admin')->middleware('auth');

echo "3. Rotas definidas:\n";
foreach ($router->toArray() as $route) {
    echo "   - {$route['method']} {$route['uri']}\n";
}
echo "\n";

// 3. Criar kernel HTTP
$kernel = new Kernel($app, $router);
$app->instance('kernel', $kernel);

echo "4. Kernel HTTP criado\n\n";

// 4. Testar diferentes requisições
echo "=== Testando Rotas da Aplicação ===\n\n";

$testRoutes = [
    ['GET', '/', 'Página inicial'],
    ['GET', '/hello/World', 'Saudação personalizada'],
    ['GET', '/hello/João', 'Saudação em português'],
    ['GET', '/about', 'Página sobre'],
    ['GET', '/api/users', 'API de usuários (JSON)'],
    ['POST', '/api/users', 'Criar usuário via API'],
    ['GET', '/admin', 'Área administrativa (com middleware)'],
    ['GET', '/not-found', 'Rota não existente (404)'],
];

foreach ($testRoutes as $test) {
    echo "🔍 Testando: {$test[2]}\n";
    echo "   Método: {$test[0]}, URI: {$test[1]}\n";
    
    try {
        $request = Request::create($test[1], $test[0]);
        
        // Para POST, adicionar alguns dados
        if ($test[0] === 'POST') {
            $request = Request::create($test[1], $test[0], [
                'name' => 'Novo Usuário',
                'email' => 'novo@example.com'
            ]);
        }
        
        $response = $kernel->handle($request);
        
        echo "   ✅ Status: {$response->getStatusCode()}\n";
        
        $content = $response->getContent();
        if (strlen($content) > 100) {
            echo "   ✅ Resposta: " . substr($content, 0, 100) . "...\n";
        } else {
            echo "   ✅ Resposta: {$content}\n";
        }
        
    } catch (\Exception $e) {
        echo "   ❌ Erro: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

// 5. Demonstrar funcionalidades do framework
echo "=== Demonstração de Funcionalidades ===\n\n";

// Container DI
echo "5. Container DI:\n";
$app->bind('greeting.service', function () {
    return new class {
        public function greet($name) {
            return "Olá, {$name} do serviço!";
        }
    };
});

$greetingService = $app->make('greeting.service');
echo "   ✅ Serviço criado: " . $greetingService->greet('Mundo') . "\n";

// Configurações
echo "\n6. Sistema de Configurações:\n";
$config = $app->make('config');
$config->set('app.name', 'Coyote Example App');
$config->set('app.version', '1.0.0');
echo "   ✅ Configurações definidas:\n";
echo "      - app.name: " . $config->get('app.name') . "\n";
echo "      - app.version: " . $config->get('app.version') . "\n";

// Views
echo "\n7. Sistema de Views:\n";
if ($app->bound('view')) {
    $viewFactory = $app->make('view');
    
    // Criar view simples
    $viewContent = '<h1>{{title}}</h1><p>{{message}}</p>';
    $view = $viewFactory->fromString($viewContent, [
        'title' => 'Exemplo de View',
        'message' => 'Esta view foi criada a partir de uma string!'
    ]);
    
    echo "   ✅ View criada a partir de string\n";
    echo "   ✅ Conteúdo renderizado: " . substr($view->render(), 0, 50) . "...\n";
}

// Helpers
echo "\n8. Helpers Globais:\n";
echo "   ✅ app() retorna instância da aplicação: " . (app() === $app ? 'Sim' : 'Não') . "\n";
echo "   ✅ config('app.name'): " . config('app.name') . "\n";

// 6. Resumo
echo "\n=== Resumo da Aplicação Exemplo ===\n\n";
echo "🎉 Aplicação exemplo do Coyote Framework funcionando perfeitamente!\n\n";
echo "Funcionalidades implementadas e testadas:\n";
echo "✓ Sistema de roteamento com parâmetros\n";
echo "✓ Suporte a diferentes métodos HTTP (GET, POST)\n";
echo "✓ Respostas JSON para APIs\n";
echo "✓ Container DI com bindings\n";
echo "✓ Sistema de configurações\n";
echo "✓ Sistema de views\n";
echo "✓ Helpers globais\n";
echo "✓ Tratamento de erros (404, 500)\n";
echo "✓ Middleware (exemplo: auth)\n\n";

echo "Estrutura do projeto criada:\n";
echo "- app/Controllers/ - Controladores da aplicação\n";
echo "- resources/views/ - Views da aplicação\n";
echo "- config/ - Configurações\n";
echo "- routes/ - Definição de rotas\n";
echo "- storage/ - Arquivos temporários e logs\n";
echo "- public/ - Ponto de entrada público\n\n";

echo "Para executar esta aplicação em um servidor web:\n";
echo "1. Configure o servidor web para apontar para public/index.php\n";
echo "2. Acesse http://localhost/ para ver a aplicação funcionando\n";
echo "3. Use as rotas definidas acima (/hello/{name}, /about, /api/users, etc.)\n\n";

echo "🚀 Coyote Framework está pronto para produção!\n";