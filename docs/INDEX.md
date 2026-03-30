# 📚 Documentação do Coyote Framework

Bem-vindo à documentação completa do Coyote Framework! Esta documentação está organizada em formato wiki para facilitar a navegação e consulta.

## 🎯 Como Usar Esta Documentação

### Navegação
- Use a **barra lateral** para navegar entre as seções
- **Busque** por termos específicos usando Ctrl+F
- **Siga os links** entre páginas relacionadas

### Estrutura
A documentação está organizada em seções lógicas:

1. **🚀 Começando** - Instalação e primeiros passos
2. **🏗️ Núcleo** - Componentes centrais do framework
3. **🌐 Camada HTTP** - Requisições, rotas, controllers
4. **🗄️ Banco de Dados** - Models, migrations, query builder
5. **📝 Formulários** - Sistema de formulários fluente
6. **🔐 Autenticação** - Sistema de login e permissões
7. **✅ Validação** - Validação de dados
8. **💾 Sessão & Cookies** - Gerenciamento de estado
9. **👁️ Views** - Sistema de templates
10. **🧪 Exemplos** - Tutoriais e exemplos práticos

## 🔍 Busca Rápida

### Para Iniciantes
- **[Instalação](getting-started/installation.md)** - Configure o framework
- **[Guia Rápido](getting-started/quickstart.md)** - Crie sua primeira app em 10min
- **[Estrutura do Projeto](getting-started/project-structure.md)** - Entenda a organização

### Componentes Principais
- **[Application](core/application.md)** - Classe principal do framework
- **[Container](core/container.md)** - Injeção de dependências
- **[Routing](http/routing.md)** - Sistema de rotas
- **[Models](database/models.md)** - ORM e banco de dados
- **[Form Builder](forms/form-builder.md)** - Criação de formulários

### Exemplos Práticos
- **[Blog Completo](examples/blog-tutorial.md)** - Tutorial passo a passo
- **[API REST](examples/rest-api.md)** - Como criar uma API
- **[Autenticação](examples/authentication.md)** - Sistema de login

## 📖 Convenções da Documentação

### Código de Exemplo
```php
// Exemplos sempre incluem contexto completo
use Coyote\Core\Application;

$app = new Application(__DIR__);
```

### Notas Importantes
> **💡 Dica:** Dicas úteis para melhor uso
> 
> **⚠️ Atenção:** Cuidados e precauções
> 
> **🚀 Performance:** Otimizações e boas práticas

### Links Relacionados
- Links para páginas relacionadas aparecem no final de cada seção
- Use `[Texto do Link](caminho/para/pagina.md)` para navegar

## 🆘 Precisa de Ajuda?

### Problemas Comuns
Consulte a seção **[Solução de Problemas](faq/troubleshooting.md)** para:
- Erros de instalação
- Problemas de configuração
- Dúvidas sobre uso

### Comunidade
- **GitHub Issues** - Reporte bugs e sugira melhorias
- **Documentação** - Esta wiki é atualizada regularmente
- **Exemplos** - Veja códigos de exemplo funcionais

## 🔄 Atualizações

Esta documentação é atualizada regularmente. Para verificar a versão:

```bash
# Verifique a versão do framework
php vendor/bin/coyote --version

# Consulte o CHANGELOG para mudanças
cat CHANGELOG.md
```

## 🤝 Contribuindo

Encontrou um erro ou tem uma sugestão?
- **[Guia de Contribuição](contributing/guide.md)** - Como contribuir
- **[Padrões de Código](contributing/code-standards.md)** - Convenções
- **[Testes](contributing/testing.md)** - Garantia de qualidade

---

**Última atualização:** 30 de Março de 2026  
**Versão da Documentação:** 1.0.0  
**Framework Compatível:** Coyote 1.0+

[🏠 Voltar para Home](README.md) | [🚀 Começar Agora](getting-started/installation.md)