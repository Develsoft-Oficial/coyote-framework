# Lista de Tarefas - Fase 3: Implementação

## Prioridade 1: Completar QueryBuilder

### 1.1 Completar método `compileSelect()`
- [ ] Analisar estrutura atual do método (linha 665: `$sql`)
- [ ] Implementar construção da cláusula SELECT com colunas
- [ ] Implementar construção da cláusula FROM com tabela
- [ ] Implementar construção da cláusula WHERE
- [ ] Implementar construção de JOINs
- [ ] Implementar construção de GROUP BY e HAVING
- [ ] Implementar construção de ORDER BY
- [ ] Implementar construção de LIMIT e OFFSET
- [ ] Testar com diferentes cenários de query

### 1.2 Implementar métodos de compilação faltantes
- [ ] Implementar `compileInsert()` para queries INSERT
- [ ] Implementar `compileUpdate()` para queries UPDATE  
- [ ] Implementar `compileDelete()` para queries DELETE
- [ ] Criar método `compileWheres()` para reutilização
- [ ] Criar método `compileJoins()` para reutilização
- [ ] Criar método `compileGroups()` para GROUP BY
- [ ] Criar método `compileOrders()` para ORDER BY
- [ ] Criar método `compileLimitOffset()` para LIMIT/OFFSET

### 1.3 Testes do QueryBuilder
- [ ] Criar testes unitários para `compileSelect()`
- [ ] Criar testes unitários para `compileInsert()`
- [ ] Criar testes unitários para `compileUpdate()`
- [ ] Criar testes unitários para `compileDelete()`
- [ ] Testar queries complexas com múltiplas condições
- [ ] Testar queries com JOINs
- [ ] Testar queries com subqueries
- [ ] Testar bindings de parâmetros para segurança

## Prioridade 2: Implementar ORM Básico

### 2.1 Criar estrutura de diretórios
- [ ] Criar `vendors/coyote/Database/Model.php`
- [ ] Criar `vendors/coyote/Database/ModelCollection.php`
- [ ] Criar diretório `vendors/coyote/Database/Relations/`
- [ ] Criar diretório `vendors/coyote/Database/Query/`

### 2.2 Implementar classe base Model
- [ ] Definir propriedades protegidas ($table, $primaryKey, $fillable, etc.)
- [ ] Implementar método `__construct()`
- [ ] Implementar método estático `all()`
- [ ] Implementar método estático `find($id)`
- [ ] Implementar método `save()` para criação/atualização
- [ ] Implementar método `create(array $attributes)`
- [ ] Implementar método `update(array $attributes)`
- [ ] Implementar método `delete()`
- [ ] Implementar método `refresh()`

### 2.3 Implementar relações básicas
- [ ] Criar interface/base `Relation.php`
- [ ] Implementar `HasOne.php`
- [ ] Implementar `HasMany.php`
- [ ] Implementar `BelongsTo.php`
- [ ] Implementar métodos no Model para definir relações
- [ ] Implementar eager loading básico

### 2.4 Implementar ModelQueryBuilder
- [ ] Criar `ModelQueryBuilder.php` que estende `QueryBuilder`
- [ ] Implementar scopes de query
- [ ] Implementar eager loading
- [ ] Implementar lazy loading

### 2.5 Testes do ORM
- [ ] Criar modelo de exemplo `User` para testes
- [ ] Testar operações CRUD básicas
- [ ] Testar relações entre modelos
- [ ] Testar eager loading
- [ ] Testar scopes de query

## Prioridade 3: Sistema de Autenticação

### 3.1 Criar estrutura de diretórios
- [ ] Criar `vendors/coyote/Auth/AuthManager.php`
- [ ] Criar diretório `vendors/coyote/Auth/Guards/`
- [ ] Criar diretório `vendors/coyote/Auth/Providers/`
- [ ] Criar diretório `vendors/coyote/Auth/Middleware/`
- [ ] Criar diretório `vendors/coyote/Auth/Contracts/`

### 3.2 Implementar contratos (interfaces)
- [ ] Criar `Authenticatable.php` interface
- [ ] Criar `UserProvider.php` interface
- [ ] Criar `Guard.php` interface

### 3.3 Implementar AuthManager
- [ ] Implementar gerenciamento de múltiplos guards
- [ ] Implementar resolução de providers
- [ ] Implementar configuração via config/auth.php
- [ ] Implementar métodos `attempt()`, `login()`, `logout()`, `user()`

### 3.4 Implementar SessionGuard
- [ ] Implementar autenticação por sessão
- [ ] Implementar "remember me" functionality
- [ ] Implementar validação de credenciais
- [ ] Implementar regeneração de sessão

### 3.5 Implementar DatabaseUserProvider
- [ ] Implementar busca de usuário por credenciais
- [ ] Implementar busca de usuário por ID
- [ ] Implementar busca de usuário por token
- [ ] Implementar validação de senha (hash)

### 3.6 Implementar middleware de autenticação
- [ ] Criar `Authenticate.php` middleware
- [ ] Criar `RedirectIfAuthenticated.php` middleware
- [ ] Integrar middleware com router
- [ ] Implementar redirecionamento para login

### 3.7 Criar User Model padrão
- [ ] Criar `app/Models/User.php`
- [ ] Implementar interface `Authenticatable`
- [ ] Definir tabela `users` e campos
- [ ] Implementar métodos de autenticação

### 3.8 Configuração de autenticação
- [ ] Criar `config/auth.php`
- [ ] Definir guards padrão (web, api)
- [ ] Definir providers (database)
- [ ] Definir configuração de passwords

