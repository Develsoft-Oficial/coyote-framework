# Plano para Publicação no Packagist

## 📋 **Visão Geral**

Publicar o Coyote Framework no Packagist.org para disponibilizá-lo como pacote Composer público.

## 🎯 **Objetivos**

1. **Publicar pacote** no Packagist como `coyote/framework`
2. **Configurar integração** com GitHub
3. **Estabelecer fluxo** de atualizações automáticas
4. **Garantir qualidade** do pacote publicado
5. **Documentar processo** para manutenção contínua

## 🔍 **Pré-requisitos**

### **1. Contas Necessárias**
- [ ] Conta no [Packagist.org](https://packagist.org) (criar se não existir)
- [ ] Conta no [GitHub](https://github.com) (já temos repositório)
- [ ] Acesso de administrador ao repositório GitHub

### **2. Configurações do Projeto**
- [ ] `composer.json` válido e otimizado ✅
- [ ] README.md completo ✅
- [ ] Licença definida (MIT) ✅
- [ ] Tags Git criadas (v1.0.0) ✅
- [ ] CI/CD configurado ✅
- [ ] Conflito de autoloader resolvido ✅

### **3. Qualidade do Código**
- [ ] Testes básicos funcionando
- [ ] Namespaces PSR-4 corretos ✅
- [ ] Sem erros de sintaxe
- [ ] Documentação de API iniciada

## 🚀 **Passos para Publicação**

### **Passo 1: Preparar Repositório GitHub**
```bash
# 1. Verificar se o repositório está público
# 2. Adicionar descrição: "PHP Micro Framework Leve e Completo"
# 3. Adicionar tópicos: php, framework, micro-framework, web-application
# 4. Configurar README como página inicial
```

### **Passo 2: Criar Conta no Packagist**
1. Acessar https://packagist.org
2. Registrar com conta GitHub (recomendado)
3. Verificar email
4. Acessar dashboard

### **Passo 3: Submeter Pacote**
1. No Packagist, clicar em "Submit"
2. Inserir URL do repositório GitHub: `https://github.com/coyoteframework/framework`
3. Packagist analisará o `composer.json`
4. Revisar informações extraídas:
   - Nome: `coyote/framework`
   - Descrição: "PHP Micro Framework Leve e Completo"
   - Versão: `v1.0.0`
   - Requisitos: PHP 8.1+
5. Confirmar submissão

### **Passo 4: Configurar Integração GitHub**
1. No Packagist, acessar configurações do pacote
2. Habilitar "GitHub Service Hook"
3. Configurar webhook automático:
   - Eventos: Push, Release
   - Atualizar automaticamente: SIM
4. Testar webhook com push de tag

### **Passo 5: Configurar Tokens de Acesso (Opcional)**
```bash
# Para atualizações automáticas via API
# 1. Gerar token no GitHub: Settings > Developer settings > Personal access tokens
# 2. Adicionar ao Packagist: pacote > settings > API tokens
```

## 🔧 **Configurações Avançadas**

### **1. GitHub Actions para Packagist**
```yaml
# .github/workflows/packagist.yml
name: Update Packagist

on:
  release:
    types: [published]

jobs:
  update-packagist:
    runs-on: ubuntu-latest
    steps:
      - name: Update Packagist
        run: |
          curl -X POST https://packagist.org/api/update-package?username=${{ secrets.PACKAGIST_USERNAME }}&apiToken=${{ secrets.PACKAGIST_TOKEN }} \
            -d '{"repository":{"url":"https://github.com/coyoteframework/framework"}}' \
            -H "Content-Type: application/json"
```

### **2. Badges para README**
```markdown
[![Latest Version on Packagist](https://img.shields.io/packagist/v/coyote/framework.svg?style=flat-square)](https://packagist.org/packages/coyote/framework)
[![Total Downloads](https://img.shields.io/packagist/dt/coyote/framework.svg?style=flat-square)](https://packagist.org/packages/coyote/framework)
[![License](https://img.shields.io/packagist/l/coyote/framework.svg?style=flat-square)](LICENSE)
```

### **3. Metadados Otimizados no composer.json**
```json
{
    "keywords": ["framework", "micro-framework", "php", "web", "application"],
    "support": {
        "issues": "https://github.com/coyoteframework/framework/issues",
        "source": "https://github.com/coyoteframework/framework",
        "docs": "https://github.com/coyoteframework/framework/docs"
    }
}
```

## 📊 **Testes Pós-publicação**

### **Teste 1: Instalação via Packagist**
```bash
# Criar projeto de teste
mkdir test-packagist-install
cd test-packagist-install
composer init --no-interaction
composer require coyote/framework
php -r "require 'vendor/autoload.php'; echo class_exists('Coyote\\Core\\Application') ? 'OK' : 'FAIL';"
```

### **Teste 2: Atualizações Automáticas**
```bash
# 1. Criar nova tag
git tag -a v1.0.1 -m "Test update"
git push --tags

# 2. Verificar se Packagist atualizou automaticamente
# 3. Testar instalação da nova versão
composer require coyote/framework:^1.0.1
```

### **Teste 3: Dependências e Conflitos**
```bash
# Testar com outras bibliotecas populares
composer require coyote/framework symfony/http-foundation
# Verificar conflitos
```

## 🗓️ **Cronograma**

| Etapa | Duração | Responsável | Status |
|-------|---------|-------------|--------|
| Preparar repositório GitHub | 30 min | Desenvolvedor | |
| Criar conta Packagist | 15 min | Desenvolvedor | |
| Submeter pacote | 15 min | Desenvolvedor | |
| Configurar webhooks | 20 min | Desenvolvedor | |
| Testar instalação | 30 min | QA | |
| Atualizar documentação | 45 min | Documentador | |
| **Total** | **2h 35min** | | |

## ⚠️ **Riscos e Mitigações**

### **Risco 1: Nome de Pacote Indisponível**
- **Probabilidade**: Baixa
- **Impacto**: Alto
- **Mitigação**: Verificar disponibilidade antes, ter nomes alternativos: `coyote-php/framework`, `coyotefw/framework`

### **Risco 2: Problemas com Webhooks**
- **Probabilidade**: Média
- **Impacto**: Médio
- **Mitigação**: Configurar atualização manual como fallback, monitorar logs

### **Risco 3: Erros no composer.json**
- **Probabilidade**: Baixa
- **Impacto**: Alto
- **Mitigação**: Validar com `composer validate --strict` antes de publicar

### **Risco 4: Dependências Conflitantes**
- **Probabilidade**: Média
- **Impacto**: Médio
- **Mitigação**: Testar com bibliotecas populares antes de publicar

## 📈 **Métricas de Sucesso**

1. ✅ Pacote listado em https://packagist.org/packages/coyote/framework
2. ✅ Instalação funcionando: `composer require coyote/framework`
3. ✅ Atualizações automáticas via webhook
4. ✅ Badges funcionando no README
5. ✅ Downloads registrados no Packagist
6. ✅ Sem issues críticas reportadas

## 🔄 **Manutenção Pós-publicação**

### **1. Atualizações de Versão**
```bash
# Processo para nova versão
1. git tag -a v1.1.0 -m "Nova funcionalidade"
2. git push --tags
3. Packagist atualiza automaticamente via webhook
4. Verificar em packagist.org
```

### **2. Monitoramento**
- **Downloads**: Acompanhar estatísticas no Packagist
- **Issues**: Monitorar GitHub Issues
- **Dependências**: Verificar projetos que usam o framework
- **Security Advisories**: Configurar notificações

### **3. Comunicação**
- **Changelog**: Manter CHANGELOG.md atualizado
- **Releases**: Criar releases no GitHub com notas
- **Twitter/Reddit**: Anunciar versões importantes

## 📚 **Documentação para Usuários**

### **Instalação**
```markdown
## Instalação

### Via Composer
```bash
composer require coyote/framework
```

### Uso Básico
```php
require_once __DIR__ . '/vendor/autoload.php';

use Coyote\Core\Application;

$app = new Application(__DIR__);
// Sua aplicação aqui
```
```

### **Links Importantes**
- Packagist: https://packagist.org/packages/coyote/framework
- GitHub: https://github.com/coyoteframework/framework
- Documentação: https://github.com/coyoteframework/framework/docs
- Issues: https://github.com/coyoteframework/framework/issues

---

**Status**: Pronto para execução  
**Prioridade**: Alta  
**Complexidade**: Baixa  
**Impacto**: Alto (visibilidade pública do framework)