# Plano para Expansão de Testes Unitários

## 📋 **Visão Geral**

Expandir a suíte de testes unitários do Coyote Framework para garantir qualidade, estabilidade e confiabilidade do pacote publicado.

## 🎯 **Objetivos**

1. **Aumentar cobertura de testes** para > 80% do código
2. **Implementar testes para todos os módulos** principais
3. **Criar testes de integração** entre módulos
4. **Estabelecer pipeline de testes** automatizada
5. **Garantir qualidade** para releases futuras

## 📊 **Situação Atual**

### **Cobertura Atual (Estimada)**
- **Core**: 40% (testes básicos existentes)
- **Http**: 30% (testes de request/response)
- **Database**: 50% (testes de query builder)
- **Auth**: 20% (testes básicos)
- **Validation**: 60% (testes de validação)
- **Outros módulos**: < 20%

### **Estrutura de Testes Atual**
```
tests/
├── FormBuilderEnhancedTest.php
├── test-*.php (arquivos de teste ad-hoc)
└── (estrutura organizada necessária)
```

## 🏗️ **Arquitetura de Testes Proposta**

### **Estrutura Organizada**
```
tests/
├── Unit/                    # Testes unitários
│   ├── Core/               # Testes do núcleo
│   ├── Http/               # Testes HTTP
│   ├── Database/           # Testes de banco
│   ├── Auth/               # Testes de autenticação
│   ├── Validation/         # Testes de validação
│   └── ...                 # Outros módulos
├── Integration/            # Testes de integração
│   ├── ApplicationTest.php
│   ├── ServiceProvidersTest.php
│   └── ...
├── Feature/               # Testes de funcionalidade
│   ├── RoutingTest.php
│   ├── FormsTest.php
│   └── ...
├── Browser/              # Testes de browser (opcional)
├── TestCase.php          # Classe base para testes
├── bootstrap.php         # Bootstrap para testes
└── phpunit.xml           # Configuração PHPUnit
```

### **Dependências de Teste**
```json
{
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "mockery/mockery": "^1.6",
        "fakerphp/faker": "^1.23",
        "symfony/http-client": "^6.0",
        "guzzlehttp/guzzle": "^7.0"
    }
}
```

## 🔧 **Módulos Prioritários para Testes**

### **Prioridade 1: Core (Alta)**
- `Application` - Classe principal do framework
- `Container` - Injeção de dependências
- `Config` - Gerenciamento de configuração
- `ServiceProvider` - Provedores de serviço

### **Prioridade 2: Http (Alta)**
- `Request` - Manipulação de requisições
- `Response` - Manipulação de respostas
- `Router` - Sistema de rotas
- `Middleware` - Pipeline de middlewares

### **Prioridade 3: Database (Média-Alta)**
- `Connection` - Conexões de banco
- `QueryBuilder` - Construtor de queries
- `Model` - ORM básico
- `Migration` - Sistema de migrações

### **Prioridade 4: Auth (Média)**
- `AuthManager` - Gerenciador de autenticação
- `Guards` - Sistemas de guarda
- `UserProvider` - Provedores de usuário

### **Prioridade 5: Validation (Média)**
- `Validator` - Sistema de validação
- `Rules` - Regras de validação
- `FormRequest` - Validação em controllers

## 🧪 **Tipos de Testes a Implementar**

### **1. Testes Unitários (Unit)**
```php
// tests/Unit/Core/ApplicationTest.php
class ApplicationTest extends TestCase
{
    public function testApplicationCanBeInstantiated()
    {
        $app = new Application(__DIR__);
        $this->assertInstanceOf(Application::class, $app);
    }
    
    public function testApplicationHasContainer()
    {
        $app = new Application(__DIR__);
        $this->assertInstanceOf(Container::class, $app->getContainer());
    }
}
```

### **2. Testes de Integração (Integration)**
```php
// tests/Integration/ApplicationIntegrationTest.php
class ApplicationIntegrationTest extends TestCase
{
    public function testApplicationBootsServiceProviders()
    {
        $app = new Application(__DIR__);
        $app->boot();
        
        $this->assertTrue($app->isBooted());
        $this->assertNotEmpty($app->getLoadedProviders());
    }
}
```

