# Roadmap Corrigido - Coyote Framework

## 📋 Visão Geral

Este roadmap corrige a sobreposição de fases identificada no projeto. Segue o plano original com fases separadas, mas considera o trabalho já realizado que estava fora de fase.

## 🎯 Objetivo Final

Criar um framework PHP completo mas leve, com foco em performance, modularidade e simplicidade, seguindo um plano de implementação faseado e organizado.

## 📊 Estado Atual (Reorganizado)

### **FASE 1: NÚCLEO - ✅ COMPLETA (100%)**
- Application, Container, Config
- Service Providers (Config, Event, Log, View)
- Autoloader PSR-4
- Bootstrap system

### **FASE 2: HTTP LAYER - ✅ COMPLETA (100%)**
- Request, Response, HttpKernel
- Router, Controllers, Middleware
- Views, ViewFactory, Template Engine básico

### **FASE 3: DATABASE - 🟡 PARCIAL (60%)**
- ✅ DatabaseManager, Connection
- ✅ QueryBuilder (1052 linhas, testado)
- ✅ Model ORM (1052 linhas, testado)
- ✅ ModelCollection (1052 linhas, testado)
- ❌ Migrations system (0%)
- ❌ Schema Builder (0%)
- ❌ Seeders (0%)

### **FASE 4: AUTH & VALIDATION - 🟡 PARCIAL (40%)**
- ✅ AuthManager, SessionGuard, DatabaseUserProvider (80%)
- ✅ User Model, Middleware (Authenticate, RedirectIfAuthenticated)
- ✅ Contracts (Authenticatable, UserProvider, Guard)
- ❌ Validation system (0%)
- ❌ Session Management (0%)
- ❌ Form Builder (0%)
- ❌ Password utilities (0%)

### **FASES 5-8: ❌ NÃO INICIADAS (0%)**
- Fase 5: Views & Cache
- Fase 6: CLI & Modules
- Fase 7: APIs & Advanced
- Fase 8: Testing & Docs

## 🗺️ Roadmap Corrigido (Ordem de Implementação)

### **ETAPA ATUAL: COMPLETAR FASE 3 (DATABASE)**

#### **Sprint 1: Migrations System (1-2 semanas)**
1. **Migration Base Class** - Classe abstrata com métodos `up()` e `down()`
2. **Migration Repository** - Armazenamento de metadados de migrations
3. **Migrator** - Execução e rollback de migrations
4. **Schema Builder básico** - Criação programática de tabelas
5. **Testes com SQLite** - Verificação de funcionalidade

#### **Sprint 2: Schema Builder & Seeders (1 semana)**
1. **Blueprint Class** - Definição de estrutura de tabelas
2. **Grammar Classes** - MySQL, PostgreSQL, SQLite
3. **Seeder Base Class** - Inserção de dados iniciais
4. **Database Seeder** - Execução de seeders
5. **Testes de integração** - Database completo

### **PRÓXIMA ETAPA: COMPLETAR FASE 4 (AUTH & VALIDATION)**

#### **Sprint 3: Session Management (1 semana)**
1. **Session Manager** - Gerenciamento de sessões
2. **Session Drivers** - File, database, array
3. **Cookie Management** - Cookies seguros
4. **CSRF Protection** - Proteção contra CSRF
5. **Integração com Auth** - Sessões para autenticação

#### **Sprint 4: Validation System (1-2 semanas)**
1. **Validator Core** - Motor de validação principal
2. **Built-in Rules** - 15+ regras comuns (required, email, min, max, etc.)
3. **Error Messages** - Mensagens personalizáveis
4. **Form Request Validation** - Validação automática de requests
5. **Integração com Forms** - Validação de formulários

#### **Sprint 5: Password Utilities & Form Builder (1 semana)**
1. **Password Hasher** - Bcrypt/Argon2 hashing
2. **Password Broker** - Reset de senha
3. **Remember Me** - Tokens persistentes
4. **Form Builder básico** - 10+ tipos de campos
5. **Integração completa** - Auth + Validation + Forms

