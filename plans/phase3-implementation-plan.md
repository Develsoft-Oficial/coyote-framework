# Plano de Implementação - Fase 3: Sistema de Banco de Dados & Autenticação

## Estado Atual
- **QueryBuilder**: 665 linhas, método `compileSelect()` incompleto (linha 665: `$sql`)
- **DatabaseManager**: Implementado (367 linhas)
- **Connection**: Implementado (426 linhas) 
- **DatabaseException**: Implementado
- **ORM**: Não implementado
- **Autenticação**: Não implementada
- **Validação**: Não implementada
- **Cache**: Não implementado

## 1. Completar QueryBuilder

### 1.1 Métodos de Compilação Faltantes
```php
protected function compileSelect(): string
{
    // Implementar construção da query SELECT
    // Incluir: SELECT columns FROM table WHERE conditions GROUP BY HAVING ORDER BY LIMIT OFFSET
}

protected function compileInsert(): string
{
    // Implementar construção da query INSERT
}

protected function compileUpdate(): string
{
    // Implementar construção da query UPDATE
}

protected function compileDelete(): string
{
    // Implementar construção da query DELETE
}
```

### 1.2 Métodos Auxiliares Necessários
- `compileWheres()` - Compilar condições WHERE
- `compileJoins()` - Compilar JOINs
- `compileGroups()` - Compilar GROUP BY
- `compileOrders()` - Compilar ORDER BY
- `compileLimitOffset()` - Compilar LIMIT e OFFSET

## 2. Implementar ORM (Object-Relational Mapping)

### 2.1 Estrutura de Diretórios
```
vendors/coyote/Database/
├── Model.php (Classe base do modelo)
├── ModelCollection.php (Coleção de modelos)
├── Relations/
│   ├── Relation.php (Classe base de relação)
│   ├── HasOne.php
│   ├── HasMany.php
│   ├── BelongsTo.php
│   └── BelongsToMany.php
└── Query/
    ├── ModelQueryBuilder.php (QueryBuilder especializado para modelos)
    └── ModelScope.php (Scopes de query)
```

### 2.2 Model Base Class
```php
class Model
{
    // Propriedades
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];
    protected $timestamps = true;
    
    // Métodos principais
    public static function all();
    public static function find($id);
    public static function create(array $attributes);
    public function save();
    public function update(array $attributes);
    public function delete();
    public function refresh();
    
    // Relações
    public function hasOne($related, $foreignKey = null, $localKey = null);
    public function hasMany($related, $foreignKey = null, $localKey = null);
    public function belongsTo($related, $foreignKey = null, $ownerKey = null);
    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null);
    
    // Events
    protected static function boot();
    public static function creating($callback);
    public static function created($callback);
    public static function updating($callback);
    public static function updated($callback);
    public static function deleting($callback);
    public static function deleted($callback);
}
```

## 3. Sistema de Autenticação

### 3.1 Estrutura de Diretórios
```
vendors/coyote/Auth/
├── AuthManager.php (Gerenciador principal)
├── Guards/
│   ├── Guard.php (Interface)
│   ├── SessionGuard.php (Autenticação por sessão)
│   └── TokenGuard.php (Autenticação por token)
├── Providers/
│   ├── UserProvider.php (Interface)
│   └── DatabaseUserProvider.php (Provider para banco de dados)
├── Middleware/
│   ├── Authenticate.php
│   ├── RedirectIfAuthenticated.php
│   └── Authorize.php
└── Contracts/
    ├── Authenticatable.php
    └── UserProvider.php
```

### 3.2 User Model Padrão
```php
namespace App\Models;

use Coyote\Database\Model;
use Coyote\Auth\Contracts\Authenticatable;

class User extends Model implements Authenticatable
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    
    // Métodos do contrato Authenticatable
    public function getAuthIdentifier();
    public function getAuthPassword();
    public function getRememberToken();
    public function setRememberToken($value);
    public function getRememberTokenName();
}
```

