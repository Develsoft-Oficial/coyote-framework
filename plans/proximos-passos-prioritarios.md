# Próximos Passos Prioritários - Coyote Framework

## 🎯 Visão Geral

Com base na análise do estado atual, identificamos que a Fase 3 (Database) está quase completa, mas misturou componentes de fases posteriores. Aqui está o plano de ação corrigido para os próximos passos.

## 📋 Plano de Ação Imediato (Próximas 2-3 Semanas)

### **SEMANA 1: COMPLETAR FASE 3 (DATABASE)**

#### **Objetivo:** Finalizar o sistema de banco de dados com migrations
- [ ] **Criar sistema de Migrations**
  - `vendors/coyote/Database/Migrations/Migration.php` (classe base)
  - `vendors/coyote/Database/Migrations/MigrationRepository.php`
  - `vendors/coyote/Database/Migrations/Migrator.php`
  
- [ ] **Implementar Schema Builder**
  - `vendors/coyote/Database/Schema/Blueprint.php`
  - `vendors/coyote/Database/Schema/Grammars/`
  - Suporte para criação/modificação de tabelas

- [ ] **Criar testes de integração**
  - Testar migrations com SQLite em memória
  - Testar rollback de migrations
  - Verificar compatibilidade com QueryBuilder

#### **Entregáveis:**
1. Sistema de migrations funcional
2. Schema builder básico
3. Testes de integração Database completo

### **SEMANA 2: COMPLETAR FASE 4 (AUTHENTICATION & VALIDATION)**

#### **Objetivo:** Finalizar autenticação e adicionar validação
- [ ] **Implementar sistema de Validation**
  - `vendors/coyote/Validation/Validator.php`
  - `vendors/coyote/Validation/Rules/` (Required, Email, Min, Max, etc.)
  - `vendors/coyote/Validation/ValidationException.php`

- [ ] **Integrar Validation com Auth**
  - Validação de credenciais de login
  - Validação de registro de usuário
  - Mensagens de erro personalizadas

- [ ] **Melhorar sistema de Session**
  - `vendors/coyote/Session/SessionManager.php`
  - `vendors/coyote/Session/Store.php`
  - Integração com Auth

#### **Entregáveis:**
1. Sistema de validação completo
2. Integração Auth + Validation
3. Session management melhorado

### **SEMANA 3: PREPARAR PARA FASE 5 (VIEWS & CACHE)**

#### **Objetivo:** Melhorar views e iniciar cache system
- [ ] **Melhorar Template Engine**
  - Adicionar herança de templates
  - Implementar seções e yields
  - Adicionar directives básicas

- [ ] **Implementar Cache System básico**
  - `vendors/coyote/Cache/CacheManager.php`
  - `vendors/coyote/Cache/Stores/FileStore.php`
  - `vendors/coyote/Cache/Stores/ArrayStore.php`

- [ ] **Criar exemplo completo**
  - Aplicação exemplo com auth, database e views
  - Documentação de uso
  - Testes de integração

#### **Entregáveis:**
1. Template engine melhorado
2. Cache system básico
3. Exemplo completo funcional

## 🚀 Ações de Curto Prazo (Próximos 2-3 Dias)

### **DIA 1: Reorganizar planos e documentação**
- [ ] Renomear `phase3-database-auth.md` → `phase3-database-only.md`
- [ ] Criar `phase4-auth-validation.md` com escopo correto
- [ ] Atualizar `RESUMO-FASE3-COMPLETO.md` para refletir estado real
- [ ] Criar `roadmap-corrigido.md` com fases separadas

### **DIA 2: Testar integração atual**
- [ ] Executar testes existentes (`test-querybuilder-simple.php`, `test-orm-basic.php`)
- [ ] Criar teste de integração Database + Auth
- [ ] Verificar compatibilidade entre componentes
- [ ] Identificar bugs ou incompatibilidades

### **DIA 3: Planejar implementação de Migrations**
- [ ] Projetar arquitetura do Migration system
- [ ] Definir interface da Migration base class
- [ ] Planejar integração com DatabaseManager
- [ ] Criar esboço dos arquivos necessários