### **ETAPAS FUTURAS (Após Fase 4 completa)**

#### **FASE 5: VIEWS & CACHE (3-4 semanas)**
1. **Template Engine improvements** - Herança, seções, directives
2. **View Components** - Componentes reutilizáveis
3. **Cache System** - File, database, array drivers
4. **Asset Management** - CSS, JS compilation/minification
5. **Performance optimizations** - Cache de views, lazy loading

#### **FASE 6: CLI & MODULES (3-4 semanas)**
1. **CLI Kernel** - Console application
2. **Essential Commands** - make:model, make:migration, migrate, etc.
3. **Module System** - Estrutura de módulos
4. **Module Manager** - Carregamento e gerenciamento
5. **Service Providers por módulo** - Registro automático

#### **FASE 7: APIS & ADVANCED (3-4 semanas)**
1. **API Resources** - Transformers, pagination, filtering
2. **Data Grid System** - Listagens com ordenação/filtro
3. **Monitoring** - Logging estruturado, metrics
4. **Security Features** - Rate limiting, CORS, XSS protection
5. **API Documentation** - OpenAPI/Swagger integration

#### **FASE 8: TESTING & DOCS (2-3 semanas)**
1. **Test Framework** - Unit tests, integration tests
2. **Documentação completa** - API docs, usage examples
3. **Exemplos práticos** - Demo applications
4. **Performance optimization** - Profiling, caching
5. **Release preparation** - Packaging, distribution

## 📅 Cronograma Estimado (Tempo Total: 14-20 semanas)

### **Fase 3 (Database) - Restante: 2-3 semanas**
- Sprint 1: Migrations (1-2 semanas)
- Sprint 2: Schema Builder & Seeders (1 semana)

### **Fase 4 (Auth & Validation) - 3-4 semanas**
- Sprint 3: Session Management (1 semana)
- Sprint 4: Validation System (1-2 semanas)
- Sprint 5: Password Utilities & Form Builder (1 semana)

### **Fases 5-8 - 9-13 semanas**
- Fase 5: Views & Cache (3-4 semanas)
- Fase 6: CLI & Modules (3-4 semanas)
- Fase 7: APIs & Advanced (3-4 semanas)
- Fase 8: Testing & Docs (2-3 semanas)

**Tempo total estimado:** 14-20 semanas (3.5-5 meses) de trabalho em tempo integral

## 🎯 Critérios de Sucesso por Fase

### **Fase 3 COMPLETA (Database):**
- [ ] QueryBuilder 100% funcional e testado ✅
- [ ] Model ORM 100% funcional e testado ✅
- [ ] ModelCollection 100% funcional e testado ✅
- [ ] Migrations system implementado e testado
- [ ] Schema builder básico implementado
- [ ] Seeders básicos implementados
- [ ] Testes de integração Database passando

### **Fase 4 COMPLETA (Auth & Validation):**
- [ ] Sistema de autenticação 100% funcional (login, logout, registro)
- [ ] Session management completo com múltiplos drivers
- [ ] Password hashing (bcrypt/argon2) funcionando
- [ ] Sistema de validação com 15+ regras built-in
- [ ] Form builder básico com 10+ tipos de campos
- [ ] CSRF protection implementada e testada
- [ ] Exemplo completo de aplicação com auth + validation

### **Fase 5 COMPLETA (Views & Cache):**
- [ ] Template engine com herança, seções, directives
- [ ] View components system implementado
- [ ] Cache system com 3+ drivers (file, database, array)
- [ ] Asset management básico
- [ ] Performance optimizations implementadas

### **Fase 6 COMPLETA (CLI & Modules):**
- [ ] CLI kernel funcional
- [ ] 10+ comandos essenciais implementados
- [ ] Module system básico implementado
- [ ] Service providers por módulo funcionando
- [ ] Exemplo de módulo completo