### **3. Testes de Funcionalidade (Feature)**
```php
// tests/Feature/RoutingTest.php
class RoutingTest extends TestCase
{
    public function testBasicRouteRegistration()
    {
        $app = new Application(__DIR__);
        
        $app->router->get('/test', function() {
            return 'Hello World';
        });
        
        $response = $app->handle(Request::create('/test'));
        $this->assertEquals('Hello World', $response->getContent());
    }
}
```

### **4. Testes com Mocks**
```php
// tests/Unit/Http/RequestTest.php
class RequestTest extends TestCase
{
    public function testRequestGetsQueryParameters()
    {
        $mockServer = [
            'QUERY_STRING' => 'name=John&age=30',
            'REQUEST_METHOD' => 'GET'
        ];
        
        $request = new Request($mockServer);
        $this->assertEquals('John', $request->query('name'));
        $this->assertEquals(30, $request->query('age'));
    }
}
```

## 📈 **Plano de Implementação por Fase**

### **Fase 1: Infraestrutura (Semana 1)**
1. Configurar PHPUnit com `phpunit.xml`
2. Criar `TestCase.php` base
3. Configurar bootstrap para autoload
4. Adicionar dependências ao `composer.json`
5. Criar estrutura de diretórios

### **Fase 2: Testes Core (Semana 2)**
1. Testes para `Application` (100% cobertura)
2. Testes para `Container` (100% cobertura)
3. Testes para `Config` (100% cobertura)
4. Testes para `ServiceProvider` (100% cobertura)

### **Fase 3: Testes Http (Semana 3)**
1. Testes para `Request` e `Response`
2. Testes para `Router` e rotas
3. Testes para `Middleware` pipeline
4. Testes para `Controller` base

### **Fase 4: Testes Database (Semana 4)**
1. Testes para `Connection` e PDO
2. Testes para `QueryBuilder`
3. Testes para `Model` e ORM
4. Testes para `Migration`

### **Fase 5: Testes Auth e Validation (Semana 5)**
1. Testes para `AuthManager` e `Guards`
2. Testes para `Validator` e regras
3. Testes para `FormRequest`

### **Fase 6: Testes de Integração (Semana 6)**
1. Testes entre módulos
2. Testes de aplicação completa
3. Testes de performance

## 🛠️ **Ferramentas e Configurações**

### **phpunit.xml**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
        <report>
            <html outputDirectory="coverage"/>
            <clover outputFile="coverage.xml"/>
        </report>
    </coverage>
    
    <php>
        <ini name="error_reporting" value="-1"/>
        <ini name="display_errors" value="1"/>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

### **TestCase.php Base**
```php
<?php

namespace Coyote\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Coyote\Core\Application;

abstract class TestCase extends BaseTestCase
{
    protected Application $app;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Application(__DIR__ . '/../');
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->app);
    }
    
    protected function createMockRequest($method = 'GET', $uri = '/')
    {
        // Helper para criar requests de teste
    }
}
```

## 📊 **Métricas e Cobertura Alvo**

### **Cobertura por Módulo (Meta)**
- **Core**: 95%+
- **Http**: 90%+
- **Database**: 85%+
- **Auth**: 80%+
- **Validation**: 90%+
- **Média Geral**: 85%+

### **Métricas de Qualidade**
- **Testes Passando**: 100%
- **Build Time**: < 5 minutos
- **Code Complexity**: Mantida ou reduzida
- **Technical Debt**: Monitorada via PHPStan

## 🧪 **Testes Específicos por Módulo**

### **Core/Application**
```php
// tests/Unit/Core/ApplicationTest.php
class ApplicationTest extends TestCase
{
    // Testes planejados:
    // 1. Instanciação com diferentes paths
    // 2. Boot de service providers
    // 3. Resolução do container
    // 4. Environment detection
    // 5. Configuração loading
    // 6. Event dispatching
    // 7. Middleware pipeline
    // 8. Error handling
    // 9. Shutdown sequence
    // 10. Singleton pattern
}
```

### **Http/Router**
```php
// tests/Unit/Http/RouterTest.php
class RouterTest extends TestCase
{
    // Testes planejados:
    // 1. Route registration (GET, POST, PUT, DELETE, etc.)
    // 2. Route parameters parsing
    // 3. Route matching
    // 4. Middleware attachment
    // 5. Route groups
    // 6. Named routes
    // 7. Route prefixing
    // 8. Route resource
    // 9. Route caching
    // 10. Error routes (404, 405)
}
```

