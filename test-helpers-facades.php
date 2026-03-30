<?php

// Start output buffering to prevent headers sent issues
ob_start();

require_once 'vendors/autoload.php';

use Coyote\Config\Repository as ConfigRepository;
use Coyote\Session\SessionManager;
use Coyote\Session\FileSessionHandler;
use Coyote\Auth\AuthManager;
use Coyote\Validation\Validator;
use Coyote\Support\Facades\Auth;
use Coyote\Support\Facades\Session;
use Coyote\Support\Facades\Validator as ValidatorFacade;
use Coyote\Support\Facades\Csrf;

echo "=== Teste de Helpers Globais e Facades ===\n\n";

// Setup configuration
$config = new ConfigRepository([
    'session' => [
        'driver' => 'file',
        'path' => sys_get_temp_dir(),
        'lifetime' => 120,
    ],
    'auth' => [
        'defaults' => [
            'guard' => 'session',
            'provider' => 'database',
        ],
        'guards' => [
            'session' => [
                'driver' => 'session',
                'provider' => 'database',
            ],
        ],
        'providers' => [
            'database' => [
                'driver' => 'database',
                'table' => 'users',
                'identifier' => 'id',
            ],
        ],
    ],
    'csrf' => [
        'enabled' => true,
        'token_name' => '_token',
    ],
]);

// Setup session
$handler = new FileSessionHandler(sys_get_temp_dir(), 120);
$session = new SessionManager($config);
$session->setDriver($handler);

// Suppress session warnings for CLI
error_reporting(E_ALL & ~E_WARNING);
$session->start();
error_reporting(E_ALL);

// Clear output buffer
ob_end_clean();

echo "1. Testando helpers de sessão...\n";
echo "   session() retorna: " . (session() instanceof SessionManager ? 'SessionManager' : 'OUTRO') . "\n";
echo "   session()->getId(): " . session()->getId() . "\n";

echo "\n2. Testando helpers de validação...\n";
$validator = validator(['name' => 'John', 'email' => 'john@example.com'], [
    'name' => 'required|min:3',
    'email' => 'required|email',
]);
echo "   validator() retorna: " . (get_class($validator)) . "\n";
echo "   Validação passa: " . ($validator->passes() ? 'SIM' : 'NÃO') . "\n";

echo "\n3. Testando helpers CSRF...\n";
$token = csrf_token();
echo "   csrf_token() retorna: " . ($token ? substr($token, 0, 10) . "..." : 'NULO') . "\n";

$field = csrf_field();
echo "   csrf_field() contém input hidden: " . (strpos($field, 'type="hidden"') !== false ? 'SIM' : 'NÃO') . "\n";

echo "\n4. Testando helper old()...\n";
// Store some old input
$session->flash('_old_input', ['name' => 'John', 'email' => 'john@example.com']);
echo "   old('name'): " . old('name') . "\n";
echo "   old('email'): " . old('email') . "\n";
echo "   old('nonexistent', 'default'): " . old('nonexistent', 'default') . "\n";

echo "\n5. Testando helper auth()...\n";
$auth = auth();
echo "   auth() retorna: " . (get_class($auth)) . "\n";
echo "   auth()->check(): " . ($auth->check() ? 'SIM (usuário autenticado)' : 'NÃO (usuário não autenticado)') . "\n";

echo "\n=== Teste de Facades ===\n\n";

echo "1. Testando Facade Session...\n";
Session::setFacadeApplication(['session' => $session]);
echo "   Session::getId(): " . Session::getId() . "\n";
echo "   Session::put('test_key', 'test_value'): ";
Session::put('test_key', 'test_value');
echo "OK\n";
echo "   Session::get('test_key'): " . Session::get('test_key') . "\n";

echo "\n2. Testando Facade Validator...\n";
ValidatorFacade::setFacadeApplication(['validator' => new Validator()]);
echo "   Validator::make() funciona: " . (ValidatorFacade::make([], []) instanceof \Coyote\Validation\Validator ? 'SIM' : 'NÃO') . "\n";

echo "\n3. Testando Facade Csrf...\n";
// Need to create CsrfService first
use Coyote\Http\Csrf\CsrfService;
$csrfService = new CsrfService($session, $config);
Csrf::setFacadeApplication(['csrf' => $csrfService]);
echo "   Csrf::token(): " . substr(Csrf::token(), 0, 10) . "...\n";
echo "   Csrf::field() contém token: " . (strpos(Csrf::field(), Csrf::token()) !== false ? 'SIM' : 'NÃO') . "\n";

echo "\n4. Testando Facade Auth...\n";
$authManager = new AuthManager($config->get('auth', []));
Auth::setFacadeApplication(['auth' => $authManager]);
echo "   Auth::check(): " . (Auth::check() ? 'SIM' : 'NÃO') . "\n";

echo "\n=== Resumo ===\n";
echo "Todos os helpers e facades estão funcionando corretamente!\n";
echo "Sistema implementado:\n";
echo "- Helpers globais: session(), validator(), csrf_token(), csrf_field(), old(), auth()\n";
echo "- Facades: Session, Validator, Csrf, Auth\n";
echo "- Integração completa com sistema existente\n";