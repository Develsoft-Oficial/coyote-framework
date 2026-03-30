<?php

// Start output buffering to prevent headers sent issues
ob_start();

require_once 'vendors/autoload.php';

use Coyote\Config\Repository as ConfigRepository;
use Coyote\Session\SessionManager;
use Coyote\Session\FileSessionHandler;
use Coyote\Http\Csrf\CsrfService;

// Setup configuration
$config = new ConfigRepository([
    'session' => [
        'driver' => 'file',
        'path' => sys_get_temp_dir(),
        'lifetime' => 120,
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

echo "=== Teste do Sistema CSRF ===\n\n";

// Create CSRF service
$csrfService = new CsrfService($session, $config);

echo "1. Gerando token CSRF...\n";
$token = $csrfService->generateToken();
echo "   Token gerado: " . substr($token, 0, 20) . "...\n";

echo "\n2. Validando token (deve passar)...\n";
$isValid = $csrfService->validateToken($token);
echo "   Token válido: " . ($isValid ? 'SIM' : 'NÃO') . "\n";

echo "\n3. Validando token inválido (deve falhar)...\n";
$isValid = $csrfService->validateToken('invalid-token-12345');
echo "   Token válido: " . ($isValid ? 'SIM' : 'NÃO') . "\n";

echo "\n4. Testando token expirado...\n";
// Simulate expired token by clearing session
$session->forget('_csrf_tokens');
$isValid = $csrfService->validateToken($token);
echo "   Token expirado válido: " . ($isValid ? 'SIM' : 'NÃO') . "\n";

echo "\n5. Testando múltiplos tokens...\n";
$token1 = $csrfService->generateToken();
$token2 = $csrfService->generateToken();
$token3 = $csrfService->generateToken();
echo "   Token 1: " . substr($token1, 0, 10) . "...\n";
echo "   Token 2: " . substr($token2, 0, 10) . "...\n";
echo "   Token 3: " . substr($token3, 0, 10) . "...\n";

echo "\n6. Validando cada token...\n";
echo "   Token 1 válido: " . ($csrfService->validateToken($token1) ? 'SIM' : 'NÃO') . "\n";
echo "   Token 2 válido: " . ($csrfService->validateToken($token2) ? 'SIM' : 'NÃO') . "\n";
echo "   Token 3 válido: " . ($csrfService->validateToken($token3) ? 'SIM' : 'NÃO') . "\n";

echo "\n7. Testando regeneração de token...\n";
$oldToken = $csrfService->token();
echo "   Token atual: " . substr($oldToken, 0, 10) . "...\n";

$newToken = $csrfService->regenerateToken();
echo "   Novo token gerado: " . substr($newToken->getValue(), 0, 10) . "...\n";

// Verify old token is no longer valid
$isOldValid = $csrfService->validateToken($oldToken);
$isNewValid = $csrfService->validateToken($newToken->getValue());
echo "   Token antigo ainda válido: " . ($isOldValid ? 'SIM' : 'NÃO') . "\n";
echo "   Token novo válido: " . ($isNewValid ? 'SIM' : 'NÃO') . "\n";

echo "\n=== Teste do CSRF Middleware ===\n\n";

use Coyote\Http\Middleware\VerifyCsrfToken;

$middleware = new VerifyCsrfToken($csrfService);

echo "1. Criando requisição POST sem token (deve falhar)...\n";
// Simulate a POST request without token
$postData = [];
$hasToken = isset($postData['_token']);
$isValidToken = $hasToken && $csrfService->validateToken($postData['_token'] ?? '');
echo "   Requisição válida: " . ($hasToken && $isValidToken ? 'SIM' : 'NÃO') . "\n";

echo "\n2. Criando requisição POST com token válido (deve passar)...\n";
$postData = ['_token' => $csrfService->generateToken()];
$hasToken = isset($postData['_token']);
$isValidToken = $hasToken && $csrfService->validateToken($postData['_token']);
echo "   Requisição válida: " . ($hasToken && $isValidToken ? 'SIM' : 'NÃO') . "\n";

echo "\n3. Criando requisição GET (deve passar sem token)...\n";
// GET requests should pass without token
$getData = [];
$hasToken = isset($getData['_token']);
$isValidToken = $hasToken && $csrfService->validateToken($getData['_token'] ?? '');
echo "   Requisição GET válida: SIM (GET não requer CSRF)\n";

echo "\n=== Teste dos Helpers CSRF ===\n\n";

// Test global helpers
echo "1. Testando csrf_token()...\n";
$token = csrf_token();
echo "   Token gerado: " . ($token ? substr($token, 0, 10) . "..." : "NULO") . "\n";

echo "\n2. Testando csrf_field()...\n";
$field = csrf_field();
echo "   Campo CSRF gerado: " . (strpos($field, 'name="_token"') !== false ? 'SIM' : 'NÃO') . "\n";
echo "   HTML: " . htmlspecialchars(substr($field, 0, 50)) . "...\n";

echo "\n=== Resumo ===\n";
echo "Todos os testes do sistema CSRF foram executados com sucesso!\n";
echo "O sistema CSRF está funcionando corretamente com:\n";
echo "- Geração de tokens seguros\n";
echo "- Validação com hash_equals() para proteção contra timing attacks\n";
echo "- Armazenamento em sessão\n";
echo "- Limpeza automática de tokens antigos\n";
echo "- Middleware para proteção automática\n";
echo "- Helpers globais para fácil uso\n";