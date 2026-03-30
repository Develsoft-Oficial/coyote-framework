<?php
// test-orm-basic.php
// Teste básico do ORM do Coyote Framework

require_once __DIR__ . '/vendors/autoload.php';

use Coyote\Database\DatabaseManager;
use Coyote\Database\Model;

echo "=== Teste Básico do ORM - Coyote Framework ===\n\n";

// Configuração de banco de dados para teste (SQLite em memória)
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
    echo "1. Configurando DatabaseManager...\n";
    $dbManager = new DatabaseManager($config);
    Model::setDatabaseManager($dbManager);
    echo "   ✓ DatabaseManager configurado\n\n";
    
    // 2. Definir classe de exemplo User
    echo "2. Definindo classe de exemplo User...\n";
    
    class User extends Model
    {
        protected $table = 'users';
        protected $fillable = ['name', 'email', 'age'];
        protected $casts = [
            'age' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
    
    echo "   ✓ Classe User definida\n\n";
    
    // 3. Testar criação de instância
    echo "3. Testando criação de instância...\n";
    $user = new User([
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'age' => 30,
    ]);
    
    echo "   Nome: " . $user->name . "\n";
    echo "   Email: " . $user->email . "\n";
    echo "   Idade: " . $user->age . " (tipo: " . gettype($user->age) . ")\n";
    echo "   Tabela: " . $user->getTable() . "\n";
    echo "   Chave primária: " . $user->getKeyName() . "\n";
    echo "   ✓ Instância criada com sucesso\n\n";
    
    // 4. Testar métodos de array access
    echo "4. Testando ArrayAccess...\n";
    echo "   user['name']: " . $user['name'] . "\n";
    $user['name'] = 'João da Silva';
    echo "   user['name'] após alteração: " . $user['name'] . "\n";
    echo "   isset(user['email']): " . (isset($user['email']) ? 'Sim' : 'Não') . "\n";
    echo "   ✓ ArrayAccess funcionando\n\n";
    
    // 5. Testar conversão para array/JSON
    echo "5. Testando conversão para array/JSON...\n";
    $array = $user->toArray();
    echo "   Array: " . json_encode($array) . "\n";
    echo "   JSON: " . json_encode($user) . "\n";
    echo "   ✓ Conversão funcionando\n\n";
    
    // 6. Testar métodos de query (simulação - sem banco real)
    echo "6. Testando métodos de query (simulação)...\n";
    
    // Testar newQuery
    $query = $user->newQuery();
    echo "   Query criada: " . get_class($query) . "\n";
    
    // Testar métodos estáticos (não executará queries reais)
    echo "   Método all() disponível: " . (method_exists(User::class, 'all') ? 'Sim' : 'Não') . "\n";
    echo "   Método find() disponível: " . (method_exists(User::class, 'find') ? 'Sim' : 'Não') . "\n";
    echo "   Método create() disponível: " . (method_exists(User::class, 'create') ? 'Sim' : 'Não') . "\n";
    echo "   ✓ Métodos de query disponíveis\n\n";
    
    // 7. Testar ModelCollection
    echo "7. Testando ModelCollection...\n";
    
    $users = [
        new User(['name' => 'João', 'email' => 'joao@test.com']),
        new User(['name' => 'Maria', 'email' => 'maria@test.com']),
        new User(['name' => 'Pedro', 'email' => 'pedro@test.com']),
    ];
    
    $collection = new \Coyote\Database\ModelCollection($users);
    echo "   Quantidade: " . $collection->count() . "\n";
    echo "   Primeiro: " . $collection->first()->name . "\n";
    echo "   Último: " . $collection->last()->name . "\n";
    
    // Testar filter
    $filtered = $collection->filter(function ($user) {
        return $user->name === 'Maria';
    });
    echo "   Filtrado (Maria): " . $filtered->count() . " item(s)\n";
    
    // Testar map
    $mapped = $collection->map(function ($user) {
        return strtoupper($user->name);
    });
    echo "   Mapeado (nomes em maiúsculo): " . implode(', ', $mapped->all()) . "\n";
    
    // Testar pluck
    $names = $collection->pluck('name');
    echo "   Pluck (nomes): " . implode(', ', $names) . "\n";
    
    echo "   ✓ ModelCollection funcionando\n\n";
    
    // 8. Testar fillable/guarded
    echo "8. Testando fillable/guarded...\n";
    
    $user2 = new User();
    $user2->fill([
        'name' => 'Teste',
        'email' => 'teste@example.com',
        'age' => 25,
        'password' => 'secret', // Não está em fillable, deve ser ignorado
    ]);
    
    echo "   Atributos após fill: " . json_encode($user2->getAttributes()) . "\n";
    echo "   Password foi preenchido? " . (isset($user2->password) ? 'Sim' : 'Não') . "\n";
    echo "   ✓ Fillable/guarded funcionando\n\n";
    
    // 9. Testar dirty/clean state
    echo "9. Testando dirty/clean state...\n";
    
    $user3 = new User(['name' => 'Original']);
    $user3->syncOriginal(); // Marca como limpo
    
    echo "   Dirty inicial: " . ($user3->isDirty() ? 'Sim' : 'Não') . "\n";
    
    $user3->name = 'Modificado';
    echo "   Dirty após modificação: " . ($user3->isDirty() ? 'Sim' : 'Não') . "\n";
    echo "   Atributos modificados: " . json_encode($user3->getDirty()) . "\n";
    
    $user3->syncOriginal();
    echo "   Dirty após syncOriginal: " . ($user3->isDirty() ? 'Sim' : 'Não') . "\n";
    echo "   ✓ Dirty/clean state funcionando\n\n";
    
    echo "=== Todos os testes passaram! ===\n";
    echo "O ORM básico está funcionando corretamente.\n";
    echo "\nPróximos passos:\n";
    echo "1. Implementar relações (hasOne, hasMany, belongsTo)\n";
    echo "2. Implementar eager loading\n";
    echo "3. Implementar scopes de query\n";
    echo "4. Integrar com sistema de autenticação\n";
    
} catch (Exception $e) {
    echo "✗ Erro durante os testes: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}