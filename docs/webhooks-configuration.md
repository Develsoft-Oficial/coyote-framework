# Configuração de Webhooks GitHub → Packagist

## Visão Geral

Webhooks permitem que o Packagist seja notificado automaticamente quando você fizer push de novas tags ou commits no GitHub, garantindo que o pacote seja atualizado automaticamente.

## Método 1: Configuração Automática (Recomendado)

### Passo a Passo

1. **Acesse o Packagist**
   - Vá para https://packagist.org
   - Faça login com sua conta

2. **Navegue até seu pacote**
   - Clique em "Your Packages" no menu
   - Selecione "coyote/framework"

3. **Configurar Webhook**
   - Clique no ícone de engrenagem (Settings)
   - Role até a seção "GitHub Service Hook"
   - Clique em "Configure"

4. **Autorizar o Packagist**
   - Você será redirecionado para o GitHub
   - Autorize o Packagist a acessar seu repositório
   - Selecione o repositório "Develsoft-Oficial/coyote"
   - Clique em "Install & Authorize"

5. **Verificar Configuração**
   - Volte ao Packagist
   - O status deve mostrar "Hook installed successfully"
   - Teste enviando um push ou criando uma nova tag

## Método 2: Configuração Manual no GitHub

Se o método automático não funcionar, configure manualmente:

### 1. Obter Token do Packagist

1. No Packagist, acesse seu perfil (canto superior direito)
2. Clique em "Show API Token"
3. Copie o token (ex: `abc123def456`)

### 2. Configurar Webhook no GitHub

1. Acesse https://github.com/Develsoft-Oficial/coyote/settings/hooks
2. Clique em "Add webhook"

3. **Configurações do Webhook:**
   - **Payload URL:** `https://packagist.org/api/github`
   - **Content type:** `application/json`
   - **Secret:** (deixe em branco ou use o token do Packagist)
   - **SSL verification:** `Enable SSL verification`
   - **Which events would you like to trigger this webhook?**
     - Selecionar "Let me select individual events"
     - Marcar apenas:
       - ✓ Push
       - ✓ Create (para tags)
       - ✓ Delete (para remover tags)

4. **Configurações Avançadas:**
   - **Active:** ✓ (marcado)
   - **Disable SSL verification:** (desmarcado)

5. Clique em "Add webhook"

### 3. Testar o Webhook

```bash
# Criar uma tag de teste
git tag v2.0.0-test
git push origin v2.0.0-test

# Verificar no GitHub
# 1. Acesse Settings → Webhooks
# 2. Clique no webhook recém-criado
# 3. Role até "Recent Deliveries"
# 4. Verifique se há entregas recentes
```

## Método 3: Usando API do Packagist

Para integração programática:

```bash
# Atualizar pacote via API
curl -XPOST https://packagist.org/api/update-package?token=SEU_TOKEN \
  -d '{"repository":{"url":"https://github.com/Develsoft-Oficial/coyote"}}'

# Ou usando webhook manual
curl -XPOST https://packagist.org/api/github \
  -H "Content-Type: application/json" \
  -d '{
    "repository": {
      "url": "https://github.com/Develsoft-Oficial/coyote",
      "name": "coyote"
    },
    "ref": "refs/tags/v2.0.0"
  }'
```

## Solução de Problemas

### Webhook não está sendo acionado

1. **Verificar permissões:**
   - O repositório deve ser público
   - Você deve ter permissões de administrador

2. **Verificar logs do webhook:**
   - GitHub: Settings → Webhooks → Clique no webhook → Recent Deliveries
   - Procure por entregas com status diferente de 200

3. **Erros comuns:**
   - **403 Forbidden:** Token inválido ou expirado
   - **404 Not Found:** URL do repositório incorreta
   - **422 Unprocessable Entity:** Payload malformado

4. **Testar manualmente:**
   ```bash
   # Redeliver último evento
   # No GitHub, clique em "Redeliver" para testar
   
   # Ou forçar atualização via API
   curl -XPOST https://packagist.org/api/update-package?token=SEU_TOKEN \
     -d '{"repository":{"url":"https://github.com/Develsoft-Oficial/coyote"}}'
   ```

### Packagist não atualiza após push

1. **Verificar se a tag foi criada corretamente:**
   ```bash
   git tag -l
   git show v2.0.0
   ```

2. **Forçar atualização no Packagist:**
   - Acesse https://packagist.org/packages/coyote/framework
   - Clique no botão "Update" (se disponível)
   - Ou use a API como mostrado acima

3. **Aguardar sincronização:**
   - O Packagist pode levar alguns minutos para sincronizar
   - Tags criadas há menos de 5 minutos podem não aparecer ainda

## Configuração de CI/CD com Webhooks

Para integração com GitHub Actions:

```yaml
# .github/workflows/notify-packagist.yml
name: Notify Packagist on Tag

on:
  push:
    tags:
      - 'v*'

jobs:
  notify:
    runs-on: ubuntu-latest
    steps:
      - name: Notify Packagist
        run: |
          curl -XPOST "https://packagist.org/api/update-package?token=${{ secrets.PACKAGIST_TOKEN }}" \
            -d '{"repository":{"url":"https://github.com/Develsoft-Oficial/coyote"}}'
```

## Monitoramento

### Verificar Status do Webhook

1. **GitHub:**
   - Settings → Webhooks → Status verde indica funcionando
   - Clique no webhook para ver "Last delivery"

2. **Packagist:**
   - Página do pacote → "Last synced: X minutes ago"
   - Se mostrar "Never" ou mais de 1 hora, há problema

### Notificações

Configure notificações para falhas de webhook:
- GitHub: Settings → Notifications → Webhooks
- Packagist: Perfil → Notification Settings

## Melhores Práticas

1. **Sempre use tags semânticas:** `v2.0.0`, `v2.1.0`, etc.
2. **Teste webhooks em ambiente de staging** se possível
3. **Monitore falhas** regularmente
4. **Mantenha tokens seguros** usando secrets do GitHub
5. **Documente o processo** para toda a equipe

## Recursos Adicionais

- [Documentação oficial do Packagist sobre webhooks](https://packagist.org/about#how-to-update-packages)
- [Documentação do GitHub sobre webhooks](https://docs.github.com/en/webhooks)
- [API do Packagist](https://packagist.org/apidoc)