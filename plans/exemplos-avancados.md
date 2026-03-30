# Plano para Criação de Exemplos Avançados

## 📋 **Visão Geral**

Criar uma coleção de exemplos avançados e tutoriais completos que demonstrem o poder e flexibilidade do Coyote Framework em cenários do mundo real.

## 🎯 **Objetivos**

1. **Demonstrar casos de uso reais** do framework
2. **Fornecer templates** para projetos comuns
3. **Mostrar integrações** com outras bibliotecas
4. **Ilustrar boas práticas** e padrões de design
5. **Facilitar aprendizado** através de exemplos práticos

## 📊 **Situação Atual**

### **Exemplos Existentes**
- `test-app/` - Aplicação básica de teste ✅
- `example-*.php` - Exemplos isolados de funcionalidades ✅
- Falta exemplos completos e tutoriais passo a passo ❌

### **Categorias de Exemplos Necessários**
- **Aplicações completas** (Blog, E-commerce, API REST)
- **Integrações** (Banco de dados, Autenticação, Cache)
- **Padrões de design** (Repository, Service, Factory)
- **Boas práticas** (Testes, Segurança, Performance)

## 🏗️ **Estrutura de Exemplos**

### **Diretório de Exemplos**
```
examples/
├── blog/                    # Blog completo
│   ├── src/                # Código fonte
│   ├── database/           # Migrations e seeders
│   ├── tests/              # Testes do exemplo
│   └── README.md           # Tutorial passo a passo
├── api-rest/               # API RESTful
├── ecommerce/              # Loja virtual básica
├── authentication/         # Sistema de autenticação
├── realtime-chat/          # Chat em tempo real
├── file-upload/            # Upload de arquivos
├── payment-integration/    # Integração com pagamento
├── microservices/          # Arquitetura de microserviços
└── README.md               # Índice de exemplos
```

### **Estrutura Padrão por Exemplo**
```
exemplo-nome/
├── src/                    # Código fonte
│   ├── Controllers/       # Controladores
│   ├── Models/            # Models
│   ├── Services/          # Serviços
│   ├── Middleware/        # Middlewares
│   └── Providers/         # Service providers
├── database/              # Banco de dados
│   ├── migrations/        # Migrations
│   └── seeders/          # Seeders
├── resources/             # Recursos
│   ├── views/            # Templates
│   └── assets/           # CSS, JS, imagens
├── tests/                 # Testes do exemplo
├── config/                # Configurações
├── public/                # Ponto de entrada
│   └── index.php         # Arquivo principal
├── composer.json          # Dependências
├── README.md              # Tutorial
└── .env.example           # Variáveis de ambiente
```

## 🧩 **Exemplos Prioritários**

### **1. Blog Completo (Prioridade Alta)**
**Objetivo**: Demonstrar CRUD completo com relacionamentos
**Funcionalidades**:
- Posts com categorias e tags
- Sistema de comentários
- Autenticação de autores
- Busca e filtros
- Paginação
- Upload de imagens

**Tecnologias**:
- Coyote Framework
- MySQL/SQLite
- Bootstrap para frontend
- Markdown para conteúdo

### **2. API RESTful (Prioridade Alta)**
**Objetivo**: Demonstrar criação de API moderna
**Funcionalidades**:
- Autenticação JWT
- CRUD de recursos
- Paginação e filtros
- Rate limiting
- Documentação OpenAPI/Swagger
- Testes de API

**Endpoints**:
- `GET /api/users` - Listar usuários
- `POST /api/auth/login` - Login
- `GET /api/posts` - Listar posts
- `POST /api/posts` - Criar post
- etc.

### **3. Sistema de Autenticação (Prioridade Média)**
**Objetivo**: Demonstrar autenticação completa
**Funcionalidades**:
- Registro de usuários
- Login/Logout
- Recuperação de senha
- Verificação de email
- Perfil de usuário
- Roles e permissões

**Fluxos**:
- Registro → Verificação → Login → Dashboard
- Esqueci senha → Reset → Login

### **4. E-commerce Básico (Prioridade Média)**
**Objetivo**: Demonstrar aplicação de e-commerce
**Funcionalidades**:
- Catálogo de produtos
- Carrinho de compras
- Checkout
- Histórico de pedidos
- Painel administrativo

**Integrações**:
- Gateway de pagamento (simulado)
- Envio de emails
- Geração de PDF (faturas)

### **5. Chat em Tempo Real (Prioridade Baixa)**
**Objetivo**: Demonstrar WebSockets e realtime
**Funcionalidades**:
- Salas de chat
- Mensagens em tempo real
- Notificações
- Status online/offline

**Tecnologias**:
- Coyote Framework + WebSocket server
- Redis para pub/sub
- Frontend com JavaScript