### 3.9 Testes de autenticação
- [ ] Testar login com credenciais válidas
- [ ] Testar login com credenciais inválidas
- [ ] Testar logout
- [ ] Testar middleware de autenticação
- [ ] Testar "remember me" functionality

## Prioridade 4: Sistema de Validação

### 4.1 Criar estrutura de validação
- [ ] Criar `vendors/coyote/Validation/Validator.php`
- [ ] Criar `vendors/coyote/Validation/Rule.php`
- [ ] Criar diretório `vendors/coyote/Validation/Rules/`
- [ ] Criar `vendors/coyote/Validation/ValidationException.php`

### 4.2 Implementar Validator
- [ ] Implementar parsing de regras de validação
- [ ] Implementar validação de dados
- [ ] Implementar coleta de erros
- [ ] Implementar mensagens de erro personalizáveis

### 4.3 Implementar regras built-in
- [ ] Implementar `Required.php`
- [ ] Implementar `Email.php`
- [ ] Implementar `Min.php` e `Max.php`
- [ ] Implementar `String.php` e `Numeric.php`
- [ ] Implementar `Unique.php` (integração com banco)
- [ ] Implementar `Confirmed.php`
- [ ] Implementar `Date.php` e `DateFormat.php`

### 4.4 Testes de validação
- [ ] Testar validação com regras simples
- [ ] Testar validação com múltiplas regras
- [ ] Testar mensagens de erro
- [ ] Testar regra `unique` com banco de dados

## Prioridade 5: Sistema de Cache

### 5.1 Criar estrutura de cache
- [ ] Criar `vendors/coyote/Cache/CacheManager.php`
- [ ] Criar `vendors/coyote/Cache/Repository.php`
- [ ] Criar diretório `vendors/coyote/Cache/Stores/`
- [ ] Criar interface `Store.php`

### 5.2 Implementar stores
- [ ] Implementar `FileStore.php`
- [ ] Implementar `DatabaseStore.php`
- [ ] Implementar `ArrayStore.php` (para testes)
- [ ] Implementar `RedisStore.php` (opcional)

### 5.3 Implementar CacheManager
- [ ] Implementar gerenciamento de múltiplos stores
- [ ] Implementar métodos `get()`, `put()`, `forget()`, `flush()`
- [ ] Implementar tags de cache
- [ ] Implementar expiração automática

### 5.4 Testes de cache
- [ ] Testar operações básicas de cache
- [ ] Testar expiração de cache
- [ ] Testar diferentes stores
- [ ] Testar performance

## Prioridade 6: CLI Commands

### 6.1 Implementar console application
- [ ] Integrar com Symfony Console
- [ ] Criar `vendors/coyote/Cli/Kernel.php`
- [ ] Criar sistema de registro de comandos
- [ ] Implementar input/output handling

### 6.2 Implementar comandos essenciais
- [ ] Implementar `make:model` Command
- [ ] Implementar `make:migration` Command
- [ ] Implementar `migrate` Command
- [ ] Implementar `make:controller` Command
- [ ] Implementar `make:middleware` Command
- [ ] Implementar `make:auth` Command (scaffolding)

### 6.3 Testes de CLI
- [ ] Testar criação de modelo via CLI
- [ ] Testar criação de migration via CLI
- [ ] Testar execução de migrations
- [ ] Testar scaffolding de autenticação

## Prioridade 7: Integração e Testes Finais

### 7.1 Integração entre componentes
- [ ] Integrar ORM com QueryBuilder
- [ ] Integrar autenticação com ORM (User model)
- [ ] Integrar validação com formulários de autenticação
- [ ] Integrar cache com ORM (query caching)

### 7.2 Testes de integração
- [ ] Testar fluxo completo de registro de usuário
- [ ] Testar fluxo completo de login/logout
- [ ] Testar operações CRUD com ORM
- [ ] Testar validação em formulários

### 7.3 Exemplos práticos
- [ ] Criar exemplo completo de aplicação com autenticação
- [ ] Criar exemplo de API REST com autenticação JWT
- [ ] Criar exemplo de uso do ORM com relações
- [ ] Atualizar `example-app.php` com novos recursos

### 7.4 Documentação
- [ ] Documentar API do QueryBuilder
- [ ] Documentar uso do ORM
- [ ] Documentar sistema de autenticação
- [ ] Documentar sistema de validação
- [ ] Documentar comandos CLI
- [ ] Atualizar README.md

## Ordem de Implementação Recomendada

1. **Semana 1**: Completar QueryBuilder + Testes
2. **Semana 2**: Implementar ORM básico + Testes
3. **Semana 3**: Sistema de autenticação + Testes
4. **Semana 4**: Validação + Cache + Testes
5. **Semana 5**: CLI Commands + Integração final
6. **Semana 6**: Testes finais + Documentação

## Critérios de Aceitação

- [ ] QueryBuilder suporta todos os tipos de queries (SELECT, INSERT, UPDATE, DELETE)
- [ ] ORM suporta operações CRUD básicas e relações simples
- [ ] Sistema de autenticação funciona com sessões
- [ ] Validação suporta regras comuns e mensagens personalizadas
- [ ] Cache funciona com pelo menos FileStore
- [ ] Comandos CLI essenciais funcionam
- [ ] Testes unitários cobrem funcionalidades principais
- [ ] Exemplos práticos funcionam corretamente
- [ ] Documentação está completa e atualizada

## Notas Importantes

- Começar sempre com testes antes da implementação
- Manter compatibilidade com código existente
- Seguir padrões PSR-4 para autoloading
- Manter código em português (comentários e documentação)
- Focar em simplicidade e performance
- Priorizar segurança (SQL injection, XSS, CSRF)