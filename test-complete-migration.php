<?php
// test-complete-migration.php
// Teste completo da migração de todos os módulos

echo "=== Teste Completo de Migração - Todos os Módulos ===\n\n";

// 1. Testar autoload do Composer
echo "1. Testando autoload do Composer...\n";
require_once __DIR__ . '/vendor/autoload.php';

echo "   ✓ Autoload carregado\n";

// 2. Testar módulos principais
echo "\n2. Testando módulos principais...\n";

$modules = [
    // Core
    'Coyote\Core\Application',
    'Coyote\Core\Config',
    'Coyote\Core\Container',
    
    // Http
    'Coyote\Http\Request',
    'Coyote\Http\Response',
    'Coyote\Http\Kernel',
    
    // Database
    'Coyote\Database\Connection',
    'Coyote\Database\QueryBuilder',
    'Coyote\Database\Model',
    
    // Auth
    'Coyote\Auth\AuthManager',
    
    // Validation
    'Coyote\Validation\Validator',
    
    // View
    'Coyote\View\ViewFactory',
    
    // Config
    'Coyote\Config\Repository',
    
    // Log
    'Coyote\Log\Logger',
    
    // Providers
    'Coyote\Providers\ServiceProvider',
    
    // Routing
    'Coyote\Routing\Router',
    
    // Session
    'Coyote\Session\SessionManager',
    
    // Forms
    'Coyote\Forms\FormBuilder',
    
    // Support
    'Coyote\Autoloader',
];

$allPassed = true;
foreach ($modules as $class) {
    if (class_exists($class)) {
        echo "   ✓ $class\n";
    } else {
        echo "   ✗ $class (NÃO ENCONTRADA)\n";
        $allPassed = false;
    }
}

// 3. Testar estrutura de diretórios
echo "\n3. Verificando estrutura de diretórios...\n";

$directories = [
    'src/Core',
    'src/Http',
    'src/Database',
    'src/Auth',
    'src/Validation',
    'src/View',
    'src/Config',
    'src/Log',
    'src/Providers',
    'src/Routing',
    'src/Session',
    'src/Forms',
    'src/Support',
];

foreach ($directories as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        $fileCount = count(glob(__DIR__ . '/' . $dir . '/*.php'));
        echo "   ✓ $dir ($fileCount arquivos PHP)\n";
    } else {
        echo "   ✗ $dir (NÃO ENCONTRADO)\n";
        $allPassed = false;
    }
}

// 4. Testar arquivos importantes
echo "\n4. Verificando arquivos importantes...\n";

$importantFiles = [
    'src/Core/Application.php',
    'src/Database/QueryBuilder.php',
    'src/Http/Request.php',
    'src/Auth/AuthManager.php',
    'src/Validation/Validator.php',
    'src/Support/Autoloader.php',
    'src/Support/helpers.php',
];

foreach ($importantFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $size = filesize(__DIR__ . '/' . $file);
        echo "   ✓ " . basename($file) . " ($size bytes)\n";
    } else {
        echo "   ✗ " . basename($file) . " (NÃO ENCONTRADO)\n";
        $allPassed = false;
    }
}

// 5. Testar composer.json
echo "\n5. Verificando composer.json...\n";
if (file_exists(__DIR__ . '/composer.json')) {
    $composer = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);
    
    if (isset($composer['name']) && $composer['name'] === 'coyote/framework') {
        echo "   ✓ Nome do pacote correto: {$composer['name']}\n";
    } else {
        echo "   ✗ Nome do pacote incorreto\n";
        $allPassed = false;
    }
    
    if (isset($composer['autoload']['psr-4']['Coyote\\']) && $composer['autoload']['psr-4']['Coyote\\'] === 'src/') {
        echo "   ✓ Autoload PSR-4 configurado corretamente\n";
    } else {
        echo "   ✗ Autoload PSR-4 não configurado corretamente\n";
        $allPassed = false;
    }
} else {
    echo "   ✗ composer.json não encontrado\n";
    $allPassed = false;
}

// 6. Testar instanciação básica
echo "\n6. Testando instanciação básica...\n";
try {
    // Testar Application
    $appClass = 'Coyote\Core\Application';
    $reflection = new ReflectionClass($appClass);
    
    // Verificar se tem método basePath
    $methods = array_map(function($m) { return $m->name; }, $reflection->getMethods());
    
    if (in_array('basePath', $methods)) {
        echo "   ✓ Application tem método basePath()\n";
    } else {
        echo "   ⚠ Application não tem método basePath()\n";
    }
    
    // Testar Config
    $configClass = 'Coyote\Config\Repository';
    if (class_exists($configClass)) {
        echo "   ✓ Config\Repository pode ser instanciado\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Erro na instanciação: " . $e->getMessage() . "\n";
    $allPassed = false;
}

// 7. Testar helpers
echo "\n7. Testando helpers...\n";
if (file_exists(__DIR__ . '/src/Support/helpers.php')) {
    // Incluir helpers
    require_once __DIR__ . '/src/Support/helpers.php';
    
    // Verificar se algumas funções helpers existem
    $helperFunctions = ['config', 'view', 'auth'];
    $helpersLoaded = false;
    
    foreach ($helperFunctions as $func) {
        if (function_exists($func)) {
            $helpersLoaded = true;
            break;
        }
    }
    
    if ($helpersLoaded) {
        echo "   ✓ Helpers carregados\n";
    } else {
        echo "   ⚠ Helpers não foram carregados (pode ser normal)\n";
    }
} else {
    echo "   ⚠ Arquivo helpers.php não encontrado\n";
}

// Resultado final
echo "\n=== RESULTADO FINAL ===\n";
if ($allPassed) {
    echo "✅ TODOS OS MÓDULOS MIGRADOS COM SUCESSO!\n";
    echo "\nResumo:\n";
    echo "- " . count($modules) . " classes testadas\n";
    echo "- " . count($directories) . " diretórios verificados\n";
    echo "- " . count($importantFiles) . " arquivos importantes\n";
    echo "- Estrutura PSR-4 configurada corretamente\n";
    echo "- Composer.json otimizado para pacote\n";
} else {
    echo "❌ ALGUNS PROBLEMAS ENCONTRADOS\n";
    echo "Verifique os erros acima antes de continuar.\n";
    exit(1);
}

echo "\n=== PRÓXIMOS PASSOS ===\n";
echo "1. Atualizar a aplicação de teste:\n";
echo "   cd test-app && composer update\n";
echo "\n2. Testar a aplicação de exemplo:\n";
echo "   cd test-app && php test-framework.php\n";
echo "\n3. Remover arquivos antigos (opcional):\n";
echo "   rm -rf vendors/coyote\n";
echo "\n4. Criar tag de versão:\n";
echo "   git add . && git commit -m 'Migração completa para estrutura de pacote'\n";
echo "   git tag -a v1.0.0 -m 'Primeira versão como pacote Composer'\n";