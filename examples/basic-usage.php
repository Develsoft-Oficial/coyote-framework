<?php
/**
 * Exemplo Básico de Uso do Coyote Framework
 * 
 * Este exemplo demonstra como usar o Coyote Framework após instalação via Composer.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Coyote\Core\Application;
use Coyote\Http\Request;
use Coyote\Http\Response;
use Coyote\Database\Connection;
use Coyote\Auth\AuthManager;
use Coyote\Validation\Validator;
use Coyote\View\ViewFactory;

echo "=== Exemplo Básico do Coyote Framework ===\n\n";

// 1. Criar uma aplicação
echo "1. Criando aplicação...\n";
$app = new Application(__DIR__);
echo "   ✓ Aplicação criada\n\n";

// 2. Trabalhar com requisições HTTP
echo "2. Trabalhando com HTTP...\n";
$request = Request::createFromGlobals();
$response = new Response('Hello from Coyote Framework!', 200);

echo "   ✓ Request criada: " . $request->getMethod() . " " . $request->getUri() . "\n";
echo "   ✓ Response criada: " . $response->getStatusCode() . " " . $response->getContent() . "\n\n";

// 3. Conectar ao banco de dados
echo "3. Conectando ao banco de dados...\n";
try {
    $config = [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'test',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ];
    
    $connection = new Connection($config);
    echo "   ✓ Conexão configurada (exemplo)\n";
} catch (Exception $e) {
    echo "   ⚠️  Conexão falhou (esperado em ambiente de teste): " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Validar dados
echo "4. Validando dados...\n";
$validator = new Validator();
$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 25
];

$rules = [
    'name' => 'required|string|min:3',
    'email' => 'required|email',
    'age' => 'required|numeric|min:18'
];

if ($validator->validate($data, $rules)) {
    echo "   ✓ Dados válidos!\n";
    echo "   - Nome: " . $data['name'] . "\n";
    echo "   - Email: " . $data['email'] . "\n";
    echo "   - Idade: " . $data['age'] . "\n";
} else {
    echo "   ✗ Dados inválidos:\n";
    foreach ($validator->errors() as $field => $errors) {
        echo "   - $field: " . implode(', ', $errors) . "\n";
    }
}
echo "\n";

// 5. Autenticação
echo "5. Sistema de autenticação...\n";
$auth = new AuthManager();
echo "   ✓ AuthManager inicializado\n";
echo "   - Driver padrão: " . $auth->getDefaultDriver() . "\n\n";

// 6. Views
echo "6. Sistema de views...\n";
$viewFactory = new ViewFactory(__DIR__ . '/views');
echo "   ✓ ViewFactory inicializado\n";
echo "   - Diretório de views: " . $viewFactory->getViewsPath() . "\n\n";

// 7. Usando helpers do framework
echo "7. Usando helpers...\n";
if (function_exists('coyote_path')) {
    echo "   ✓ Helper coyote_path() disponível\n";
    echo "   - Base path: " . coyote_path() . "\n";
}

if (function_exists('config')) {
    echo "   ✓ Helper config() disponível\n";
}

echo "\n";

// 8. Resumo
echo "=== Resumo ===\n";
echo "O Coyote Framework foi carregado com sucesso via Composer!\n";
echo "Componentes testados:\n";
echo "✓ Application Core\n";
echo "✓ HTTP Request/Response\n";
echo "✓ Database Connection\n";
echo "✓ Validation System\n";
echo "✓ Authentication Manager\n";
echo "✓ View Factory\n";
echo "✓ Helpers\n\n";

echo "Para começar a desenvolver:\n";
echo "1. Crie um arquivo index.php\n";
echo "2. Configure suas rotas em routes/web.php\n";
echo "3. Execute `php -S localhost:8000 -t public`\n";
echo "4. Acesse http://localhost:8000\n\n";

echo "Documentação completa: https://github.com/coyoteframework/framework\n";
?>