### 3.3 Configuração de Autenticação
```php
// config/auth.php
return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],
    
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
        ],
    ],
    
    'providers' => [
        'users' => [
            'driver' => 'database',
            'model' => App\Models\User::class,
        ],
    ],
    
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],
];
```

## 4. Sistema de Validação

### 4.1 Estrutura
```
vendors/coyote/Validation/
├── Validator.php
├── Rule.php (Classe base de regras)
├── Rules/ (Regras built-in)
│   ├── Required.php
│   ├── Email.php
│   ├── Min.php
│   ├── Max.php
│   ├── Unique.php
│   └── ...
└── ValidationException.php
```

### 4.2 Exemplo de Uso
```php
$validator = new Validator($data, [
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|min:8|confirmed',
]);

if ($validator->fails()) {
    return $validator->errors();
}
```

## 5. Sistema de Cache

### 5.1 Estrutura
```
vendors/coyote/Cache/
├── CacheManager.php
├── Stores/
│   ├── Store.php (Interface)
│   ├── FileStore.php
│   ├── DatabaseStore.php
│   ├── RedisStore.php
│   └── ArrayStore.php
└── Repository.php
```

## 6. CLI Commands

### 6.1 Comandos Prioritários
1. `php coyote make:model User` - Criar modelo
2. `php coyote make:migration create_users_table` - Criar migration
3. `php coyote migrate` - Executar migrations
4. `php coyote make:auth` - Gerar scaffolding de autenticação
5. `php coyote make:controller AuthController` - Criar controller de autenticação

## 7. Cronograma de Implementação

### Fase 3.1: Completar QueryBuilder (1-2 dias)
1. Implementar `compileSelect()`
2. Implementar `compileInsert()`, `compileUpdate()`, `compileDelete()`
3. Implementar métodos auxiliares de compilação
4. Testar queries básicas

### Fase 3.2: Implementar ORM Básico (2-3 dias)
1. Criar classe base `Model`
2. Implementar operações CRUD básicas
3. Implementar relações simples (hasOne, hasMany, belongsTo)
4. Criar `ModelQueryBuilder`

### Fase 3.3: Sistema de Autenticação (2-3 dias)
1. Implementar `AuthManager`
2. Implementar `SessionGuard` e `DatabaseUserProvider`
3. Criar middleware de autenticação
4. Implementar sistema de senhas (hash, reset)

### Fase 3.4: Validação e Cache (1-2 dias)
1. Implementar `Validator` com regras básicas
2. Implementar `CacheManager` com FileStore
3. Integrar validação com formulários

### Fase 3.5: CLI Commands (1 dia)
1. Implementar console application
2. Criar comandos essenciais
3. Integrar com estrutura do projeto

## 8. Testes

### 8.1 Testes Unitários
- Testar QueryBuilder com diferentes tipos de queries
- Testar operações CRUD do Model
- Testar autenticação (login, logout, registro)
- Testar validação de dados

### 8.2 Testes de Integração
- Testar fluxo completo de autenticação
- Testar integração banco de dados + ORM
- Testar cache em diferentes cenários

## 9. Documentação

### 9.1 Documentação Técnica
- Documentar API do QueryBuilder
- Documentar uso do ORM
- Documentar sistema de autenticação
- Documentar validação

### 9.2 Exemplos Práticos
- Exemplo completo de aplicação com autenticação
- Exemplo de uso do ORM com relações
- Exemplo de validação de formulários
- Exemplo de uso de cache

## 10. Próximos Passos Imediatos

1. **Completar QueryBuilder** - Prioridade máxima
2. **Criar estrutura básica do ORM**
3. **Implementar autenticação por sessão**
4. **Criar comandos CLI essenciais**

Este plano fornece um roteiro claro para completar a Fase 3 do Coyote Framework, transformando-o em um framework completo com suporte a banco de dados, ORM, autenticação e validação.