## 🔧 Componentes Prioritários por Complexidade

### **Alta Prioridade (Críticos)**
1. **Migrations System** - Necessário para uso prático do ORM
2. **Validation System** - Essencial para aplicações web
3. **Session Management** - Requisito para Auth funcionar corretamente

### **Média Prioridade (Importantes)**
1. **Template Engine improvements** - Melhorar developer experience
2. **Cache System básico** - Performance básica
3. **Schema Builder** - Para criação programática de tabelas

### **Baixa Prioridade (Futuro)**
1. **CLI Commands** - Pode esperar até Fase 6
2. **Module System** - Complexo, deixar para depois
3. **API Resources** - Especializado, priorizar depois

## 📊 Métricas de Progresso

### **Para considerar Fase 3 COMPLETA:**
- [ ] QueryBuilder 100% funcional ✅
- [ ] Model ORM 100% funcional ✅  
- [ ] ModelCollection 100% funcional ✅
- [ ] Migrations system implementado ❌
- [ ] Schema builder básico implementado ❌
- [ ] Testes de integração Database passando ❌

### **Para considerar Fase 4 COMPLETA:**
- [ ] AuthManager 100% funcional ✅
- [ ] SessionGuard 100% funcional ✅
- [ ] Validation system implementado ❌
- [ ] Form builder básico implementado ❌
- [ ] Session management completo ❌

## 🎯 Decisões Estratégicas

### **1. Manter ou refatorar Auth já implementado?**
**Decisão:** MANTER - O sistema de auth já está 80% implementado e funciona. Melhor completá-lo como parte da Fase 4 do que refatorar.

### **2. Implementar Migrations antes ou depois de Validation?**
**Decisão:** ANTES - Migrations é parte da Fase 3 (Database) e é pré-requisito para uso prático do ORM.

### **3. Usar bibliotecas externas ou implementar tudo?**
**Decisão:** IMPLEMENTAR - O objetivo do Coyote é ser um framework leve e independente. Usar apenas dependências essenciais (PDO para database).

### **4. Priorizar features ou estabilidade?**
**Decisão:** ESTABILIDADE - Completar e estabilizar as fases atuais antes de adicionar novas features.

## 📝 Checklist de Próximas Ações

### **Imediato (hoje/amanhã)**
- [ ] Revisar e aprovar este plano
- [ ] Renomear arquivos de plano confusos
- [ ] Criar estrutura para Migration system
- [ ] Atualizar documentação de progresso

### **Curto Prazo (esta semana)**
- [ ] Implementar Migration base class
- [ ] Criar Migrator e MigrationRepository
- [ ] Testar migrations com SQLite
- [ ] Documentar uso do Migration system

### **Médio Prazo (próximas 2 semanas)**
- [ ] Implementar Validation system
- [ ] Integrar Validation com Auth
- [ ] Melhorar Session management
- [ ] Criar exemplo completo de aplicação

## 🔗 Dependências e Riscos

### **Dependências:**
1. Migration system depende do DatabaseManager (✅ pronto)
2. Validation depende do Request/Response (✅ pronto)
3. Cache system é independente (pode ser feito depois)

### **Riscos:**
1. **Complexidade do Migration system** - Pode levar mais tempo que o esperado
2. **Integração entre componentes** - Pode revelar incompatibilidades
3. **Manutenção de código existente** - O código já tem ~10k linhas

### **Mitigação:**
1. Começar com Migration system simples (apenas up/down)
2. Testar integração incrementalmente
3. Manter testes unitários para código existente

## ✅ Conclusão

**Próximo passo imediato:** Implementar o sistema de Migrations para completar a Fase 3 (Database) verdadeira.

**Recomendação:** Seguir esta ordem:
1. Completar Migrations (Fase 3)
2. Completar Validation (Fase 4)  
3. Melhorar Views e adicionar Cache básico (Fase 5)
4. Implementar CLI Commands (Fase 6)

Este plano corrige a sobreposição de fases e estabelece um caminho claro para transformar o Coyote Framework em um framework funcional e prático.