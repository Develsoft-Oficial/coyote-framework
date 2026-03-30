# Plano para Resolver Conflito de Autoloader

## 📋 **Análise do Problema**

### **Situação Atual**
1. **Composer Autoloader** - Registra `Coyote\` → `src/` via PSR-4 no `composer.json`
2. **Coyote Autoloader** - Classe `Coyote\Autoloader` em `src/Support/Autoloader.php` que se auto-registra
3. **Conflito** - Quando o arquivo `Autoloader.php` é carregado pelo Composer, ele tenta se registrar novamente, causando:
   - `Cannot redeclare class Coyote\Autoloader`
   - Duplicação de registro de autoload

### **Cenários de Uso**
1. **Como pacote Composer** - Usuário só precisa do autoloader do Composer
2. **Desenvolvimento local** - Framework pode precisar de seu próprio autoloader para otimizações
3. **Ambientes sem Composer** - Framework precisa funcionar standalone

## 🎯 **Objetivos da Solução**

1. **Compatibilidade total** com Composer PSR-4
2. **Otimização de performance** para produção
3. **Funcionamento standalone** quando necessário
4. **Sem conflitos** de dupla declaração
5. **Backward compatibility** com código existente

## 🔧 **Opções de Solução**

### **Opção 1: Autoloader Condicional (Recomendada)**
```php
// src/Support/Autoloader.php
namespace Coyote;

if (!class_exists('Coyote\Autoloader', false)) {
    class Autoloader { ... }
}

// Registrar apenas se não estiver em ambiente Composer
if (!defined('COMPOSER_AUTOLOADER_REGISTERED')) {
    Autoloader::register();
}
```

**Vantagens:**
- Simples de implementar
- Resolve conflito imediatamente
- Mantém funcionalidade standalone
- Backward compatible

**Desvantagens:**
- Ainda carrega classe desnecessária no Composer

### **Opção 2: Separar em Dois Arquivos**
```
src/
├── Support/
│   ├── Autoloader.php          # Classe principal (sem auto-registro)
│   └── AutoloaderStandalone.php # Auto-registro para standalone
└── bootstrap.php               # Ponto de entrada para standalone
```

**Vantagens:**
- Separação clara de responsabilidades
- Otimizado para cada cenário
- Sem código desnecessário

**Desvantagens:**
- Mais complexo
- Requer mudanças na estrutura

### **Opção 3: Remover Auto-registro do Autoloader**
- Remover `Autoloader::register()` do final do arquivo
- Registrar apenas via Composer scripts
- Criar arquivo `bootstrap.php` para standalone

**Vantagens:**
- Mais limpo
- Totalmente compatível com Composer

**Desvantagens:**
- Perde funcionalidade standalone
- Requer arquivo adicional

## 🚀 **Plano de Implementação (Opção 1 + 3 Híbrida)**

### **Fase 1: Preparação**
1. Analisar todos os usos do Autoloader no código
2. Criar testes para validar cada cenário
3. Documentar mudanças necessárias

### **Fase 2: Modificar Autoloader.php**
```php
// 1. Adicionar verificação de classe existente
if (!class_exists('Coyote\Autoloader', false)) {
    class Autoloader { ... }
}

// 2. Modificar auto-registro para ser condicional
if (!defined('COYOTE_COMPOSER_AUTOLOAD') && php_sapi_name() !== 'cli') {
    // Só auto-registrar se não for via Composer e não for CLI
    Autoloader::register();
}

// 3. Adicionar constante para controle
define('COYOTE_AUTOLOADER_LOADED', true);
```

### **Fase 3: Atualizar Composer Scripts**
```json
{
    "scripts": {
        "post-autoload-dump": [
            "Coyote\\Autoloader::buildClassMap"
        ],
        "optimize": [
            "@php src/Cli/Commands/OptimizeCommand.php"
        ]
    }
}
```

### **Fase 4: Criar Bootstrap para Standalone**
```php
// bootstrap.php
<?php
define('COYOTE_STANDALONE', true);
require_once __DIR__ . '/src/Support/Autoloader.php';
// Registrar manualmente se necessário
if (!Coyote\Autoloader::isRegistered()) {
    Coyote\Autoloader::register();
}
```

### **Fase 5: Atualizar Documentação**
1. Documentar uso com Composer
2. Documentar uso standalone
3. Exemplos para cada cenário

## 📊 **Testes Necessários**

### **Teste 1: Composer Installation**
```bash
cd test-clean-install
composer require coyote/framework:@dev
php -r "require 'vendor/autoload.php'; echo class_exists('Coyote\\Core\\Application') ? 'OK' : 'FAIL';"
```

### **Teste 2: Standalone Usage**
```php
<?php
// test-standalone.php
require_once __DIR__ . '/src/Support/Autoloader.php';
$app = new Coyote\Core\Application(__DIR__);
echo "Standalone OK";
```

### **Teste 3: Development Symlink**
```bash
cd test-app
php -r "require 'vendor/autoload.php'; new Coyote\Core\Application(__DIR__);"
```

### **Teste 4: Performance**
```php
// test-performance.php
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    class_exists('Coyote\\Http\\Request');
}
echo "Time: " . (microtime(true) - $start);
```

## 🗓️ **Cronograma**

| Fase | Duração | Entregáveis |
|------|---------|-------------|
| Análise | 1 hora | Documento de análise, lista de usos |
| Implementação | 2 horas | Autoloader modificado, bootstrap.php |
| Testes | 1 hora | Testes para todos os cenários |
| Documentação | 1 hora | README atualizado, exemplos |
| **Total** | **5 horas** | Solução completa |

## ⚠️ **Riscos e Mitigações**

### **Risco 1: Quebra de Compatibilidade**
- **Mitigação**: Manter backward compatibility com verificação de classe existente
- **Teste**: Executar todos os testes existentes

### **Risco 2: Performance Degradada**
- **Mitigação**: Manter cache de classmap para produção
- **Teste**: Medir performance antes e depois

### **Risco 3: Complexidade Aumentada**
- **Mitigação**: Manter solução simples (Opção 1)
- **Teste**: Validar simplicidade da implementação

## 🔄 **Próximos Passos Imediatos**

1. **Implementar verificação de classe existente** no Autoloader
2. **Testar instalação via Composer** após modificação
3. **Criar bootstrap.php** para uso standalone
4. **Atualizar documentação** com exemplos
5. **Executar testes de validação** completos

## 📈 **Métricas de Sucesso**

1. ✅ Instalação via Composer sem erros de classe duplicada
2. ✅ Uso standalone funcionando com bootstrap.php
3. ✅ Performance igual ou melhor que antes
4. ✅ Todos os testes existentes passando
5. ✅ Documentação clara para ambos os cenários

---

**Status**: Pronto para implementação  
**Prioridade**: Alta (bloqueia publicação no Packagist)  
**Complexidade**: Baixa/Média  
**Impacto**: Crítico para uso como pacote Composer