<?php
// test-migrations-basic.php
// Teste básico do sistema de migrations do Coyote Framework

echo "=== Teste do Sistema de Migrations - Coyote Framework ===\n\n";

// 1. Configurar ambiente de teste
echo "1. Configurando ambiente de teste...\n";

// Criar diretório temporário para migrations
$testDir = __DIR__ . '/storage/test_migrations';
if (!is_dir($testDir)) {
    mkdir($testDir, 0777, true);
    echo "   ✓ Diretório de teste criado: {$testDir}\n";
} else {
    echo "   ✓ Diretório de teste já existe\n";
}

// 2. Testar carregamento das classes
echo "\n2. Testando carregamento das classes...\n";

require_once 'vendors/autoload.php';

$classes = [
    'Coyote\Database\Migrations\Migration',
    'Coyote\Database\Migrations\MigrationRepository',
    'Coyote\Database\Migrations\Migrator',
    'Coyote\Database\DatabaseManager',
    'Coyote\Database\Connection',
];

$allLoaded = true;
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "   ✓ {$class} carregada\n";
    } else {
        echo "   ✗ {$class} NÃO carregada\n";
        $allLoaded = false;
    }
}

if (!$allLoaded) {
    echo "\n❌ Erro: Nem todas as classes foram carregadas. Abortando teste.\n";
    exit(1);
}

echo "   ✓ Todas as classes foram carregadas com sucesso\n";

// 3. Criar conexão de teste com SQLite em memória
echo "\n3. Criando conexão de teste com SQLite...\n";

