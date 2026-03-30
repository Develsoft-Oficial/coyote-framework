# Fase 4: Autenticação & Validação (Authentication & Validation)

## Visão Geral
Esta fase implementará o sistema completo de autenticação e validação do Coyote Framework. A autenticação já está parcialmente implementada (80%), e esta fase completará o sistema e adicionará validação de dados.

## Estado Atual da Implementação

### ✅ **AUTENTICAÇÃO - JÁ IMPLEMENTADO (80%)**
```
vendors/coyote/Auth/
├── AuthManager.php (322 linhas) - Gerenciador principal
├── Contracts/
│   ├── Authenticatable.php - Interface para usuários
│   ├── UserProvider.php - Interface para provedores de usuários
│   └── Guard.php - Interface para guards
├── Guards/
│   ├── Guard.php - Interface base
│   └── SessionGuard.php - Guard baseado em sessão
├── Providers/
│   └── DatabaseProvider.php - Provedor de usuários via banco
├── Models/
│   └── User.php - Modelo de usuário padrão
├── Middleware/
│   ├── Authenticate.php - Middleware de autenticação
│   └── RedirectIfAuthenticated.php - Middleware para redirecionamento
└── config/auth.php - Configuração do sistema
```

### ❌ **VALIDAÇÃO - NÃO IMPLEMENTADO (0%)**
- Validator com regras built-in
- Sistema de mensagens de erro
- Integração com formulários
- Validação de requests HTTP

## Objetivos Principais

### 1. Completar Sistema de Autenticação (20% restante)
1. **Session Management** - Gerenciamento completo de sessões
2. **Password Hashing** - Utilitários para hash de senhas
3. **Remember Me** - Funcionalidade "lembrar de mim"
4. **Password Reset** - Sistema de recuperação de senha
5. **Email Verification** - Verificação de email (opcional)

### 2. Implementar Sistema de Validação (100% novo)
1. **Validator Core** - Motor de validação principal
2. **Built-in Rules** - Regras de validação comuns
3. **Custom Rules** - Sistema para regras personalizadas
4. **Error Messages** - Mensagens de erro personalizáveis
5. **Form Integration** - Integração com formulários

### 3. Implementar Session & Cookie Management
1. **Session Manager** - Gerenciamento de sessões
2. **Session Drivers** - File, database, redis
3. **Cookie Management** - Manipulação de cookies seguros
4. **CSRF Protection** - Proteção contra CSRF

### 4. Implementar Form Builder Básico
1. **Form Builder** - Construção programática de formulários
2. **Form Types** - Tipos de campos (text, email, password, etc.)
3. **CSRF Integration** - Proteção automática contra CSRF
4. **Validation Integration** - Validação automática de formulários

## 1. Sistema de Autenticação (Completar)

### 1.1 Session Management
```php
vendors/coyote/Session/
├── SessionManager.php
├── Store.php (Interface)
├── FileSessionHandler.php
├── DatabaseSessionHandler.php
└── EncryptedStore.php
```

### 1.2 Password Utilities
```php
vendors/coyote/Auth/
├── Password/
│   ├── PasswordHasher.php
│   ├── BcryptHasher.php
│   ├── Argon2Hasher.php
│   └── PasswordBroker.php (para reset de senha)
```

### 1.3 Remember Me Functionality
- Tokens persistentes para "lembrar de mim"
- Armazenamento seguro em banco de dados
- Expiração configurável
- Revogação de tokens

### 1.4 Password Reset System
- Geração de tokens de reset
- Validação de tokens
- Email integration (básico)
- Expiração de tokens

## 2. Sistema de Validação

### 2.1 Validator Core
```php
vendors/coyote/Validation/
├── Validator.php (classe principal)
├── ValidationException.php
├── Rule.php (classe base para regras)
├── Rules/ (regras built-in)
│   ├── Required.php
│   ├── Email.php
│   ├── Min.php
│   ├── Max.php
│   ├── Numeric.php
│   ├── StringRule.php
│   ├── ArrayRule.php
│   ├── In.php
│   ├── NotIn.php
│   ├── Unique.php
│   └── Exists.php
└── MessageBag.php (coleção de mensagens de erro)
```

### 2.2 Validação de Dados
```php
$validator = new Validator($data, [
    'name' => 'required|string|min:3|max:255',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|string|min:8|confirmed',
    'age' => 'nullable|integer|min:18',
]);

if ($validator->fails()) {
    $errors = $validator->errors();
    // Manipular erros
}
```

### 2.3 Validação de Form Requests
```php
class UserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ];
    }
    
    public function authorize()
    {
        return true; // ou lógica de autorização
    }
}
```

## 3. Session & Cookie Management

### 3.1 Session Manager
- Drivers: file, database, redis, array
- Encryption automática de dados sensíveis
- Configuração por ambiente
- Garbage collection automático

### 3.2 Cookie Management
- Cookies seguros (HTTPOnly, Secure, SameSite)
- Encryption de valores de cookies
- Expiração configurável
- Domain/path restrictions

### 3.3 CSRF Protection
- Geração de tokens CSRF
- Validação automática em formulários
- Middleware de verificação CSRF
- Tokens por sessão

## 4. Form Builder Básico

### 4.1 Form Builder
```php
vendors/coyote/Forms/
├── FormBuilder.php
├── Form.php
├── Field.php (classe base)
├── Fields/
│   ├── TextField.php
│   ├── EmailField.php
│   ├── PasswordField.php
│   ├── TextareaField.php
│   ├── SelectField.php
│   ├── CheckboxField.php
│   └── FileField.php
└── Concerns/
    ├── HasValidation.php
    └── HasAttributes.php
```