## 📚 **Formato dos Tutoriais**

### **Estrutura do README.md**
```markdown
# Blog com Coyote Framework

## Visão Geral
Crie um blog completo com sistema de posts, categorias, comentários e autenticação.

## Pré-requisitos
- PHP 8.1+
- Composer
- MySQL ou SQLite

## Passo 1: Configuração Inicial
```bash
composer create-project coyote/framework-example-blog my-blog
cd my-blog
cp .env.example .env
```

## Passo 2: Configurar Banco de Dados
```php
// config/database.php
return [
    'default' => env('DB_CONNECTION', 'sqlite'),
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ],
    ],
];
```

## Passo 3: Executar Migrations
```bash
php vendor/bin/coyote migrate
```

## Passo 4: Criar Primeiro Post
```php
// Em seu controller
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ]);
    
    $post = Post::create($validated);
    
    return redirect()->route('posts.show', $post);
}
```

## Funcionalidades Implementadas
- ✅ CRUD de posts
- ✅ Sistema de comentários
- ✅ Autenticação de autores
- ✅ Upload de imagens
- ✅ Busca e filtros

## Estrutura do Projeto
```
src/
├── Controllers/
│   ├── PostController.php
│   ├── CommentController.php
│   └── AuthController.php
├── Models/
│   ├── Post.php
│   ├── Comment.php
│   └── User.php
└── ...
```

## Próximos Passos
1. Adicionar sistema de tags
2. Implementar cache para melhor performance
3. Adicionar RSS feed
4. Criar API para aplicativos móveis

## Recursos Adicionais
- [Documentação do Coyote Framework](https://coyoteframework.org/docs)
- [Código fonte completo](https://github.com/coyoteframework/examples/blog)
- [Vídeo tutorial](https://youtube.com/...)
```

### **Código Comentado e Explicado**
```php
<?php
// examples/blog/src/Controllers/PostController.php

namespace App\Controllers;

use Coyote\Http\Controller;
use Coyote\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Exibe lista de posts com paginação
     * 
     * A paginação é feita automaticamente pelo framework
     * com 15 itens por página por padrão.
     */
    public function index()
    {
        // Busca posts ordenados por data de criação (mais recentes primeiro)
        $posts = Post::latest()->paginate();
        
        // Retorna view com os posts
        return view('posts.index', compact('posts'));
    }
    
    /**
     * Armazena um novo post no banco de dados
     * 
     * Valida os dados de entrada usando Form Request
     * e redireciona para o post criado.
     */
    public function store(StorePostRequest $request)
    {
        // Cria post com dados validados
        $post = Post::create($request->validated());
        
        // Flash message para feedback ao usuário
        session()->flash('success', 'Post criado com sucesso!');
        
        // Redireciona para o post criado
        return redirect()->route('posts.show', $post);
    }
}
```

## 🛠️ **Ferramentas para Desenvolvimento de Exemplos**

### **Script de Criação de Exemplo**
```bash
#!/bin/bash
# scripts/create-example.sh

EXAMPLE_NAME=$1
EXAMPLE_DIR="examples/$EXAMPLE_NAME"

echo "Criando exemplo: $EXAMPLE_NAME"
mkdir -p $EXAMPLE_DIR/{src,database/migrations,database/seeders,resources/views,resources/assets,tests,config,public}

# Copiar template básico
cp templates/example-composer.json $EXAMPLE_DIR/composer.json
cp templates/example-env $EXAMPLE_DIR/.env.example
cp templates/example-readme.md $EXAMPLE_DIR/README.md

echo "Exemplo criado em: $EXAMPLE_DIR"
```

### **Template de composer.json para Exemplos**
```json
{
    "name": "coyote/example-blog",
    "description": "Exemplo de blog completo com Coyote Framework",
    "type": "project",
    "require": {
        "coyote/framework": "^1.0",
        "ext-pdo": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

## 📈 **Plano de Implementação**

### **Fase 1: Blog Completo (2 semanas)**
1. Estrutura básica do blog
2. CRUD de posts e categorias
3. Sistema de comentários
4. Autenticação de autores
5. Frontend com Bootstrap
6. Testes e documentação

### **Fase 2: API RESTful (1.5 semanas)**
1. Estrutura de API
2. Autenticação JWT
3. CRUD de recursos
4. Documentação OpenAPI
5. Testes de API

### **Fase 3: Autenticação (1 semana)**
1. Sistema de registro/login
2. Recuperação de senha
3. Verificação de email
4. Perfil de usuário

### **Fase 4: E-commerce (2 semanas)**
1. Catálogo de produtos
2. Carrinho de compras
3. Checkout simulado
4. Painel administrativo

### **Fase 5: Outros Exemplos (2 semanas)**
1. Chat em tempo real
2. Upload de arquivos
3. Microserviços
4. Integrações diversas

## 🧪 **Testes nos Exemplos**

### **Testes Automatizados**
```php
// examples/blog/tests/Feature/PostTest.php
class PostTest extends TestCase
{
    public function test_user_can_create_post()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
             ->post('/posts', [
                 'title' => 'Test Post',
                 'content' => 'Test content'
             ])
             ->assertRedirect()
             ->assertSessionHas('success');
             
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post'
        ]);
    }
}
```

### **Testes de API**
```php
// examples/api-rest/tests/Feature/ApiTest.php
class ApiTest extends TestCase
{
    public function test_api_returns_posts()
    {
        $response = $this->getJson('/api/posts');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'title', 'content']
                     ],
                     'links',
                     'meta'
                 ]);
    }
}
```

## 🌐 **Hospedagem e Demonstração**

### **GitHub Pages para Demonstração**
```yaml
# .github/workflows/deploy-examples.yml
name: Deploy Examples

