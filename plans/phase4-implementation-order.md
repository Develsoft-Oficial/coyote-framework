# Ordem de Implementação - Fase 4 (Auth & Validation)

## 🎯 **PRIORIDADE E ORDEM DE IMPLEMENTAÇÃO**

### **FASE 4A: SESSION MANAGEMENT (Semana 1)**
**Prioridade: ALTA** - Necessário para Auth funcionar corretamente

#### **Etapa 1: Estrutura Básica (Dia 1)**
1. **Criar diretórios:**
   ```
   mkdir vendors/coyote/Session
   mkdir vendors/coyote/Auth/Password
   ```

2. **Criar Session Interface:**
   - `vendors/coyote/Session/Store.php` - Interface base
   - Define métodos: get(), put(), forget(), flush(), etc.

3. **Criar SessionManager:**
   - `vendors/coyote/Session/SessionManager.php`
   - Gerenciamento de múltiplos drivers
   - Configuração via config/session.php

#### **Etapa 2: FileSessionHandler (Dia 2)**
1. **Implementar FileSessionHandler:**
   - `vendors/coyote/Session/FileSessionHandler.php`
   - Armazenamento em arquivos
   - Serialização segura

2. **Criar configuração:**
   - `config/session.php`
   - Drivers: file, database, array
   - Configurações de lifetime, path, etc.

#### **Etapa 3: Integração com Auth (Dia 3)**
1. **Atualizar SessionGuard:**
   - Modificar para usar SessionManager
   - Remover dependência direta de SessionInterface
   - Manter compatibilidade com código existente

2. **Testar integração:**
   - Criar teste básico de sessão
   - Verificar persistência entre requests
   - Testar flash data

#### **Etapa 4: DatabaseSessionHandler (Dia 4)**
1. **Implementar DatabaseSessionHandler:**
   - `vendors/coyote/Session/DatabaseSessionHandler.php`
   - Armazenamento em banco de dados
   - Tabela `sessions` com migrations

2. **Criar migration para tabela sessions:**
   - Usar Schema Builder da Fase 3
   - Campos: id, user_id, ip_address, user_agent, payload, last_activity

### **FASE 4B: PASSWORD UTILITIES (Semana 1 - Continuação)**

#### **Etapa 5: PasswordHasher (Dia 5)**
1. **Implementar PasswordHasher:**
   - `vendors/coyote/Auth/Password/PasswordHasher.php`
   - Interface para hashing de senhas
   - Métodos: hash(), verify(), needsRehash()

2. **Implementar BcryptHasher:**
   - `vendors/coyote/Auth/Password/BcryptHasher.php`
   - Usar `password_hash()` e `password_verify()`
   - Configuração de cost

#### **Etapa 6: PasswordBroker (Dia 6)**
1. **Implementar PasswordBroker:**
   - `vendors/coyote/Auth/Password/PasswordBroker.php`
   - Reset de senha
   - Geração e validação de tokens

2. **Criar tabela password_resets:**
   - Migration usando Schema Builder
   - Campos: email, token, created_at

#### **Etapa 7: Remember Me (Dia 7)**
1. **Implementar Remember Me:**
   - Extender SessionGuard
   - Tokens persistentes
   - Tabela `remember_tokens`

2. **Testar integração completa:**
   - Login com remember me
   - Persistência entre sessões
   - Logout com revogação de tokens

### **FASE 4C: VALIDATION SYSTEM (Semana 2)**

#### **Etapa 8: Validator Core (Dia 8-9)**
1. **Criar estrutura de Validation:**
   ```
   mkdir vendors/coyote/Validation
   mkdir vendors/coyote/Validation/Rules
   ```

2. **Implementar Validator:**
   - `vendors/coyote/Validation/Validator.php`
   - Parsing de regras: `required|email|min:6`
   - Validação de dados
   - Coleta de erros

3. **Implementar ValidationException:**
   - `vendors/coyote/Validation/ValidationException.php`
   - Extender Exception
   - Conter MessageBag com erros

#### **Etapa 9: Built-in Rules (Dia 10-11)**
1. **Implementar 15+ regras:**
   - RequiredRule, EmailRule, MinRule, MaxRule
   - NumericRule, IntegerRule, StringRule, ArrayRule
   - BooleanRule, DateRule, UrlRule, IpRule
   - RegexRule, InRule, NotInRule, SameRule, DifferentRule

2. **Criar MessageBag:**
   - `vendors/coyote/Validation/MessageBag.php`
   - Gerenciamento de mensagens de erro
   - Formatação e acesso

#### **Etapa 10: FormRequest (Dia 12)**
1. **Implementar FormRequest:**
   - `vendors/coyote/Validation/FormRequest.php`
   - Validação automática de requests
   - Redirecionamento com erros

2. **Integrar com controllers:**
   - Type-hint FormRequest em métodos
   - Validação automática antes da execução

### **FASE 4D: FORM BUILDER & INTEGRAÇÃO FINAL (Semana 3)**

#### **Etapa 11: Form Builder Core (Dia 13-14)**
1. **Criar estrutura de Forms:**
   ```
   mkdir vendors/coyote/Forms
   mkdir vendors/coyote/Forms/Fields
   mkdir vendors/coyote/Forms/Concerns
   ```