### 4.2 Uso do Form Builder
```php
$form = new FormBuilder();
$form->add('name', 'text', [
    'label' => 'Nome',
    'required' => true,
    'placeholder' => 'Digite seu nome',
]);
$form->add('email', 'email', [
    'label' => 'Email',
    'required' => true,
]);
$form->add('password', 'password', [
    'label' => 'Senha',
    'required' => true,
]);
$form->add('submit', 'submit', ['label' => 'Cadastrar']);

echo $form->render();
```

## 5. Integração com Componentes Existentes

### 5.1 Integração Auth + Validation
- Validação de credenciais de login
- Validação de registro de usuário
- Validação de reset de senha
- Mensagens de erro específicas para auth

### 5.2 Integração Auth + Session
- Sessões para autenticação
- Cookies para "remember me"
- Regeneração de sessão após login
- Limpeza de sessão após logout

### 5.3 Integração Validation + Forms
- Validação automática de formulários
- Exibição de erros nos campos
- Manutenção de valores antigos (old input)
- CSRF protection automática

## 6. Plano de Implementação

### Semana 1: Completar Autenticação
1. **Session Management** (2 dias)
   - Implementar SessionManager
   - Criar drivers file e database
   - Integrar com Auth

2. **Password Utilities** (1 dia)
   - Implementar PasswordHasher (bcrypt/argon2)
   - Criar PasswordBroker para reset

3. **Remember Me & Password Reset** (2 dias)
   - Implementar tokens persistentes
   - Criar sistema de reset de senha
   - Testar integração completa

### Semana 2: Implementar Validação
1. **Validator Core** (2 dias)
   - Implementar Validator.php
   - Criar Rule base class
   - Implementar 10 regras básicas

2. **Error Handling & Messages** (1 dia)
   - Implementar MessageBag
   - Criar sistema de mensagens personalizáveis
   - Internacionalização básica

3. **Form Request Validation** (2 dias)
   - Implementar FormRequest base class
   - Integrar com controllers
   - Criar exemplos práticos

### Semana 3: Session & Forms
1. **Session & Cookie Management** (2 dias)
   - Completar Session system
   - Implementar Cookie management
   - Adicionar CSRF protection

2. **Form Builder** (2 dias)
   - Implementar FormBuilder
   - Criar field types básicos
   - Integrar com validation

3. **Integração Final** (1 dia)
   - Testar integração completa
   - Criar exemplo de aplicação
   - Documentar uso

## 7. Estrutura de Diretórios Final

```
vendors/coyote/
├── Auth/ (✅ 80% completo)
│   ├── AuthManager.php (✅)
│   ├── Contracts/ (✅)
│   ├── Guards/ (✅)
│   ├── Providers/ (✅)
│   ├── Models/ (✅)
│   ├── Middleware/ (✅)
│   ├── Password/ (🆕)
│   └── config/auth.php (✅)
├── Validation/ (🆕)
│   ├── Validator.php
│   ├── ValidationException.php
│   ├── Rule.php
│   ├── Rules/
│   ├── MessageBag.php
│   └── FormRequest.php
├── Session/ (🆕)
│   ├── SessionManager.php
│   ├── Store.php
│   ├── FileSessionHandler.php
│   ├── DatabaseSessionHandler.php
│   └── EncryptedStore.php
├── Forms/ (🆕)
│   ├── FormBuilder.php
│   ├── Form.php
│   ├── Field.php
│   ├── Fields/
│   └── Concerns/
└── Support/Helpers.php (adicionar helpers)
```

## 8. Helpers e Facades

### Helpers a adicionar:
```php
// auth.php helper
function auth($guard = null) {
    return app('auth')->guard($guard);
}

// session.php helper  
function session($key = null, $default = null) {
    if (is_null($key)) {
        return app('session');
    }
    
    if (is_array($key)) {
        return app('session')->put($key);
    }
    
    return app('session')->get($key, $default);
}

// validator.php helper
function validator(array $data, array $rules, array $messages = []) {
    return app('validator')->make($data, $rules, $messages);
}

// csrf_field() helper
function csrf_field() {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}
```

### Facades a adicionar:
```php
// Auth facade
class Auth extends Facade {
    protected static function getFacadeAccessor() {
        return 'auth';
    }
}

// Session facade
class Session extends Facade {
    protected static function getFacadeAccessor() {
        return 'session';
    }
}

// Validator facade
class Validator extends Facade {
    protected static function getFacadeAccessor() {
        return 'validator';
    }
}
```

## 9. Critérios de Aceitação

### Para considerar Fase 4 COMPLETA:
- [ ] Sistema de autenticação 100% funcional (login, logout, registro)
- [ ] Session management completo com múltiplos drivers
- [ ] Password hashing (bcrypt/argon2) funcionando
- [ ] Sistema de validação com pelo menos 15 regras built-in
- [ ] Form builder básico com suporte a 10 tipos de campos
- [ ] CSRF protection implementada e testada
- [ ] Helpers e facades para auth, session, validator
- [ ] Exemplo completo de aplicação com auth + validation
- [ ] Testes unitários e de integração passando

## 10. Considerações Técnicas

### Segurança
- Password hashing com algoritmos modernos (bcrypt/argon2)
- Proteção contra timing attacks
- CSRF protection em todos os formulários
- Session fixation protection
- Secure cookies (HTTPOnly, Secure, SameSite)

### Performance
- Lazy loading de serviços de auth
- Cache de configurações de validação
- Otimização de queries de sessão
- Minimização de overhead de validação

### Compatibilidade
- PHP 8.1+ requirement
- PSR-4 autoloading
- Sem dependências externas obrigatórias
- Suporte a múltiplos drivers de sessão

---

**Status:** 📋 Fase 4 planejada - Autenticação 80% completa, Validação 0%

**Próxima Ação:** Implementar Session Management para completar o sistema de autenticação