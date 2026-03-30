<?php
// test-migration-partial.php
// Teste da migração parcial do módulo Database

echo "=== Teste de Migração Parcial - Módulo Database ===\n\n";

// 1. Testar autoload do Composer
echo "1. Testando autoload do Composer...\n";
require_once __DIR__ . '/vendor/autoload.php';

// 2. Testar se as classes do Database estão disponíveis
echo "2. Testando classes do Database...\n";

$classes = [
    'Coyote\Database\Connection',
    'Coyote\Database\DatabaseManager',
    'Coyote\Database\QueryBuilder',
    'Coyote\Database\Model',
    'Coyote\Database\ModelCollection',
    'Coyote\Database\Migrations\Migration',
    'Coyote\Database\Migrations\MigrationRepository',
    'Coyote\Database\Migrations\Migrator',
];

$allPassed = true;
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "   ✓ $class\n";
    } else {
        echo "   ✗ $class (NÃO ENCONTRADA)\n";
        $allPassed = false;
    }
}

// 3. Testar instanciação básica
echo "\n3. Testando instanciação básica...\n";
try {
    // Testar QueryBuilder
    $reflection = new ReflectionClass('Coyote\Database\QueryBuilder');
    echo "   ✓ QueryBuilder pode ser refletido\n";
    
    // Verificar métodos públicos
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    $methodCount = count($methods);
    echo "   ✓ QueryBuilder tem $methodCount métodos públicos\n";
    
} catch (Exception $e) {
    echo "   ✗ Erro ao refletir QueryBuilder: " . $e->getMessage() . "\n";
    $allPassed = false;
}

// 4. Testar estrutura de arquivos
echo "\n4. Verificando estrutura de arquivos...\n";
$files = [
    __DIR__ . '/src/Database/Connection.php',
    __DIR__ . '/src/Database/QueryBuilder.php',
    __DIR__ . '/src/Database/Model.php',
    __DIR__ . '/src/Database/Migrations/Migration.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "   ✓ " . basename(dirname($file)) . "/" . basename($file) . " ($size bytes)\n";
    } else {
        echo "   ✗ " . basename(dirname($file)) . "/" . basename($file) . " (NÃO ENCONTRADO)\n";
        $allPassed = false;
    }
}

// 5. Testar namespaces
echo "\n5. Verificando namespaces...\n";
$testFile = __DIR__ . '/src/Database/QueryBuilder.php';
$content = file_get_contents($testFile);
if (strpos($content, 'namespace Coyote\Database;') !== false) {
    echo "   ✓ Namespace correto em QueryBuilder.php\n";
} else {
    echo "   ✗ Namespace incorreto em QueryBuilder.php\n";
    $allPassed = false;
}

// Resultado final
echo "\n=== RESULTADO FINAL ===\n";
if ($allPassed) {
    echo "✅ TODOS OS TESTES PASSARAM!\n";
    echo "O módulo Database foi migrado com sucesso para a estrutura de pacote.\n";
} else {
    echo "❌ ALGUNS TESTES FALHARAM\n";
    echo "Verifique os problemas acima antes de continuar.\n";
    exit(1);
}

echo "\n=== PRÓXIMOS PASSOS ===\n";
echo "1. Execute o script de desenvolvimento symlink:\n";
echo "   powershell -ExecutionPolicy Bypass -File scripts/setup-dev-symlink.ps1\n";
echo "\n2. Crie uma aplicação de teste para validar o funcionamento:\n";
echo "   cd .. && composer create-project coyote/example-app test-app\n";
echo "\n3. Teste a integração completa:\n";
echo "   cd test-app && php -S localhost:8000 -t public\n";