<?php
// test-schema-basic.php
// Teste básico do Schema Builder do Coyote Framework

echo "=== Teste do Schema Builder - Coyote Framework ===\n\n";

// 1. Configurar ambiente de teste
echo "1. Configurando ambiente de teste...\n";

require_once 'vendors/autoload.php';

// Configuração para SQLite em memória
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

try {
    $dbManager = new Coyote\Database\DatabaseManager($config);
    $connection = $dbManager->connection();
    
    echo "   ✓ Conexão SQLite criada com sucesso\n";
    
    // 2. Testar Schema Builder
    echo "\n2. Testando Schema Builder...\n";
    
    $schema = new Coyote\Database\Schema\Builder($connection);
    
    // 3. Criar tabela de teste
    echo "3. Criando tabela de teste...\n";
    
    $schema->create('test_users', function ($table) {
        $table->increments('id');
        $table->string('name', 100);
        $table->string('email', 255)->unique();
        $table->timestamps();
    });
    
    echo "   ✓ Tabela 'test_users' criada\n";
    
    // 4. Verificar se tabela existe
    echo "4. Verificando se tabela existe...\n";
    
    $tableExists = $schema->hasTable('test_users');
    echo "   " . ($tableExists ? "✓" : "✗") . " Tabela existe: " . ($tableExists ? "sim" : "não") . "\n";
    
    if (!$tableExists) {
        throw new Exception("Tabela não foi criada");
    }
    
    // 5. Adicionar coluna
    echo "5. Adicionando coluna à tabela...\n";
    
    $schema->table('test_users', function ($table) {
        $table->string('phone', 20)->nullable();
    });
    
    echo "   ✓ Coluna 'phone' adicionada\n";
    
    // 6. Listar colunas
    echo "6. Listando colunas da tabela...\n";
    
    $columns = $schema->getColumnListing('test_users');
    echo "   ✓ Colunas encontradas: " . implode(', ', $columns) . "\n";
    
    // 7. Excluir tabela
    echo "7. Excluindo tabela...\n";
    
    $schema->drop('test_users');
    
    // Verificar se tabela foi excluída
    $tableExistsAfterDrop = $schema->hasTable('test_users');
    echo "   " . (!$tableExistsAfterDrop ? "✓" : "✗") . " Tabela excluída: " . (!$tableExistsAfterDrop ? "sim" : "não") . "\n";
    
    if ($tableExistsAfterDrop) {
        throw new Exception("Tabela não foi excluída");
    }
    
    echo "\n✅ Schema Builder testado com sucesso!\n";
    
} catch (Exception $e) {
    echo "\n❌ Erro durante o teste: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}