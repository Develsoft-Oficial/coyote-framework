# Fase 3: Sistema de Banco de Dados (Database)

## Visão Geral
Esta fase implementará o sistema de banco de dados completo do Coyote Framework, incluindo conexões, query builder, ORM e migrations.

## Objetivos Principais
1. Sistema de banco de dados com suporte a múltiplos drivers
2. Query Builder fluente e seguro
3. ORM (Object-Relational Mapping) estilo Eloquent
4. Sistema de migrations para versionamento de banco
5. Schema builder para criação programática de tabelas

## 1. Sistema de Banco de Dados

### 1.1 Database Manager
- Suporte a múltiplos drivers: MySQL, PostgreSQL, SQLite, SQL Server
- Gerenciamento de conexões múltiplas
- Pool de conexões
- Configuração por ambiente

### 1.2 Query Builder
- Interface fluente para construção de queries
- Suporte a joins, subqueries, unions
- Paginação automática
- Escaping de parâmetros para segurança

### 1.3 Migrations
- Sistema de versionamento de banco de dados
- CLI para criação e execução de migrations
- Rollback de migrations
- Seeders para dados iniciais

## 2. ORM (Object-Relational Mapping)

### 2.1 Model Base Class
- Mapeamento de tabelas para classes
- Relações: hasOne, hasMany, belongsTo, belongsToMany
- Timestamps automáticos
- Soft deletes
- Scopes de query

### 2.2 Eager Loading
- Carregamento otimizado de relações
- N+1 query prevention
- Lazy loading opcional

### 2.3 Events & Observers
- Eventos de ciclo de vida do modelo
- Observers para lógica de negócio
- Hooks: creating, created, updating, updated, etc.

## 3. Sistema de Migrations

### 3.1 Migration Base Class
- Classe abstrata para todas as migrations
- Métodos `up()` e `down()` para aplicar/reverter
- Suporte a transações
- Timestamps automáticos

### 3.2 Migration Repository
- Armazenamento de metadados de migrations
- Controle de quais migrations foram aplicadas
- Suporte a múltiplos ambientes
- Rollback de migrations específicas

### 3.3 Migrator
- Execução de migrations em lote
- Rollback de migrations
- Status das migrations
- Resolução de dependências entre migrations

### 3.4 Schema Builder
- Criação programática de tabelas
- Modificação de tabelas existentes
- Suporte a índices e chaves estrangeiras
- Compatibilidade entre diferentes bancos de dados

## 4. Schema Builder

### 4.1 Blueprint Class
- Definição de estrutura de tabelas
- Métodos para adicionar colunas
- Suporte a modificação de tabelas
- Geração de SQL específico por driver

### 4.2 Column Types
- Tipos de dados suportados (integer, string, text, boolean, etc.)
- Modificadores de coluna (nullable, default, unique)
- Chaves primárias e estrangeiras
- Índices e constraints

### 4.3 Grammar Classes
- MySQLGrammar
- PostgreSQLGrammar
- SQLiteGrammar
- SQLServerGrammar

## 5. Seeders

### 5.1 Seeder Base Class
- Classe base para seeders
- Método `run()` para inserção de dados
- Suporte a dados de teste
- Integração com Faker para dados realistas

### 5.2 Database Seeding
- Execução de seeders
- Seeders condicionais por ambiente
- Limpeza de dados antes do seeding
- Seeders em ordem específica

## 6. Estado Atual da Implementação

### ✅ JÁ IMPLEMENTADO
- DatabaseManager (gerenciamento de conexões)
- Connection (wrapper PDO com transações)
- QueryBuilder (completo e testado - 1052 linhas)
- Model (ORM básico completo - 1052 linhas)
- ModelCollection (coleções de modelos - 1052 linhas)

### 🔄 EM ANDAMENTO
- Migrations system (planejamento)

### ❌ PENDENTE
- Schema Builder
- Seeders
- Testes de integração completos

## 7. Próximos Passos Imediatos

### Semana 1: Completar Migrations System
1. Implementar Migration base class
2. Criar MigrationRepository
3. Implementar Migrator
4. Testar com SQLite em memória

### Semana 2: Implementar Schema Builder
1. Criar Blueprint class
2. Implementar Grammar classes básicas
3. Adicionar suporte a criação/modificação de tabelas
4. Testar com diferentes drivers

### Semana 3: Seeders e Integração
1. Implementar Seeder base class
2. Criar sistema de execução de seeders
3. Integrar com migrations
4. Criar exemplos práticos

## 8. Estrutura de Diretórios Proposta
```
vendors/coyote/Database/
├── Connection.php (✅)
├── DatabaseManager.php (✅)
├── QueryBuilder.php (✅)
├── Model.php (✅)
├── ModelCollection.php (✅)
├── Migrations/
│   ├── Migration.php
│   ├── MigrationRepository.php
│   ├── Migrator.php
│   └── DatabaseMigrationRepository.php
├── Schema/
│   ├── Blueprint.php
│   ├── Builder.php
│   └── Grammars/
│       ├── Grammar.php
│       ├── MySqlGrammar.php
│       ├── PostgresGrammar.php
│       └── SQLiteGrammar.php
└── Seeders/
    ├── Seeder.php
    └── DatabaseSeeder.php
```

## 9. Critérios de Aceitação

### Para considerar Fase 3 COMPLETA:
- [ ] QueryBuilder 100% funcional ✅
- [ ] Model ORM 100% funcional ✅
- [ ] ModelCollection 100% funcional ✅
- [ ] Migrations system implementado e testado
- [ ] Schema builder básico implementado
- [ ] Seeders básicos implementados
- [ ] Testes de integração Database passando

## 10. Considerações Técnicas

### Performance
- Lazy loading de conexões de banco
- Prepared statements para segurança
- Cache de queries simples
- Otimização para alta concorrência

### Segurança
- SQL injection prevention via prepared statements
- Escaping automático de nomes de tabelas/colunas
- Validação de inputs antes de queries
- Proteção contra ataques de timing

### Compatibilidade
- Suporte a MySQL 5.7+, PostgreSQL 10+, SQLite 3.8+
- PSR-4 autoloading
- PHP 8.1+ requirement
- Sem dependências externas além do PDO

---

**Status:** 📋 Fase 3 (Database) em andamento - QueryBuilder e ORM completos

**Próxima Ação:** Implementar sistema de Migrations para completar a fase