# Guia Passo a Passo para Publicar no Packagist

## 📋 Pré-requisitos

1. **Conta no GitHub** - Você já tem: `Develsoft-Oficial`
2. **Repositório público** - Já está: `https://github.com/Develsoft-Oficial/coyote-framework`
3. **Conta no Packagist** - Vamos criar agora

## 🚀 Passo 1: Criar Conta no Packagist

### 1.1 Acessar o Packagist
- Abra: https://packagist.org
- Clique em **"Sign up"** (canto superior direito)

### 1.2 Registrar com GitHub (Recomendado)
- Clique no botão **"Login with GitHub"**
- Autorize o Packagist a acessar sua conta GitHub
- **Permissões necessárias:**
  - Acesso a repositórios públicos (já tem)
  - Acesso a informações básicas do perfil

### 1.3 Completar Registro
- Se preferir, pode registrar com email:
  - Email: (seu email)
  - Senha: (crie uma senha)
  - Confirme email clicando no link enviado

## 🚀 Passo 2: Submeter o Pacote

### 2.1 Acessar Dashboard
- Após login, clique em **"Submit"** no menu superior

### 2.2 Inserir URL do Repositório
- Cole a URL: `https://github.com/Develsoft-Oficial/coyote-framework`
- Formato correto: `https://github.com/USUARIO/REPOSITORIO`
- **URL do Coyote Framework:** `https://github.com/Develsoft-Oficial/coyote-framework`

### 2.3 Clique em "Check"
- O Packagist vai:
  - Validar o repositório
  - Ler o `composer.json`
  - Verificar tags Git
  - Mostrar preview do pacote

### 2.4 Revisar Informações
Verifique se as informações estão corretas:

```
Nome do pacote: coyote/framework
Descrição: PHP Micro Framework Leve e Completo
Versão: v2.0.0 (ou v1.0.0)
Repositório: https://github.com/Develsoft-Oficial/coyote-framework
```

### 2.5 Clique em "Submit"
- Pacote será publicado
- Você será redirecionado para a página do pacote

## 🚀 Passo 3: Configurar Automatic Updates (Webhook)

### 3.1 Na Página do Pacote
- Clique no ícone de **engrenagem** (Settings)
- Role até **"GitHub Service Hook"**

### 3.2 Configurar Webhook Automático
- Clique em **"Configure"**
- Você será redirecionado para o GitHub
- Autorize a instalação do **"Packagist" GitHub App**
- Selecione o repositório: `Develsoft-Oficial/coyote-framework`
- Clique em **"Install & Authorize"**

### 3.3 Verificar Configuração
- Volte ao Packagist
- Status deve mostrar: **"Hook installed successfully"**
- Teste criando uma tag:
  ```bash
  git tag v2.0.1
  git push origin --tags
  ```

## 🚀 Passo 4: Testar a Instalação

### 4.1 Criar Projeto de Teste
```bash
# Criar diretório de teste
mkdir test-packagist
cd test-packagist

# Criar composer.json
echo '{
    "require": {
        "coyote/framework": "^2.0"
    }
}' > composer.json

# Instalar
composer install
```

### 4.2 Verificar Instalação
```bash
# Verificar se o pacote foi instalado
ls -la vendor/coyote/framework/

# Testar básico
php -r "require 'vendor/autoload.php'; echo 'Coyote Framework instalado!';"
```

## 🚀 Passo 5: Gerenciar Versões

### 5.1 Criar Nova Versão
```bash
# No diretório do framework
git tag v2.1.0
git push origin --tags
```

### 5.2 Atualizar no Packagist
- **Automático:** Se webhook configurado, atualiza em 1-2 minutos
- **Manual:** Na página do pacote, clique em **"Update"**

### 5.3 Versionamento Semântico
- **MAJOR (3.0.0):** Mudanças incompatíveis
- **MINOR (2.1.0):** Novas funcionalidades compatíveis  
- **PATCH (2.0.1):** Correções de bugs

## 🔧 Solução de Problemas Comuns

### ❌ "Repository not found"
- Verifique se o repositório GitHub está **público**
- URL está correta? `https://github.com/Develsoft-Oficial/coyote-framework`
- Você tem permissão para acessar o repositório

### ❌ "Could not parse composer.json"
- Execute `composer validate` localmente para verificar erros
- Certifique-se de que `composer.json` está no formato JSON correto
- Verifique se há vírgulas extras ou aspas faltando

### ❌ "No valid version found"
- Certifique-se de ter pelo menos uma tag Git
- Tags devem seguir semântica: `v1.0.0`, `v2.0.0`
- Execute: `git tag -l` para listar tags

### ❌ Webhook não funciona
1. **Verificar no GitHub:**
   - Acesse: `https://github.com/Develsoft-Oficial/coyote-framework/settings/hooks`
   - Veja se há webhooks do Packagist
   - Clique no webhook para ver "Recent Deliveries"

2. **Configurar manualmente:**
   - Siga guia em `docs/webhooks-configuration.md`

### ❌ Pacote não aparece nas buscas
- Pode levar alguns minutos para indexação
- Verifique em: `https://packagist.org/packages/coyote/framework`
- Use o link direto do seu pacote

## 📊 Monitoramento

### Verificar Status
- **Packagist:** Página do pacote → "Last synced: X minutes ago"
- **GitHub:** Settings → Webhooks → Status verde
- **Downloads:** Na página do pacote, mostra contador de downloads

### Notificações
- **Packagist:** Perfil → Notification Settings
- **GitHub:** Settings → Notifications → Webhooks

## 🎯 Melhores Práticas

### 1. **Sempre use tags**
```bash
# Antes de publicar mudanças importantes
git tag v2.0.0
git push origin --tags
```

### 2. **Mantenha composer.json atualizado**
```json
{
    "name": "coyote/framework",
    "description": "PHP Micro Framework Leve e Completo",
    "type": "library",
    "license": "MIT",
    "homepage": "https://github.com/Develsoft-Oficial/coyote-framework",
    "require": {
        "php": ">=8.1"
    }
}
```

### 3. **Teste antes de publicar**
```bash
# Validar composer.json
composer validate --strict

# Testar instalação local
composer require ../coyote-framework
```

### 4. **Documente mudanças**
- Use CHANGELOG.md
- Atualize README.md para novas versões
- Documente breaking changes claramente

## 📞 Suporte

### Links Úteis
- **Packagist Documentation:** https://packagist.org/about
- **GitHub Webhooks:** https://docs.github.com/en/webhooks
- **Composer Documentation:** https://getcomposer.org/doc/

### Contato
- **Issues do Framework:** https://github.com/Develsoft-Oficial/coyote-framework/issues
- **Suporte Packagist:** https://packagist.org/about#contact

## ✅ Checklist Final

- [ ] Conta Packagist criada
- [ ] Repositório GitHub público verificado
- [ ] `composer.json` válido
- [ ] Tags Git criadas (v1.0.0, v2.0.0)
- [ ] Pacote submetido no Packagist
- [ ] Webhook configurado
- [ ] Instalação testada via `composer require`
- [ ] README.md atualizado no GitHub

## 🎉 Parabéns!

Seu pacote `coyote/framework` está publicado no Packagist e pode ser instalado por qualquer pessoa no mundo com:

```bash
composer require coyote/framework
```

**Link do seu pacote:** `https://packagist.org/packages/coyote/framework`

Agora você faz parte do ecossistema PHP! 🚀