2. **Implementar FormBuilder:**
   - `vendors/coyote/Forms/FormBuilder.php`
   - Construção programática de formulários
   - 10+ tipos de campos

3. **Implementar Field types:**
   - TextField, EmailField, PasswordField
   - TextareaField, SelectField, CheckboxField
   - RadioField, FileField, HiddenField, SubmitField

#### **Etapa 12: CSRF Protection (Dia 15)**
1. **Implementar CSRF:**
   - Geração de tokens
   - Validação de tokens
   - Middleware de verificação

2. **Integrar com FormBuilder:**
   - CSRF token automático em formulários
   - Validação no backend

#### **Etapa 13: Helpers & Facades (Dia 16)**
1. **Implementar helpers:**
   - `auth()` - Acesso ao AuthManager
   - `session()` - Acesso ao SessionManager
   - `validator()` - Criação de Validator
   - `csrf_field()`, `csrf_token()`

2. **Implementar facades:**
   - Auth facade
   - Session facade
   - Validator facade

#### **Etapa 14: Integração Final & Testes (Dia 17-21)**
1. **Testes de integração:**
   - Fluxo completo de registro/login
   - Validação de formulários
   - Session persistence
   - Password reset

2. **Exemplo completo:**
   - Aplicação exemplo com auth + validation
   - Formulários com validação
   - Session management

## 🔄 **DEPENDÊNCIAS ENTRE ETAPAS**

```
SessionManager (Etapa 1-3)
    ↓
PasswordHasher (Etapa 5)
    ↓
SessionGuard atualizado (Etapa 3)
    ↓
Validator (Etapa 8-10)
    ↓
FormBuilder (Etapa 11-12)
    ↓
Helpers & Facades (Etapa 13)
    ↓
Integração Final (Etapa 14)
```

## ⚡ **ORDEM CRÍTICA (O que deve ser feito primeiro)**

1. **SessionManager** - Sem ele, Auth não funciona corretamente
2. **PasswordHasher** - Necessário para senhas seguras
3. **Validator Core** - Base para validação de formulários
4. **FormBuilder** - Depende do Validator
5. **CSRF Protection** - Depende do SessionManager
6. **Helpers & Facades** - Dependem de todos os componentes anteriores

## 🧪 **TESTES POR ETAPA**

### **Etapa 1-3 (Session):**
- [ ] SessionManager inicia sessão corretamente
- [ ] FileSessionHandler persiste dados
- [ ] SessionGuard usa SessionManager

### **Etapa 4-7 (Password):**
- [ ] PasswordHasher cria e verifica hashes
- [ ] PasswordBroker gera tokens de reset
- [ ] Remember me funciona entre sessões

### **Etapa 8-10 (Validation):**
- [ ] Validator valida dados corretamente
- [ ] 15+ regras built-in funcionam
- [ ] FormRequest valida automaticamente

### **Etapa 11-13 (Forms & CSRF):**
- [ ] FormBuilder cria formulários com campos
- [ ] CSRF tokens são gerados e validados
- [ ] Helpers e facades funcionam

### **Etapa 14 (Integração):**
- [ ] Fluxo completo de auth funciona
- [ ] Validação em formulários funciona
- [ ] Session persiste entre requests

## 📊 **MÉTRICAS DE PROGRESSO**

### **Marcos Principais:**
- **Marco 1:** SessionManager funcionando (Dia 3)
- **Marco 2:** Password hashing funcionando (Dia 7)
- **Marco 3:** Validator com 15+ regras (Dia 12)
- **Marco 4:** FormBuilder com CSRF (Dia 16)
- **Marco 5:** Integração completa (Dia 21)

### **Critérios de Conclusão por Etapa:**
- ✅ Todos os testes da etapa passando
- ✅ Integração com componentes anteriores funcionando
- ✅ Documentação básica criada
- ✅ Exemplo de uso implementado

## 🚨 **PONTOS DE ATENÇÃO**

### **Compatibilidade com Código Existente:**
1. **SessionGuard** já referencia `Coyote\Session\SessionInterface`
2. **DatabaseProvider** já funciona com QueryBuilder
3. **Config/auth.php** já está completo

### **Mudanças Necessárias:**
1. Atualizar SessionGuard para usar SessionManager
2. Atualizar DatabaseProvider para usar PasswordHasher
3. Criar Service Providers para novos componentes

### **Riscos Técnicos:**
1. **Session fixation** - Implementar regeneração de ID
2. **CSRF timing attacks** - Usar comparação segura
3. **Password hashing** - Usar algoritmos modernos

## 📅 **CRONOGRAMA RESUMIDO**

| Semana | Etapas | Entregáveis |
|--------|--------|-------------|
| **1** | 1-7 | SessionManager, PasswordHasher, Auth integrado |
| **2** | 8-10 | Validator com 15+ regras, FormRequest |
| **3** | 11-14 | FormBuilder, CSRF, Helpers, Integração completa |

**Total:** 21 dias (3 semanas) de implementação

---

**Status:** 📋 Ordem de implementação definida - Pronto para começar

**Próxima Ação:** Criar estrutura de diretórios e implementar SessionManager (Etapa 1)