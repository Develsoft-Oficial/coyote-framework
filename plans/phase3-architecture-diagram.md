# Diagrama de Arquitetura - Fase 3

## Visão Geral do Sistema

```mermaid
graph TB
    subgraph "Camada de Aplicação"
        App[Application]
        Router[Router]
        Controllers[Controllers]
        Middleware[Middleware]
    end
    
    subgraph "Camada de Serviços"
        Auth[Auth Manager]
        Validator[Validator]
        Cache[Cache Manager]
        CLI[CLI Commands]
    end
    
    subgraph "Camada de Dados"
        ORM[ORM / Model]
        QueryBuilder[Query Builder]
        DBManager[Database Manager]
        Connection[Connection / PDO]
    end
    
    subgraph "Camada de Armazenamento"
        Database[(Database)]
        Session[Session Storage]
        CacheStore[Cache Stores]
    end
    
    App --> Router
    Router --> Controllers
    Controllers --> Middleware
    Middleware --> Auth
    Middleware --> Validator
    
    Controllers --> ORM
    ORM --> QueryBuilder
    QueryBuilder --> DBManager
    DBManager --> Connection
    Connection --> Database
    
    Auth --> Session
    Cache --> CacheStore
    CLI --> ORM
    CLI --> DBManager
    
    Validator --> ORM
```

## Fluxo de Autenticação

```mermaid
sequenceDiagram
    participant User as Usuário
    participant App as Aplicação
    participant Auth as AuthManager
    participant Guard as SessionGuard
    participant Provider as UserProvider
    participant DB as Banco de Dados
    participant Session as Sessão
    
    User->>App: Acessa /login
    App->>Auth: attempt(credentials)
    Auth->>Guard: authenticate(credentials)
    Guard->>Provider: retrieveByCredentials(credentials)
    Provider->>DB: SELECT user WHERE email=?
    DB-->>Provider: User data
    Provider-->>Guard: User object
    Guard->>Guard: verifyPassword(password, hash)
    Guard->>Session: storeUserInSession(user)
    Session-->>Guard: Session ID
    Guard-->>Auth: Authentication successful
    Auth-->>App: User authenticated
    App-->>User: Redirect to dashboard
```

## Fluxo de Query ORM

```mermaid
sequenceDiagram
    participant Controller
    participant Model as User Model
    participant Query as ModelQueryBuilder
    participant Builder as QueryBuilder
    participant DBMan as DatabaseManager
    participant Conn as Connection
    participant DB as Database
    
    Controller->>Model: User::where('active', 1)
    Model->>Query: newQuery()
    Query->>Builder: where('active', 1)
    Builder->>Builder: compileSelect()
    Builder->>DBMan: execute(query, bindings)
    DBMan->>Conn: getConnection()
    Conn->>DB: PDO query with bindings
    DB-->>Conn: Result set
    Conn-->>DBMan: Formatted results
    DBMan-->>Builder: Array of results
    Builder-->>Query: Collection of data
    Query->>Model: hydrate(results)
    Model-->>Controller: Collection of User models
```

## Estrutura de Diretórios da Fase 3

