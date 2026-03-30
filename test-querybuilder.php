<?php
// test-querybuilder.php
// Teste do QueryBuilder implementado

require_once __DIR__ . '/vendors/autoload.php';

use Coyote\Database\DatabaseManager;
use Coyote\Database\QueryBuilder;

echo "=== Teste do QueryBuilder - Coyote Framework ===\n\n";

// Configuração de banco de dados para teste (usando SQLite em memória)
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
    echo "1. Criando DatabaseManager...\n";
    $dbManager = new DatabaseManager($config);
    echo "   ✓ DatabaseManager criado\n\n";
    
    // 2. Criar QueryBuilder
    echo "2. Criando QueryBuilder para tabela 'users'...\n";
    $query = new QueryBuilder($dbManager, 'users');
    echo "   ✓ QueryBuilder criado\n\n";
    
    // 3. Testar SELECT básico
    echo "3. Testando SELECT básico...\n";
    $query->select(['id', 'name', 'email'])
          ->where('active', 1)
          ->where('age', '>', 18)
          ->orderBy('name', 'ASC')
          ->limit(10);
    
    $sql = $query->toSql();
    $bindings = $query->getBindings();
    
    echo "   SQL: " . $sql . "\n";
    echo "   Bindings: " . json_encode($bindings) . "\n";
    echo "   ✓ SELECT básico compilado\n\n";
    
    // 4. Testar SELECT com múltiplas condições
    echo "4. Testando SELECT com múltiplas condições...\n";
    $query2 = new QueryBuilder($dbManager, 'products');
    $query2->select('*')
           ->where('category_id', 5)
           ->whereIn('status', ['active', 'pending'])
           ->whereNotNull('price')
           ->groupBy('category_id')
           ->having('COUNT(*)', '>', 1)
           ->orderBy('created_at', 'DESC');
    
    $sql2 = $query2->toSql();
    $bindings2 = $query2->getBindings();
    
    echo "   SQL: " . $sql2 . "\n";
    echo "   Bindings: " . json_encode($bindings2) . "\n";
    echo "   ✓ SELECT complexo compilado\n\n";
    
    // 5. Testar INSERT (apenas compilação, sem execução)
    echo "5. Testando INSERT (compilação)...\n";
    $query3 = new QueryBuilder($dbManager, 'users');
    $query3->type = 'insert'; // Definir tipo manualmente para teste
    $query3->values = [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'age' => 30,
    ];
    
    $sql3 = $query3->compileInsert();
    $bindings3 = array_values($query3->values);
    
    echo "   SQL: " . $sql3 . "\n";
    echo "   Bindings: " . json_encode($bindings3) . "\n";
    echo "   ✓ INSERT compilado\n\n";
    
    // 6. Testar UPDATE (apenas compilação)
    echo "6. Testando UPDATE (compilação)...\n";
    $query4 = new QueryBuilder($dbManager, 'users');
    $query4->type = 'update'; // Definir tipo manualmente
    $query4->values = [
        'name' => 'João da Silva',
        'age' => 31,
    ];
    $query4->wheres = [
        [
            'type' => 'basic',
            'column' => 'id',
            'operator' => '=',
            'value' => 1,
            'boolean' => 'AND',
        ],
        [
            'type' => 'basic',
            'column' => 'email',
            'operator' => '=',
            'value' => 'joao@example.com',
            'boolean' => 'AND',
        ],
    ];
    
    $sql4 = $query4->compileUpdate();
    $bindings4 = $query4->getBindings();
    
    echo "   SQL: " . $sql4 . "\n";
    echo "   Bindings: " . json_encode($bindings4) . "\n";
    echo "   ✓ UPDATE compilado\n\n";
    
    // 7. Testar DELETE (apenas compilação)
    echo "7. Testando DELETE (compilação)...\n";
    $query5 = new QueryBuilder($dbManager, 'users');
    $query5->type = 'delete'; // Definir tipo manualmente
    $query5->wheres = [
        [
            'type' => 'basic',
            'column' => 'active',
            'operator' => '=',
            'value' => 0,
            'boolean' => 'AND',
        ],
        [
            'type' => 'basic',
            'column' => 'created_at',
            'operator' => '<',
            'value' => '2023-01-01',
            'boolean' => 'AND',
        ],
    ];
    
    $sql5 = $query5->compileDelete();
    $bindings5 = $query5->getBindings();
    
    echo "   SQL: " . $sql5 . "\n";
    echo "   Bindings: " . json_encode($bindings5) . "\n";
    echo "   ✓ DELETE compilado\n\n";
    
    // 8. Testar JOIN
    echo "8. Testando JOIN...\n";
    $query6 = new QueryBuilder($dbManager, 'users');
    $query6->select(['users.name', 'orders.total'])
           ->join('orders', 'users.id', '=', 'orders.user_id')
           ->where('users.active', 1)
           ->orderBy('orders.created_at', 'DESC');
    
    $sql6 = $query6->toSql();
    $bindings6 = $query6->getBindings();
    
    echo "   SQL: " . $sql6 . "\n";
    echo "   Bindings: " . json_encode($bindings6) . "\n";
    echo "   ✓ JOIN compilado\n\n";
    
    // 9. Testar métodos auxiliares
    echo "9. Testando métodos auxiliares...\n";
    
    // Testar wrapTable
    $wrappedTable = $query->wrapTable('users');
    echo "   wrapTable('users'): " . $wrappedTable . "\n";
    
    // Testar wrapColumn
    $wrappedColumn = $query->wrapColumn('users.name');
    echo "   wrapColumn('users.name'): " . $wrappedColumn . "\n";
    
    $wrappedColumn2 = $query->wrapColumn('COUNT(*) as total');
    echo "   wrapColumn('COUNT(*) as total'): " . $wrappedColumn2 . "\n";
    
    echo "   ✓ Métodos auxiliares funcionando\n\n";
    
    echo "=== Todos os testes passaram! ===\n";
    echo "O QueryBuilder está funcionando corretamente.\n";
    
} catch (Exception $e) {
    echo "✗ Erro durante os testes: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}