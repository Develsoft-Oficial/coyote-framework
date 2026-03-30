# Publicando o Coyote Framework no Packagist

## Pré-requisitos

1. **Conta no GitHub** - O repositório deve estar público
2. **Conta no Packagist** - Registre-se em https://packagist.org
3. **Acesso de administrador** ao repositório GitHub

## Passo a Passo

### 1. Preparar o Repositório GitHub

```bash
# Adicionar remote do GitHub (se ainda não tiver)
git remote add origin https://github.com/Develsoft-Oficial/coyote-framework.git

# Push do código e tags
git push -u origin master
git push --tags
```

### 2. Configurar o Repositório no GitHub

1. Acesse https://github.com/Develsoft-Oficial/coyote-framework
2. Certifique-se de que o repositório está **público**
3. Adicione descrição: "PHP Micro Framework Leve e Completo"
4. Adicione tópicos: `php`, `framework`, `micro-framework`, `web-application`
5. Configure o README.md como página inicial

### 3. Publicar no Packagist

1. Acesse https://packagist.org/login
2. Faça login com sua conta GitHub (recomendado)
3. Clique em "Submit" no menu superior
4. Cole a URL do repositório GitHub: `https://github.com/Develsoft-Oficial/coyote-framework`
5. Clique em "Check" para validar
6. Clique em "Submit" para publicar

### 4. Configurar Webhook Automático (Opcional mas Recomendado)

Para atualizações automáticas quando você fizer push no GitHub:

1. No Packagist, acesse sua página do pacote
2. Clique em "Settings" (engrenagem)
3. Role até "GitHub Service Hook"
4. Clique em "Configure" e siga as instruções
5. Ou manualmente: adicione webhook no GitHub:
   - URL: `https://packagist.org/api/github`
   - Content type: `application/json`
   - Secret: (deixe em branco ou use o token do Packagist)

### 5. Testar a Instalação

Após publicação, teste a instalação do pacote:

```bash
# Criar projeto de teste
mkdir test-packagist-install && cd test-packagist-install

# Criar composer.json
echo '{
    "require": {
        "develsoft/coyote": "^2.0"
    }
}' > composer.json

# Instalar
composer install

# Verificar se funciona
php -r "require 'vendor/autoload.php'; echo 'Coyote Framework instalado com sucesso!';"
```

## Manutenção Contínua

### Atualizando Versões

1. Crie uma nova tag Git:
   ```bash
   git tag v2.0.1
   git push --tags
   ```

2. O Packagist detectará automaticamente se o webhook estiver configurado
3. Caso contrário, clique em "Update" na página do pacote no Packagist

### Versionamento Semântico

- **MAJOR** (2.0.0): Mudanças incompatíveis com versões anteriores
- **MINOR** (2.1.0): Novas funcionalidades compatíveis
- **PATCH** (2.0.1): Correções de bugs compatíveis

## Solução de Problemas

### Erro: "Package not found"
- Verifique se o repositório GitHub está público
- Verifique se a URL está correta no Packagist
- Aguarde alguns minutos após o push

### Erro: "Could not parse version constraint"
- Verifique se as tags Git seguem o padrão semântico (vX.Y.Z)
- Certifique-se de que o composer.json tem "version" no formato correto

### Atualizações não aparecendo
- Clique manualmente em "Update" no Packagist
- Verifique se o webhook está configurado corretamente
- Verifique logs do webhook no GitHub (Settings → Webhooks)

## Links Úteis

- [Packagist Documentation](https://packagist.org/about)
- [Semantic Versioning](https://semver.org)
- [GitHub Webhooks](https://docs.github.com/en/webhooks)
- [Composer Documentation](https://getcomposer.org/doc/)

---

**Status Atual do Coyote Framework:**
- ✅ composer.json configurado corretamente
- ✅ Autoloader PSR-4 funcionando
- ✅ Tag v2.0.0 criada (versão Composer)
- ✅ README.md completo
- ✅ Licença MIT definida
- ⚠️ Necessário: Push para GitHub e publicação no Packagist