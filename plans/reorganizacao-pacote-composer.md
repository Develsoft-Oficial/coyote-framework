# Plano de Reorganização para Pacote Composer - Coyote Framework

## 📋 Visão Geral

Este plano descreve a reorganização do Coyote Framework para transformá-lo em um pacote Composer independente, separado da aplicação de exemplo.

## 🎯 Objetivos

1. Separar o framework da aplicação de exemplo
2. Criar estrutura de pacote Composer independente
3. Manter capacidade de desenvolvimento local
4. Preparar para publicação no Packagist
5. Criar sistema de testes como pacote real

## 🔍 Análise da Estrutura Atual

### Estrutura Atual (Monorepo)
```
j:/gdrive/develsoft/coyote/
├── vendors/coyote/          # Framework (pacote)
├── app/                     # Aplicação de exemplo
├── config/                  # Configurações da aplicação
├── routes/                  # Rotas da aplicação
├── public/                  # Assets públicos
├── composer.json           # Configuração atual do pacote
└── test-*.php              # Testes do framework
```

### Problemas Identificados
1. **Mistura de responsabilidades**: Framework e aplicação no mesmo repositório
2. **Autoload complexo**: Sistema personalizado que precisa ser integrado ao Composer
3. **Dificuldade de teste**: Testes não simulam uso real como pacote
4. **Publicação difícil**: Estrutura não otimizada para Packagist

## 🏗️ Arquitetura Proposta

### Estrutura Final (Dois Repositórios)
```
# REPOSITÓRIO 1: Pacote Coyote Framework
coyote-framework/
├── src/                    # Código fonte do framework
│   ├── Core/
│   ├── Http/
│   ├── Database/
│   ├── Auth/
│   ├── Validation/
│   ├── View/
│   └── Support/
├── tests/                  # Testes unitários
├── composer.json          # Configuração do pacote
├── LICENSE
├── README.md
└── CHANGELOG.md

# REPOSITÓRIO 2: Aplicação de Exemplo
coyote-example-app/
├── app/
├── config/
├── routes/
├── public/
├── composer.json          # Depende de coyote/framework
└── vendor/               # Pacote instalado via Composer
```

## 📋 Plano de Migração (Fases)

### Fase 1: Preparação (1-2 dias)
1. **Analisar dependências atuais**
   - Mapear todas as classes e namespaces
   - Identificar arquivos de configuração da aplicação
   - Documentar estrutura de autoload atual

2. **Criar estrutura do pacote**
   - Criar diretório `src/` com estrutura PSR-4
   - Configurar `composer.json` otimizado para pacote
   - Preparar sistema de testes unitários

### Fase 2: Separação do Código (3-4 dias)
1. **Mover código do framework**
   - Transferir `vendors/coyote/` para `src/`
   - Ajustar namespaces para estrutura PSR-4
   - Atualizar autoload para usar Composer padrão

2. **Preservar autoloader personalizado**
   - Manter `Coyote\Autoloader` como classe de otimização
   - Integrar com Composer via scripts post-autoload-dump

3. **Separar configurações da aplicação**
   - Identificar configurações específicas do framework
   - Separar configurações da aplicação de exemplo

### Fase 3: Sistema de Desenvolvimento Local (2-3 dias)
1. **Configurar desenvolvimento symlink**
   ```json
   {
     "repositories": [
       {
         "type": "path",
         "url": "../coyote-framework",
         "options": {
           "symlink": true
         }
       }
     ]
   }
   ```

2. **Criar script de desenvolvimento**
   - Script para linkar pacote localmente
   - Ambiente de desenvolvimento integrado
   - Hot-reload para desenvolvimento

### Fase 4: Testes como Pacote (2-3 dias)
1. **Criar aplicação de teste**
   - Aplicação minimalista que usa o pacote
   - Testar instalação via `composer require`
   - Validar autoload e funcionamento

2. **Testes de integração**
   - Simular cenários reais de uso
   - Testar com diferentes versões do PHP
   - Validar compatibilidade

### Fase 5: Publicação (1-2 dias)
1. **Preparar para Packagist**
   - Otimizar `composer.json`
   - Criar tags de versão
   - Configurar GitHub Actions para CI/CD

2. **Documentação**
   - Atualizar README.md
   - Criar documentação de instalação
   - Exemplos de uso

## 🗂️ Estrutura de Diretórios Detalhada

### Pacote Coyote Framework
```
src/
├── Core/
│   ├── Application.php
│   ├── Container.php
│   └── Config.php
├── Http/
│   ├── Request.php
│   ├── Response.php
│   ├── Kernel.php
│   └── Router.php
├── Database/
│   ├── Connection.php
│   ├── QueryBuilder.php
│   ├── Model.php
│   └── Migrations/
├── Auth/
│   ├── AuthManager.php
│   ├── Guards/
│   └── Contracts/
├── Validation/
│   ├── Validator.php
│   ├── Rules/
│   └── Contracts/
├── View/
│   ├── ViewFactory.php
│   ├── Engines/
│   └── Contracts/
└── Support/
    ├── Facades/
    ├── Helpers.php
    └── ServiceProvider.php
```