```
coyote/
├── vendors/coyote/
│   ├── Database/
│   │   ├── Model.php
│   │   ├── ModelCollection.php
│   │   ├── ModelQueryBuilder.php
│   │   ├── Relations/
│   │   │   ├── Relation.php
│   │   │   ├── HasOne.php
│   │   │   ├── HasMany.php
│   │   │   ├── BelongsTo.php
│   │   │   └── BelongsToMany.php
│   │   ├── QueryBuilder.php (completo)
│   │   ├── DatabaseManager.php
│   │   └── Connection.php
│   │
│   ├── Auth/
│   │   ├── AuthManager.php
│   │   ├── Contracts/
│   │   │   ├── Authenticatable.php
│   │   │   └── UserProvider.php
│   │   ├── Guards/
│   │   │   ├── Guard.php
│   │   │   ├── SessionGuard.php
│   │   │   └── TokenGuard.php
│   │   ├── Providers/
│   │   │   ├── UserProvider.php
│   │   │   └── DatabaseUserProvider.php
│   │   └── Middleware/
│   │       ├── Authenticate.php
│   │       ├── RedirectIfAuthenticated.php
│   │       └── Authorize.php
│   │
│   ├── Validation/
│   │   ├── Validator.php
│   │   ├── Rule.php
│   │   ├── ValidationException.php
│   │   └── Rules/
│   │       ├── Required.php
│   │       ├── Email.php
│   │       ├── Min.php
│   │       ├── Max.php
│   │       ├── Unique.php
│   │       └── ...
│   │
│   ├── Cache/
│   │   ├── CacheManager.php
│   │   ├── Repository.php
│   │   └── Stores/
│   │       ├── Store.php
│   │       ├── FileStore.php
│   │       ├── DatabaseStore.php
│   │       ├── ArrayStore.php
│   │       └── RedisStore.php
│   │
│   └── Cli/
│       ├── Kernel.php
│       ├── Commands/
│       │   ├── MakeModelCommand.php
│       │   ├── MakeMigrationCommand.php
│       │   ├── MigrateCommand.php
│       │   ├── MakeControllerCommand.php
│       │   └── MakeAuthCommand.php
│       └── Command.php
│
├── app/
│   ├── Models/
│   │   └── User.php
│   ├── Controllers/
│   │   └── AuthController.php
│   └── Middleware/
│
├── config/
│   ├── auth.php
│   ├── database.php
│   └── cache.php
│
├── database/
│   ├── migrations/
│   └── seeds/
│
└── tests/
    ├── Database/
    ├── Auth/
    ├── Validation/
    └── Cache/
```

## Dependências entre Componentes

```mermaid
graph LR
    QueryBuilder --> DatabaseManager
    DatabaseManager --> Connection
    Connection --> PDO
    
    Model --> QueryBuilder
    Model --> ModelQueryBuilder
    ModelQueryBuilder --> QueryBuilder
    
    AuthManager --> Guards
    AuthManager --> Providers
    SessionGuard --> UserProvider
    DatabaseUserProvider --> Model
    
    Validator --> Rules
    UniqueRule --> DatabaseManager
    
    CacheManager --> Stores
    FileStore --> Filesystem
    
    CLI --> Commands
    MakeModelCommand --> Filesystem
    MakeMigrationCommand --> DatabaseManager
```

## Sequência de Implementação Recomendada

1. **QueryBuilder** → **DatabaseManager** → **Connection** (Base de dados)
2. **Model** → **ModelQueryBuilder** → **Relations** (ORM básico)
3. **Auth Contracts** → **UserProvider** → **SessionGuard** → **AuthManager** (Autenticação)
4. **Validator** → **Rules** (Validação)
5. **Cache Stores** → **CacheManager** (Cache)
6. **CLI Commands** → **CLI Kernel** (Interface de linha de comando)
7. **Integração** → **Testes** → **Documentação**

## Considerações de Performance

1. **QueryBuilder**: Usar prepared statements para segurança
2. **ORM**: Implementar eager loading para evitar N+1 queries
3. **Cache**: Cache de queries frequentes
4. **Session**: Session driver otimizado
5. **Validation**: Validação early exit para melhor performance

## Considerações de Segurança

1. **SQL Injection**: Usar bindings em todas as queries
2. **XSS**: Escape automático de output nas views
3. **CSRF**: Tokens em formulários
4. **Authentication**: Hash de senhas (bcrypt/argon2)
5. **Session**: Regeneração de session ID após login
6. **Validation**: Validação de input em todos os endpoints

## Próximos Passos Imediatos

1. Completar método `compileSelect()` no QueryBuilder
2. Implementar métodos `compileInsert()`, `compileUpdate()`, `compileDelete()`
3. Criar classe base `Model` com operações CRUD
4. Implementar `AuthManager` com suporte a sessões
5. Criar comandos CLI básicos (`make:model`, `make:migration`)

Este diagrama fornece uma visão clara da arquitetura da Fase 3 e serve como guia para a implementação.