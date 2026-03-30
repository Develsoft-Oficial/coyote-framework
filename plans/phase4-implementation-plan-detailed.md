# Plano Detalhado de Implementação - Fase 4 (Auth & Validation)

## 📊 Estado Atual da Fase 4

### ✅ **COMPONENTES JÁ IMPLEMENTADOS (80%)**
1. **AuthManager** - Gerenciador principal de autenticação (322 linhas)
2. **SessionGuard** - Guard baseado em sessão (465 linhas)
3. **DatabaseProvider** - Provedor de usuários via banco de dados
4. **User Model** - Modelo de usuário padrão
5. **Contracts** - Interfaces (Authenticatable, UserProvider, Guard)
6. **Middleware** - Authenticate e RedirectIfAuthenticated
7. **Configuração** - config/auth.php completo (276 linhas)

### ❌ **COMPONENTES FALTANTES (20% restante)**

#### **1. Session Management (0%)**
- SessionManager - Gerenciador de sessões
- Session Store Interface
- FileSessionHandler
- DatabaseSessionHandler
- EncryptedStore

#### **2. Password Utilities (0%)**
- PasswordHasher (bcrypt/argon2)
- PasswordBroker (reset de senha)
- Remember Me functionality

#### **3. Validation System (0%)**
- Validator Core
- Built-in Rules (15+ regras)
- MessageBag para erros
- FormRequest base class

#### **4. Form Builder (0%)**
- FormBuilder
- Field types (text, email, password, etc.)
- CSRF integration
- Validation integration

#### **5. Helpers & Facades (0%)**
- auth() helper
- session() helper
- validator() helper
- csrf_field() helper
- Facades (Auth, Session, Validator)

## 🎯 **PLANO DE IMPLEMENTAÇÃO DETALHADO**

### **SEMANA 1: SESSION MANAGEMENT & PASSWORD UTILITIES**

#### **Dia 1-2: Session System**
1. **Criar estrutura de diretórios:**
   ```
   vendors/coyote/Session/
   ├── SessionManager.php
   ├── Store.php (Interface)
   ├── FileSessionHandler.php
   ├── DatabaseSessionHandler.php
   └── EncryptedStore.php
   ```

2. **Implementar SessionManager:**
   - Gerenciamento de múltiplos drivers
   - Configuração via config/session.php
   - Métodos: start(), save(), destroy(), regenerate()
   - Flash data support

3. **Implementar FileSessionHandler:**
   - Armazenamento em arquivos
   - Garbage collection automático
   - Serialização segura

#### **Dia 3: DatabaseSessionHandler**
- Armazenamento em banco de dados
- Tabela `sessions` com estrutura adequada
- Limpeza automática de sessões expiradas
- Integração com DatabaseManager

#### **Dia 4-5: Password Utilities**
1. **Criar estrutura:**
   ```
   vendors/coyote/Auth/Password/
   ├── PasswordHasher.php
   ├── BcryptHasher.php
   ├── Argon2Hasher.php
   └── PasswordBroker.php
   ```

2. **Implementar PasswordHasher:**
   - Suporte a bcrypt e argon2
   - Verificação de senhas
   - Re-hashing automático

3. **Implementar PasswordBroker:**
   - Geração de tokens de reset
   - Validação de tokens
   - Expiração configurável

#### **Dia 6-7: Remember Me & Integration**
1. **Remember Me functionality:**
   - Tokens persistentes
   - Armazenamento em banco
   - Revogação de tokens

2. **Integração com AuthManager:**
   - Atualizar SessionGuard para usar SessionManager
   - Adicionar password hashing
   - Implementar remember me

### **SEMANA 2: VALIDATION SYSTEM**

#### **Dia 1-2: Validator Core**
1. **Criar estrutura:**
   ```
   vendors/coyote/Validation/
   ├── Validator.php
   ├── ValidationException.php
   ├── Rule.php
   ├── Rules/
   │   ├── RequiredRule.php
   │   ├── EmailRule.php
   │   ├── MinRule.php
   │   └── ...
   ├── MessageBag.php
   └── FormRequest.php
   ```

2. **Implementar Validator:**
   - Parsing de regras
   - Validação de dados
   - Coleta de erros
   - Mensagens personalizáveis

#### **Dia 3-4: Built-in Rules (15+ regras)**
- required, email, min, max, numeric, integer
- string, array, boolean, date, url, ip
- regex, in, not_in, same, different
- size, between, digits, alpha, alpha_num

#### **Dia 5: Error Handling & Messages**
- MessageBag para gerenciamento de erros
- Internacionalização básica
- Formatação de mensagens
- Acesso a erros por campo

#### **Dia 6-7: Form Request Validation**
- FormRequest base class
- Integração com controllers
- Validação automática de requests
- Redirecionamento com erros

### **SEMANA 3: FORM BUILDER & INTEGRAÇÃO FINAL**

#### **Dia 1-2: Form Builder Core**
1. **Criar estrutura:**
   ```
   vendors/coyote/Forms/
   ├── FormBuilder.php
   ├── Form.php
   ├── Field.php
   ├── Fields/
   │   ├── TextField.php
   │   ├── EmailField.php
   │   ├── PasswordField.php
   │   └── ...
   └── Concerns/
       └── HasValidation.php
   ```