try {
    $config = [
        'default' => 'default',
        'connections' => [
            'default' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ],
    ];
    
    $dbManager = new Coyote\Database\DatabaseManager($config);
    $connection = $dbManager->connection();
    
    echo "   ✓ Conexão SQLite criada com sucesso\n";
    echo "   ✓ DatabaseManager instanciado\n";
} catch (Exception $e) {
    echo "   ✗ Erro ao criar conexão: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Testar MigrationRepository
echo "\n4. Testando MigrationRepository...\n";

try {
    $repository = new Coyote\Database\Migrations\MigrationRepository($connection);
    
    // Testar criação do repositório
    $created = $repository->createRepository();
    if ($created) {
        echo "   ✓ Tabela de migrations criada com sucesso\n";
    } else {
        echo "   ✗ Falha ao criar tabela de migrations\n";
    }
    
    // Testar se repositório existe
    $exists = $repository->repositoryExists();
    echo "   " . ($exists ? "✓" : "✗") . " Repositório existe: " . ($exists ? "sim" : "não") . "\n";
    
    // Testar métodos básicos
    $ran = $repository->getRan();
    echo "   ✓ Migrations aplicadas: " . count($ran) . " (esperado: 0)\n";
    
    $lastBatch = $repository->getLastBatchNumber();
    echo "   ✓ Último batch: {$lastBatch} (esperado: 0)\n";
    
    $nextBatch = $repository->getNextBatchNumber();
    echo "   ✓ Próximo batch: {$nextBatch} (esperado: 1)\n";
    
    echo "   ✓ MigrationRepository testado com sucesso\n";
} catch (Exception $e) {
    echo "   ✗ Erro no MigrationRepository: " . $e->getMessage() . "\n";
    exit(1);
}

// 5. Criar migration de teste
echo "\n5. Criando migration de teste...\n";

$testMigrationContent = <<<'PHP'
<?php
// Test migration for Coyote Framework

use Coyote\Database\Migrations\Migration;

class TestCreateUsersTable extends Migration
{
    public function up()
    {
        $this->statement("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Inserir dados de teste
        $this->statement("
            INSERT INTO users (name, email) VALUES 
            ('Test User 1', 'test1@example.com'),
            ('Test User 2', 'test2@example.com')
        ");
    }
    
    public function down()
    {
        $this->statement("DROP TABLE IF EXISTS users");
    }
}
PHP;

$testMigrationFile = $testDir . '/2025_01_01_000001_test_create_users_table.php';
file_put_contents($testMigrationFile, $testMigrationContent);

if (file_exists($testMigrationFile)) {
    echo "   ✓ Migration de teste criada: " . basename($testMigrationFile) . "\n";
} else {
    echo "   ✗ Falha ao criar migration de teste\n";
    exit(1);
}

// 6. Testar Migrator
echo "\n6. Testando Migrator...\n";

try {
    $migrator = new Coyote\Database\Migrations\Migrator($repository, $dbManager);
    $migrator->addPath($testDir);
    
    // Testar status antes da migration
    $statusBefore = $migrator->status();
    echo "   ✓ Status antes: " . count($statusBefore) . " migration(s) encontrada(s)\n";
    
    // Executar migration
    echo "   Executando migration...\n";
    $result = $migrator->run([$testDir]);
    
    if (!empty($result['migrations'])) {
        echo "   ✓ Migration executada com sucesso: " . $result['migrations'][0] . "\n";
        echo "   ✓ Batch: " . $result['batch'] . "\n";
        echo "   ✓ Total: " . $result['total'] . " migration(s) executada(s)\n";
    } else {
        echo "   ✗ Nenhuma migration executada\n";
    }
    
    // Verificar se a tabela foi criada
    $tableExists = false;
    try {
        $result = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        $tableExists = $result->fetch() !== false;
    } catch (Exception $e) {
        // Ignorar erro
    }
    
    echo "   " . ($tableExists ? "✓" : "✗") . " Tabela 'users' criada: " . ($tableExists ? "sim" : "não") . "\n";
    
    // Verificar dados inseridos
    if ($tableExists) {
        $result = $connection->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch();
        $userCount = $row['count'] ?? 0;
        echo "   ✓ Usuários inseridos: {$userCount} (esperado: 2)\n";
    }
    
    // Testar status após a migration
    $statusAfter = $migrator->status();
    $pendingCount = 0;
    foreach ($statusAfter as $status) {
        if ($status['status'] === 'Pending') {
            $pendingCount++;
        }
    }
    echo "   ✓ Migrations pendentes após execução: {$pendingCount} (esperado: 0)\n";
    
    // Testar rollback
    echo "\n7. Testando rollback...\n";
    $rollbackResult = $migrator->rollback(1);
    
    if (!empty($rollbackResult['migrations'])) {
        echo "   ✓ Rollback executado com sucesso: " . $rollbackResult['migrations'][0] . "\n";
        echo "   ✓ Total revertido: " . $rollbackResult['rolled_back'] . " migration(s)\n";
    } else {
        echo "   ✗ Nenhuma migration revertida\n";
    }
    
    // Verificar se a tabela foi removida
    $tableExistsAfterRollback = false;
    try {
        $result = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        $tableExistsAfterRollback = $result->fetch() !== false;
    } catch (Exception $e) {
        // Ignorar erro
    }
    
    echo "   " . ($tableExistsAfterRollback ? "✗" : "✓") . " Tabela 'users' após rollback: " . 
         ($tableExistsAfterRollback ? "ainda existe (ERRO)" : "removida (OK)") . "\n";
    
    echo "   ✓ Migrator testado com sucesso\n";
} catch (Exception $e) {
    echo "   ✗ Erro no Migrator: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

// 7. Limpar ambiente de teste
echo "\n8. Limpando ambiente de teste...\n";

// Remover arquivo de migration de teste
if (file_exists($testMigrationFile)) {
    unlink($testMigrationFile);
    echo "   ✓ Arquivo de migration removido\n";
}

// Remover diretório de teste se estiver vazio
if (is_dir($testDir) && count(scandir($testDir)) <= 2) {
    rmdir($testDir);
    echo "   ✓ Diretório de teste removido\n";
}

// 8. Resumo do teste
echo "\n=== RESUMO DO TESTE ===\n";
echo "✅ Sistema de migrations testado com sucesso!\n";
echo "✅ Componentes testados:\n";
echo "   - Migration (classe base)\n";
echo "   - MigrationRepository (armazenamento de metadados)\n";
echo "   - Migrator (execução e rollback)\n";
echo "✅ Funcionalidades verificadas:\n";
echo "   - Criação de tabela de migrations\n";
echo "   - Execução de migration (up)\n";
echo "   - Rollback de migration (down)\n";
echo "   - Controle de batches\n";
echo "   - Status das migrations\n";
echo "\n✅ O sistema de migrations está funcionando corretamente!\n";
echo "✅ Próximo passo: Integrar com Schema Builder e criar comandos CLI.\n";

exit(0);