### Aplicação de Exemplo
```
example-app/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Middleware/
│   └── Providers/
├── config/
│   ├── app.php
│   ├── database.php
│   └── auth.php
├── routes/
│   ├── web.php
│   └── api.php
├── public/
│   ├── index.php
│   └── assets/
├── storage/
│   ├── logs/
│   ├── cache/
│   └── sessions/
└── vendor/          # coyote/framework instalado aqui
```

## 🔧 Sistema de Autoload para Desenvolvimento

### Opção 1: Symlink (Recomendado)
```json
{
  "require": {
    "coyote/framework": "@dev"
  },
  "repositories": [
    {
      "type": "path",
      "url": "/caminho/para/coyote-framework",
      "options": {
        "symlink": true
      }
    }
  ]
}
```

### Opção 2: Composer Local Repository
```bash
# Na aplicação de exemplo
composer config repositories.coyote path ../coyote-framework
composer require coyote/framework:@dev
```

### Opção 3: Desenvolvimento com Docker
```dockerfile
# Container com pacote montado como volume
VOLUME /var/www/coyote-framework
WORKDIR /var/www/example-app
```

## 🧪 Plano de Testes como Pacote

### Testes Unitários (Framework)
```bash
# No diretório do pacote
composer install
vendor/bin/phpunit
```

### Testes de Integração (Aplicação)
```bash
# Na aplicação de exemplo
composer require ../coyote-framework
php -S localhost:8000 -t public
# Testar funcionalidades
```

### Testes Automatizados
1. **GitHub Actions Workflow**
   - Testes em PHP 8.1, 8.2, 8.3
   - Análise estática com PHPStan
   - Testes de compatibilidade

2. **Testes de Instalação**
   ```bash
   # Script de teste de instalação
   composer create-project coyote/example-app test-install
   cd test-install
   php artisan serve
   ```

## 🚀 Processo de Publicação

### Passo 1: Versionamento
```bash
git tag -a v1.0.0 -m "Primeira versão estável"
git push origin v1.0.0
```

### Passo 2: Packagist
1. Criar conta no Packagist.org
2. Conectar repositório GitHub
3. Configurar webhook para atualizações automáticas

### Passo 3: Documentação
1. Atualizar README.md com:
   - Instalação via Composer
   - Requisitos do sistema
   - Exemplos básicos
   - Link para documentação completa

2. Criar documentação no GitHub Wiki
   - Guia de instalação
   - Configuração
   - Tutorial passo a passo

### Passo 4: Manutenção
1. **Branch de desenvolvimento**: `develop`
2. **Branch principal**: `main` (versões estáveis)
3. **Versionamento semântico**: MAJOR.MINOR.PATCH

## 📊 Cronograma Estimado

| Fase | Duração | Entregáveis |
|------|---------|-------------|
| Preparação | 2 dias | Análise completa, plano detalhado |
| Separação do código | 4 dias | Estrutura do pacote, código movido |
| Desenvolvimento local | 3 dias | Sistema symlink, scripts de dev |
| Testes como pacote | 3 dias | Aplicação de teste, testes de integração |
| Publicação | 2 dias | Packagist, documentação, CI/CD |
| **Total** | **14 dias** | Pacote publicado e funcional |

## ⚠️ Riscos e Mitigações

### Risco 1: Quebra de compatibilidade
- **Mitigação**: Manter suite de testes abrangente
- **Mitigação**: Versão alpha para testes da comunidade

### Risco 2: Complexidade do autoload
- **Mitigação**: Manter autoloader personalizado como opcional
- **Mitigação**: Testar com diferentes cenários de autoload

### Risco 3: Dependências externas
- **Mitigação**: Manter dependências mínimas
- **Mitigação**: Testar com versões diferentes do PHP

## 🎯 Próximos Passos Imediatos

1. **Iniciar Fase 1**: Analisar estrutura atual em detalhes
2. **Criar branch experimental**: `feature/package-structure`
3. **Testar migração parcial**: Mover um módulo como prova de conceito
4. **Validar com equipe**: Revisar plano e ajustar conforme necessário

## 📞 Contato e Suporte

- **Issues**: GitHub Issues no repositório
- **Discussões**: GitHub Discussions para planejamento
- **Documentação**: Wiki do projeto para guias detalhados

---

*Este plano será atualizado conforme o progresso do projeto. Última atualização: 2026-03-30*