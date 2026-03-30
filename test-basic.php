<?php
// test-basic.php
// Script para testar a estrutura básica do framework

echo "=== Teste da Estrutura Básica do Coyote Framework ===\n\n";

// 1. Verificar autoloader
echo "1. Testando autoloader...\n";
require_once 'vendors/autoload.php';

if (class_exists('Coyote\Autoloader')) {
    echo "   ✓ Autoloader carregado com sucesso\n";
    
    // Verificar se está registrado
    $prefixes = Coyote\Autoloader::getPrefixes();
    if (!empty($prefixes)) {
        echo "   ✓ Namespaces registrados: " . count($prefixes) . "\n";
    } else {
        echo "   ✗ Nenhum namespace registrado\n";
    }
} else {
    echo "   ✗ Falha ao carregar autoloader\n";
    exit(1);
}

// 2. Testar Application
echo "\n2. Testando Application...\n";
try {
    $app = new Coyote\Core\Application(__DIR__);
    echo "   ✓ Application instanciada com sucesso\n";
    echo "   ✓ Base path: " . $app->basePath() . "\n";
    echo "   ✓ Version: " . $app->version() . "\n";
    
    // Verificar se container está funcionando
    if ($app->bound('app')) {
        echo "   ✓ Container bindings funcionando\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erro ao criar Application: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Testar Config
echo "\n3. Testando Config...\n";
try {
    $config = $app->make('config');
    echo "   ✓ Config resolvida do container\n";
    
    // Carregar configuração
    $config->loadFromPath(__DIR__ . '/config');
    
    // Testar acesso
    $appName = $config->get('app.name', 'Não encontrado');
    echo "   ✓ App name: " . $appName . "\n";
    
    // Testar ArrayAccess
    $env = $config['app.env'] ?? 'Não encontrado';
    echo "   ✓ App env: " . $env . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Erro no Config: " . $e->getMessage() . "\n";
}

// 4. Testar Service Providers
echo "\n4. Testando Service Providers...\n";
$providers = $app->getProviders();
echo "   ✓ Providers registrados: " . count($providers) . "\n";

foreach ($providers as $provider) {
    echo "   - " . get_class($provider) . "\n";
}

// 5. Testar boot da aplicação
echo "\n5. Testando boot da aplicação...\n";
try {
    $app->boot();
    echo "   ✓ Aplicação bootada com sucesso\n";
    echo "   ✓ Booted: " . ($app->isBooted() ? 'Sim' : 'Não') . "\n";
} catch (Exception $e) {
    echo "   ✗ Erro no boot: " . $e->getMessage() . "\n";
}

// 6. Testar execução básica
echo "\n6. Testando execução básica...\n";
try {
    $response = $app->run();
    echo "   ✓ Execução retornou: " . gettype($response) . "\n";
    
    if (is_string($response)) {
        echo "   ✓ Conteúdo: " . substr($response, 0, 50) . "...\n";
    }
} catch (Exception $e) {
    echo "   ✗ Erro na execução: " . $e->getMessage() . "\n";
}

// 7. Verificar estrutura de diretórios
echo "\n7. Verificando estrutura de diretórios...\n";
$requiredDirs = [
    'public',
    'vendors/coyote',
    'app',
    'config',
    'storage',
    'routes',
    'modules',
    'tests'
];

foreach ($requiredDirs as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        echo "   ✓ " . $dir . "/\n";
    } else {
        echo "   ✗ " . $dir . "/ (não encontrado)\n";
    }
}

// 8. Verificar arquivos essenciais
echo "\n8. Verificando arquivos essenciais...\n";
$requiredFiles = [
    'public/index.php',
    'vendors/autoload.php',
    'vendors/coyote/Core/Application.php',
    'vendors/coyote/Core/Container.php',
    'vendors/coyote/Core/Config.php',
    'composer.json',
    'config/app.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✓ " . $file . "\n";
    } else {
        echo "   ✗ " . $file . " (não encontrado)\n";
    }
}

echo "\n=== Teste Concluído ===\n";
echo "Status: " . (isset($e) ? "FALHA" : "SUCESSO") . "\n";

if (!isset($e)) {
    echo "\n🎉 Estrutura básica do Coyote Framework está funcionando!\n";
    echo "Próximos passos:\n";
    echo "1. Executar 'composer install' para instalar dependências\n";
    echo "2. Configurar servidor web apontando para 'public/'\n";
    echo "3. Acessar http://localhost para ver a aplicação\n";
    echo "4. Implementar sistema de roteamento e controllers\n";
}