2. **Implementar FormBuilder:**
   - Construção programática de formulários
   - 10+ tipos de campos
   - CSRF protection automática
   - Old input support

#### **Dia 3-4: Field Types & CSRF**
- text, email, password, textarea, select
- checkbox, radio, file, hidden, submit
- CSRF token generation
- CSRF validation middleware

#### **Dia 5: Helpers & Facades**
1. **Helpers:**
   ```php
   function auth($guard = null)
   function session($key = null, $default = null)
   function validator(array $data, array $rules, array $messages = [])
   function csrf_field()
   function csrf_token()
   ```

2. **Facades:**
   - Auth facade
   - Session facade
   - Validator facade

#### **Dia 6-7: Integração Final & Testes**
1. **Integração completa:**
   - Auth + Session + Validation
   - Form builder com validation
   - CSRF protection em todos os formulários

2. **Testes de integração:**
   - Fluxo completo de registro/login
   - Validação de formulários
   - Session management
   - Password reset

## 📁 **ESTRUTURA DE ARQUIVOS FINAL**

```
vendors/coyote/
├── Auth/ (✅ 80% completo)
│   ├── AuthManager.php
│   ├── Contracts/
│   ├── Guards/
│   ├── Providers/
│   ├── Models/
│   ├── Middleware/
│   └── Password/ (🆕)
│       ├── PasswordHasher.php
│       ├── BcryptHasher.php
│       ├── Argon2Hasher.php
│       └── PasswordBroker.php
├── Session/ (🆕)
│   ├── SessionManager.php
│   ├── Store.php
│   ├── FileSessionHandler.php
│   ├── DatabaseSessionHandler.php
│   └── EncryptedStore.php
├── Validation/ (🆕)
│   ├── Validator.php
│   ├── ValidationException.php
│   ├── Rule.php
│   ├── Rules/
│   ├── MessageBag.php
│   └── FormRequest.php
├── Forms/ (🆕)
│   ├── FormBuilder.php
│   ├── Form.php
│   ├── Field.php
│   ├── Fields/
│   └── Concerns/
└── Support/Helpers.php (🆕 - adicionar helpers)
```

## 🔧 **DEPENDÊNCIAS E INTEGRAÇÕES**

### **Dependências Existentes:**
1. **DatabaseManager** - Para DatabaseSessionHandler e PasswordBroker
2. **Request/Response** - Para FormRequest validation
3. **Config** - Para configurações de auth, session, validation

### **Integrações Necessárias:**
1. **SessionGuard → SessionManager** - Atualizar para usar novo sistema
2. **DatabaseProvider → PasswordHasher** - Adicionar hashing de senhas
3. **FormBuilder → Validator** - Validação automática de formulários
4. **Middleware → CSRF** - Proteção em todas as rotas POST

## 🧪 **TESTES E CRITÉRIOS DE ACEITAÇÃO**

### **Testes Unitários:**
- [ ] SessionManager com todos os drivers
- [ ] PasswordHasher (bcrypt e argon2)
- [ ] Validator com 15+ regras
- [ ] FormBuilder com 10+ tipos de campos
- [ ] CSRF token generation/validation

### **Testes de Integração:**
- [ ] Fluxo completo de registro/login/logout
- [ ] Password reset functionality
- [ ] Form validation com redirecionamento
- [ ] Session persistence entre requests
- [ ] Remember me functionality

### **Critérios de Aceitação (Fase 4 COMPLETA):**
- [ ] Sistema de autenticação 100% funcional
- [ ] Session management com múltiplos drivers
- [ ] Password hashing (bcrypt/argon2) funcionando
- [ ] Sistema de validação com 15+ regras built-in
- [ ] Form builder com 10+ tipos de campos
- [ ] CSRF protection implementada e testada
- [ ] Helpers e facades para auth, session, validator
- [ ] Exemplo completo de aplicação com auth + validation
- [ ] Testes unitários e de integração passando

## ⚠️ **RISCO E MITIGAÇÃO**

### **Riscos Identificados:**
1. **Complexidade do Session system** - Pode levar mais tempo
2. **Integração entre componentes** - Pode revelar incompatibilidades
3. **Security concerns** - CSRF, session fixation, password hashing

### **Mitigação:**
1. Começar com FileSessionHandler (mais simples)
2. Testar integração incrementalmente
3. Seguir security best practices
4. Usar bibliotecas padrão do PHP (password_hash, session_start)

## 📅 **CRONOGRAMA ESTIMADO**

- **Semana 1:** Session Management & Password Utilities (7 dias)
- **Semana 2:** Validation System (7 dias)
- **Semana 3:** Form Builder & Integração Final (7 dias)

**Total estimado:** 21 dias (3 semanas) de trabalho

## 🚀 **PRÓXIMOS PASSOS IMEDIATOS**

1. **Criar estrutura de diretórios** para Session, Validation, Forms
2. **Implementar SessionManager** básico com FileSessionHandler
3. **Integrar SessionManager** com SessionGuard existente
4. **Criar PasswordHasher** com bcrypt support
5. **Atualizar DatabaseProvider** para usar password hashing

---

**Status:** 📋 Plano detalhado criado - Pronto para iniciar implementação da Fase 4

**Próxima Ação:** Criar estrutura de diretórios e implementar SessionManager