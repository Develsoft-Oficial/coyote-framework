# Coyote Framework Documentation

![Coyote Framework](https://img.shields.io/badge/PHP-8.1%2B-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Status](https://img.shields.io/badge/Status-Active-brightgreen)

**Coyote** é um framework PHP completo mas extremamente leve, focado em performance, modularidade e simplicidade. Este manual fornece documentação completa para desenvolvedores que desejam utilizar o framework em seus projetos.

## 🚀 Começando

- **[Instalação](getting-started/installation.md)** - Como instalar e configurar o framework
- **[Primeiros Passos](getting-started/quickstart.md)** - Guia rápido para criar sua primeira aplicação
- **[Estrutura do Projeto](getting-started/project-structure.md)** - Organização de diretórios e arquivos

## 📚 Documentação por Módulo

### 🏗️ Núcleo (Core)
- **[Aplicação](core/application.md)** - Classe principal `Application` e inicialização
- **[Container](core/container.md)** - Injeção de dependências (DI Container)
- **[Configuração](core/config.md)** - Gerenciamento de configurações
- **[Service Providers](core/service-providers.md)** - Provedores de serviços

### 🌐 Camada HTTP
- **[Requisição & Resposta](http/request-response.md)** - Manipulação de requisições HTTP
- **[Roteamento](http/routing.md)** - Sistema de rotas e URL generation
- **[Controladores](http/controllers.md)** - Controladores base e REST
- **[Middleware](http/middleware.md)** - Pipeline de middlewares
- **[CSRF Protection](http/csrf.md)** - Proteção contra Cross-Site Request Forgery

### 🗄️ Banco de Dados
- **[Conexão](database/connection.md)** - Gerenciamento de conexões PDO
- **[Query Builder](database/query-builder.md)** - Construtor de queries fluente
- **[Models](database/models.md)** - ORM básico e relacionamentos
- **[Migrations](database/migrations.md)** - Sistema de migrações de banco
- **[Seeders](database/seeders.md)** - População de dados iniciais

### 📝 Formulários
- **[Form Builder](forms/form-builder.md)** - API fluente para criação de formulários
- **[Field Types](forms/field-types.md)** - Tipos de campos disponíveis
- **[Validação de Formulários](forms/validation.md)** - Validação integrada
- **[Renderização](forms/rendering.md)** - Personalização da renderização

### 🔐 Autenticação
- **[Auth Manager](auth/auth-manager.md)** - Gerenciador de autenticação
- **[Guards](auth/guards.md)** - Sistemas de guarda (Session, Token)
- **[User Providers](auth/user-providers.md)** - Provedores de usuários
- **[Middleware de Autenticação](auth/middleware.md)** - Proteção de rotas

### ✅ Validação
- **[Validator](validation/validator.md)** - Sistema de validação de dados
- **[Regras de Validação](validation/rules.md)** - Regras disponíveis
- **[Form Requests](validation/form-requests.md)** - Validação em controladores
- **[Mensagens de Erro](validation/messages.md)** - Personalização de mensagens

### 💾 Sessão & Cookies
- **[Session Manager](session/session-manager.md)** - Gerenciamento de sessões
- **[Session Store](session/store.md)** - Armazenamento de dados de sessão
- **[Cookies](session/cookies.md)** - Manipulação de cookies

### 👁️ Views
- **[View Factory](views/view-factory.md)** - Sistema de templates
- **[Blade-like Syntax](views/syntax.md)** - Sintaxe de templates
- **[Layouts & Components](views/layouts.md)** - Layouts reutilizáveis

### 🧩 Módulos
- **[Module System](modules/module-system.md)** - Sistema de módulos
- **[Criação de Módulos](modules/creating-modules.md)** - Como criar módulos personalizados
- **[Autoloading](modules/autoloading.md)** - Carregamento automático de módulos

### 🖥️ CLI
- **[Console Commands](cli/commands.md)** - Comandos de console disponíveis
- **[Criação de Commands](cli/creating-commands.md)** - Como criar comandos personalizados

## 📖 Referência da API

- **[Índice de Classes](api/class-index.md)** - Lista completa de classes
- **[Métodos Públicos](api/methods.md)** - Documentação de métodos
- **[Exceções](api/exceptions.md)** - Exceções e tratamento de erros

## 🧪 Exemplos Práticos

- **[Blog Completo](examples/blog-tutorial.md)** - Tutorial passo a passo criando um blog
- **[API REST](examples/rest-api.md)** - Criação de uma API RESTful
- **[Autenticação](examples/authentication.md)** - Sistema completo de login/registro
- **[Formulários Avançados](examples/advanced-forms.md)** - Exemplos complexos de formulários
- **[Migrations](examples/migrations-example.md)** - Exemplos de migrações de banco

## 🔧 Ferramentas & Utilitários

- **[Helpers](utilities/helpers.md)** - Funções helper globais
- **[Facades](utilities/facades.md)** - Facades disponíveis
- **[Logging](utilities/logging.md)** - Sistema de logging
- **[Cache](utilities/cache.md)** - Sistema de cache

## 🤝 Contribuindo

- **[Guia de Contribuição](contributing/guide.md)** - Como contribuir para o projeto
- **[Padrões de Código](contributing/code-standards.md)** - Padrões de codificação
- **[Testes](contributing/testing.md)** - Escrevendo testes

## ❓ FAQ & Suporte

- **[Perguntas Frequentes](faq/general.md)** - Perguntas comuns
- **[Solução de Problemas](faq/troubleshooting.md)** - Resolução de problemas
- **[Suporte](faq/support.md)** - Onde obter ajuda

## 📄 Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](../LICENSE) para detalhes.

---

**Última atualização:** 30 de Março de 2026  
**Versão do Framework:** 1.0.0  
**PHP Requerido:** 8.1 ou superior