### **Fase 7 COMPLETA (APIs & Advanced):**
- [ ] API resources system implementado
- [ ] Data grid system básico
- [ ] Monitoring e logging estruturado
- [ ] Security features implementadas
- [ ] API documentation básica

### **Fase 8 COMPLETA (Testing & Docs):**
- [ ] Test framework com 80%+ coverage
- [ ] Documentação completa em português/inglês
- [ ] 3+ exemplos práticos de aplicações
- [ ] Performance optimizations finalizadas
- [ ] Release 1.0.0 preparada

## 🔧 Considerações Técnicas

### **Arquitetura Mantida:**
- PSR-4 autoloading
- Service Provider pattern
- Middleware pipeline
- Dependency Injection container
- Modular design

### **Dependências Externas:**
- **Obrigatórias:** PHP 8.1+, PDO extension
- **Opcionais:** Redis/Memcached (para cache), Symfony Console (para CLI)
- **Evitar:** Dependências pesadas, frameworks externos complexos

### **Performance Focus:**
- Lazy loading de serviços
- Cache agressivo em produção
- Minimização de overhead
- Otimização para alta concorrência

### **Security Focus:**
- SQL injection prevention via prepared statements
- XSS protection no template engine
- CSRF protection em formulários
- Password hashing com algoritmos modernos
- Secure session/cookie management

## 📈 Métricas de Progresso

### **Código Base Atual:**
- ~10,000 linhas de código PHP
- ~3,000 linhas de testes
- ~2,000 linhas de documentação
- 50+ arquivos de código

### **Meta Final (v1.0.0):**
- 25,000-30,000 linhas de código PHP
- 8,000-10,000 linhas de testes
- 5,000+ linhas de documentação
- 150+ arquivos de código
- < 2MB tamanho total do framework

## 🚀 Próximos Passos Imediatos (Hoje/Amanhã)

### **1. Implementar Migration Base Class**
```php
// vendors/coyote/Database/Migrations/Migration.php
abstract class Migration {
    abstract public function up();
    abstract public function down();
    // ...
}
```

### **2. Criar Migration Repository**
```php
// vendors/coyote/Database/Migrations/MigrationRepository.php
class MigrationRepository {
    public function getRan();
    public function getMigrations();
    public function log($file);
    public function delete($migration);
}
```

### **3. Implementar Migrator**
```php
// vendors/coyote/Database/Migrations/Migrator.php
class Migrator {
    public function run($paths = []);
    public function rollback($steps = 1);
    public function reset();
    public function status();
}
```

### **4. Testar com SQLite em memória**
- Criar teste de migration simples
- Verificar up/down functionality
- Testar rollback
- Integrar com DatabaseManager

## 📝 Decisões Estratégicas

### **1. Manter Auth já implementado como Fase 4?**
**DECISÃO:** SIM - Considerar o trabalho já feito como parte da Fase 4, mas completar os 20% restantes (session management, password utilities).

### **2. Implementar tudo do zero ou usar bibliotecas?**
**DECISÃO:** IMPLEMENTAR - O objetivo do Coyote é ser um framework leve e independente. Usar apenas dependências essenciais.

### **3. Priorizar features ou estabilidade?**
**DECISÃO:** ESTABILIDADE - Completar e estabilizar cada fase antes de avançar para a próxima.

### **4. Suporte a PHP 8.0 ou 8.1+?**
**DECISÃO:** PHP 8.1+ - Usar features modernas (enums, readonly properties, etc.) para código mais limpo e performático.

## ✅ Conclusão

Este roadmap corrigido estabelece um caminho claro e organizado para completar o Coyote Framework. A reorganização das fases resolve a sobreposição identificada e permite um desenvolvimento mais estruturado.

**Próxima ação imediata:** Implementar o sistema de Migrations para completar a Fase 3 (Database).