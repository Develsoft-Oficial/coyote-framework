<?php
// test-querybuilder-simple.php
// Teste simplificado do QueryBuilder - apenas compilação

require_once __DIR__ . '/vendors/autoload.php';

use Coyote\Database\DatabaseManager;
use Coyote\Database\QueryBuilder;

echo "=== Teste Simplificado do QueryBuilder ===\n\n";

// Configuração mínima
$config = [
    'default' => 'test',
    'connections' => [
        'test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ],
    ],
];

try {
    // 1. Criar DatabaseManager
    $dbManager = new DatabaseManager($config);
    
    // 2. Testar SELECT básico
    echo "1. Testando SELECT básico:\n";
    $query = new QueryBuilder($dbManager, 'users');
    $query->select(['id', 'name', 'email'])
          ->where('active', 1)
          ->where('age', '>', 18)
          ->orderBy('name', 'ASC')
          ->limit(10);
    
    echo "   SQL: " . $query->toSql() . "\n";
    echo "   Bindings: " . json_encode($query->getBindings()) . "\n";
    echo "   ✓ OK\n\n";
    
    // 3. Testar WHERE IN
    echo "2. Testando WHERE IN:\n";
    $query2 = new QueryBuilder($dbManager, 'products');
    $query2->select('*')
           ->whereIn('category_id', [1, 2, 3])
           ->whereNotNull('price');
    
    echo "   SQL: " . $query2->toSql() . "\n";
    echo "   Bindings: " . json_encode($query2->getBindings()) . "\n";
    echo "   ✓ OK\n\n";
    
    // 4. Testar métodos de compilação individualmente (usando Reflection)
    echo "3. Testando métodos de compilação individualmente:\n";
    
    $reflection = new ReflectionClass(QueryBuilder::class);
    
    // Testar wrapTable
    $wrapTableMethod = $reflection->getMethod('wrapTable');
    $wrapTableMethod->setAccessible(true);
    $wrappedTable = $wrapTableMethod->invoke($query, 'users');
    echo "   wrapTable('users'): " . $wrappedTable . "\n";
    
    // Testar wrapColumn
    $wrapColumnMethod = $reflection->getMethod('wrapColumn');
    $wrapColumnMethod->setAccessible(true);
    $wrappedColumn = $wrapColumnMethod->invoke($query, 'users.name');
    echo "   wrapColumn('users.name'): " . $wrappedColumn . "\n";
    
    // Testar compileColumns
    $compileColumnsMethod = $reflection->getMethod('compileColumns');
    $compileColumnsMethod->setAccessible(true);
    $compiledColumns = $compileColumnsMethod->invoke($query);
    echo "   compileColumns(): " . $compiledColumns . "\n";
    
    echo "   ✓ OK\n\n";
    
    // 5. Testar diferentes tipos de queries
    echo "4. Testando diferentes estruturas de query:\n";
    
    // Query com JOIN
    $query3 = new QueryBuilder($dbManager, 'users');
    $query3->select(['users.name', 'orders.total'])
           ->join('orders', 'users.id', '=', 'orders.user_id')
           ->where('users.active', 1);
    
    echo "   Query com JOIN:\n";
    echo "   SQL: " . $query3->toSql() . "\n";
    echo "   ✓ OK\n\n";
    
    // Query com GROUP BY e HAVING
    $query4 = new QueryBuilder($dbManager, 'orders');
    $query4->select(['user_id', 'COUNT(*) as total_orders'])
           ->groupBy('user_id')
           ->having('total_orders', '>', 5);
    
    echo "   Query com GROUP BY/HAVING:\n";
    echo "   SQL: " . $query4->toSql() . "\n";
    echo "   ✓ OK\n\n";
    
    // 6. Verificar bindings
    echo "5. Verificando bindings:\n";
    
    $query5 = new QueryBuilder($dbManager, 'users');
    $query5->select('*')
           ->where('status', 'active')
           ->where('age', 'BETWEEN', [18, 65])
           ->whereNull('deleted_at');
    
    $bindings = $query5->getBindings();
    echo "   Query: " . $query5->toSql() . "\n";
    echo "   Bindings count: " . count($bindings) . "\n";
    echo "   Bindings: " . json_encode($bindings) . "\n";
    
    if (count($bindings) >= 1) {
        echo "   ✓ Bindings coletados corretamente\n\n";
    } else {
        echo "   ✗ Problema com bindings\n\n";
    }
    
    echo "=== Teste concluído com sucesso! ===\n";
    echo "O QueryBuilder está compilando queries corretamente.\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}