### **Database/QueryBuilder**
```php
// tests/Unit/Database/QueryBuilderTest.php
class QueryBuilderTest extends TestCase
{
    // Testes planejados:
    // 1. Basic SELECT queries
    // 2. WHERE conditions
    // 3. JOIN operations
    // 4. GROUP BY and HAVING
    // 5. ORDER BY and LIMIT
    // 6. INSERT operations
    // 7. UPDATE operations
    // 8. DELETE operations
    // 9. Transaction handling
    // 10. Query logging
}
```

## ⚠️ **Riscos e Mitigações**

### **Risco 1: Complexidade dos Testes**
- **Problema**: Testes muito complexos ou frágeis
- **Mitigação**: Focar em testes simples e isolados primeiro
- **Solução**: Refatorar código para melhor testabilidade

### **Risco 2: Performance dos Testes**
- **Problema**: Suíte de testes muito lenta
- **Mitigação**: Usar SQLite in-memory para testes de banco
- **Solução**: Paralelizar testes com PHPUnit

### **Risco 3: Manutenção dos Testes**
- **Problema**: Testes quebram com frequência
- **Mitigação**: Escrever testes estáveis, não frágeis
- **Solução**: Usar factories e fakers para dados de teste

### **Risco 4: Cobertura Insuficiente**
- **Problema**: Não atingir metas de cobertura
- **Mitigação**: Priorizar módulos críticos primeiro
- **Solução**: Usar ferramentas de análise de cobertura

## 📅 **Cronograma Detalhado**

| Semana | Módulo | Testes Planejados | Horas Estimadas |
|--------|--------|-------------------|-----------------|
| 1 | Infraestrutura | Configuração PHPUnit, TestCase, bootstrap | 8 |
| 2 | Core | Application, Container, Config, ServiceProvider | 12 |
| 3 | Http | Request, Response, Router, Middleware | 10 |
| 4 | Database | Connection, QueryBuilder, Model, Migration | 12 |
| 5 | Auth | AuthManager, Guards, UserProvider | 8 |
| 6 | Validation | Validator, Rules, FormRequest | 8 |
| 7 | Integração | Application boot, Service integration | 10 |
| 8 | Feature | Routing, Forms, Authentication flows | 12 |
| **Total** | | | **80 horas** |

## 🔄 **Integração com CI/CD**

### **GitHub Actions Workflow**
```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: ['8.1', '8.2', '8.3']
        
    steps:
    - uses: actions/checkout@v3
    - uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php }}
        coverage: xdebug
        
    - run: composer install
    - run: vendor/bin/phpunit --coverage-clover=coverage.xml
    
    - name: Upload coverage
      uses: codecov/codecov-action@v3
      with:
        file: coverage.xml
```

### **Badges para README**
```markdown
[![Tests](https://github.com/coyoteframework/framework/workflows/Tests/badge.svg)](https://github.com/coyoteframework/framework/actions)
[![Code Coverage](https://codecov.io/gh/coyoteframework/framework/branch/main/graph/badge.svg)](https://codecov.io/gh/coyoteframework/framework)
```

## 📚 **Documentação para Desenvolvedores**

### **Escrevendo Novos Testes**
```bash
# 1. Criar arquivo de teste no diretório correto
# 2. Estender TestCase base
# 3. Seguir convenção de nomenclatura: ClassNameTest.php
# 4. Usar métodos descriptivos: testMethodDoesSomething()
# 5. Executar testes localmente antes de commitar

# Executar testes específicos
vendor/bin/phpunit tests/Unit/Core/ApplicationTest.php

# Executar com cobertura
vendor/bin/phpunit --coverage-html coverage
```

### **Helpers de Teste**
```php
// Em TestCase.php
protected function assertArrayHasKeys(array $keys, array $array, string $message = '')
{
    foreach ($keys as $key) {
        $this->assertArrayHasKey($key, $array, $message ?: "Array should have key: {$key}");
    }
}

protected function createMockModel()
{
    // Factory para modelos de teste
}
```

---

**Status**: Pronto para implementação  
**Prioridade**: Alta (qualidade do pacote)  
**Complexidade**: Alta (abrange todo o framework)  
**Impacto**: Crítico (confiança nas releases)