on:
  push:
    branches: [main]
    paths: ['examples/**']

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Build Examples
        run: |
          cd examples/blog
          composer install --no-dev
          npm install
          npm run build
      - name: Deploy to GitHub Pages
        uses: peaceiris/actions-gh-pages@v3
        with:
          github_token: ${{ secrets.GITHUB_TOKEN }}
          publish_dir: examples/blog/public
```

### **Demo Online**
- Blog: https://coyoteframework.github.io/examples/blog
- API: https://api.coyoteframework.org/examples/api
- E-commerce: https://coyoteframework.github.io/examples/ecommerce

## 📊 **Métricas de Sucesso**

### **Qualidade dos Exemplos**
- ✅ Código funcionando sem erros
- ✅ Documentação clara e completa
- ✅ Testes passando
- ✅ Boas práticas aplicadas
- ✅ Performance adequada

### **Adoção pelos Desenvolvedores**
- ⭐ Estrelas no repositório de exemplos
- 📊 Downloads dos exemplos via Composer
- 💬 Feedback positivo da comunidade
- 🔗 Referências em tutoriais externos

### **Impacto no Framework**
- 📈 Aumento de downloads do pacote principal
- 🎯 Redução de issues de "como fazer X"
- 🤝 Contribuições da comunidade baseadas nos exemplos
- 📚 Melhoria na documentação baseada em feedback

## ⚠️ **Riscos e Mitigações**

### **Risco 1: Exemplos Desatualizados**
- **Problema**: Exemplos não funcionam com novas versões
- **Mitigação**: Testar exemplos em cada release
- **Solução**: CI/CD para validar exemplos automaticamente

### **Risco 2: Complexidade Excessiva**
- **Problema**: Exemplos muito complexos para iniciantes
- **Mitigação**: Criar exemplos progressivos (básico → avançado)
- **Solução**: Níveis de dificuldade claramente marcados

### **Risco 3: Manutenção Cansativa**
- **Problema**: Muitos exemplos para manter
- **Mitigação**: Focar em exemplos principais primeiro
- **Solução**: Comunidade ajudar na manutenção

### **Risco 4: Falta de Diversidade**
- **Problema**: Exemplos muito similares entre si
- **Mitigação**: Cobrir diferentes casos de uso
- **Solução**: Pesquisar necessidades da comunidade

## 📅 **Cronograma Detalhado**

| Período | Exemplo | Entregáveis | Horas |
|---------|---------|-------------|-------|
| Semanas 1-2 | Blog Completo | CRUD, auth, comments, frontend | 40 |
| Semanas 3-4 | API RESTful | JWT, endpoints, docs, tests | 30 |
| Semana 5 | Autenticação | Register, login, recovery, profile | 20 |
| Semanas 6-7 | E-commerce | Products, cart, checkout, admin | 40 |
| Semana 8 | Diversos | Chat, upload, microservices | 30 |
| **Total** | | | **160 horas** |

## 🔗 **Integração com Documentação**

### **Links Cruzados**
```markdown
# Na documentação da API:
> **Exemplo prático**: Veja como implementar um blog completo no [exemplo de blog](https://github.com/coyoteframework/examples/blog).

# Nos exemplos:
> **Documentação relacionada**: Consulte a [documentação de rotas](https://coyoteframework.org/docs/http/routing) para mais detalhes.
```

### **Geração Automática de Exemplos**
```bash
# Script para extrair exemplos da documentação
php scripts/extract-examples.php docs/api/Core/Application.md
# Gera: examples/from-docs/application-basic-usage.php
```

---

**Status**: Pronto para implementação  
**Prioridade**: Alta (mostra valor do framework)  
**Complexidade**: Alta (projetos completos)  
**Impacto**: Crítico (adoção por desenvolvedores)