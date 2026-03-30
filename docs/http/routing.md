# Sistema de Roteamento

O sistema de roteamento do Coyote Framework permite definir rotas HTTP de forma declarativa e flexível, com suporte a parâmetros, middlewares, grupos e muito mais.

## 📋 Visão Geral

```php
use Coyote\Routing\Router;

$router = new Router();

// Definir rotas
$router->get('/', 'HomeController@index');
$router->post('/users', 'UserController@store');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@destroy');
```

## 🛣️ Tipos de Rotas

### Rotas Básicas

```php
// Métodos HTTP suportados
$router->get($uri, $action);
$router->post($uri, $action);
$router->put($uri, $action);
$router->patch($uri, $action);
$router->delete($uri, $action);
$router->options($uri, $action);

// Múltiplos métodos
$router->match(['GET', 'POST'], '/users', 'UserController@handle');
$router->any('/api/*', 'ApiController@handle'); // Qualquer método
```

### Parâmetros de Rota

```php
// Parâmetros obrigatórios
$router->get('/users/{id}', 'UserController@show');
$router->get('/posts/{post}/comments/{comment}', 'CommentController@show');

// Parâmetros opcionais
$router->get('/users/{id?}', 'UserController@show');
// Acessível em: /users e /users/123

// Parâmetros com regex
$router->get('/users/{id}', 'UserController@show')
    ->where('id', '[0-9]+');
    
$router->get('/posts/{slug}', 'PostController@show')
    ->where('slug', '[A-Za-z\-]+');
    
// Múltiplas constraints
$router->get('/{type}/{id}', 'ContentController@show')
    ->where([
        'type' => 'post|page|article',
        'id' => '[0-9]+'
    ]);
```

## 🎯 Ações de Rota

### Callbacks

```php
// Closure
$router->get('/', function () {
    return 'Hello World';
});

// Closure com parâmetros
$router->get('/users/{id}', function ($id) {
    return "User ID: {$id}";
});

// Closure com tipo hinting
$router->get('/posts/{post}', function (Post $post) {
    return view('posts.show', compact('post'));
});
```

### Controller Actions

```php
// Sintaxe string
$router->get('/users', 'UserController@index');

// Sintaxe array
$router->get('/users', [UserController::class, 'index']);

// Controller com namespace
$router->get('/admin/users', 'Admin\\UserController@index');

// Resource controller
$router->resource('photos', 'PhotoController');
// Gera: GET /photos, POST /photos, GET /photos/{id}, etc.
```

## 🎪 Grupos de Rotas

### Prefixo

```php
$router->prefix('admin')->group(function ($router) {
    $router->get('/dashboard', 'AdminController@dashboard');
    $router->get('/users', 'AdminController@users');
    // Rotas acessíveis em: /admin/dashboard, /admin/users
});

$router->prefix('api/v1')->group(function ($router) {
    $router->get('/users', 'Api\UserController@index');
    $router->post('/users', 'Api\UserController@store');
});
```

### Middleware

```php
$router->middleware(['auth', 'admin'])->group(function ($router) {
    $router->get('/dashboard', 'DashboardController@index');
    $router->get('/settings', 'SettingsController@index');
});

// Middleware específico para rota
$router->get('/profile', 'ProfileController@index')
    ->middleware('auth');
```

### Namespace

```php
$router->namespace('Admin')->group(function ($router) {
    $router->get('/users', 'UserController@index'); // Admin\UserController
    $router->get('/products', 'ProductController@index'); // Admin\ProductController
});
```

### Domínio

```php
$router->domain('api.example.com')->group(function ($router) {
    $router->get('/users', 'Api\UserController@index');
});

$router->domain('admin.example.com')->group(function ($router) {
    $router->get('/dashboard', 'AdminController@dashboard');
});
```

### Grupos Aninhados

```php
$router->prefix('api')->group(function ($router) {
    $router->middleware('api')->group(function ($router) {
        $router->prefix('v1')->group(function ($router) {
            $router->get('/users', 'Api\V1\UserController@index');
            $router->post('/users', 'Api\V1\UserController@store');
        });
        
        $router->prefix('v2')->group(function ($router) {
            $router->get('/users', 'Api\V2\UserController@index');
        });
    });
});
```

## 🔗 Nomeação de Rotas

```php
// Nomear uma rota
$router->get('/users/profile', 'UserController@profile')
    ->name('profile');

// Nomear com prefixo
$router->name('admin.')->group(function ($router) {
    $router->get('/dashboard', 'DashboardController@index')
        ->name('dashboard'); // Nome: admin.dashboard
});

// Gerar URL pelo nome
$url = route('profile');
$url = route('admin.dashboard');

// Com parâmetros
$router->get('/users/{id}', 'UserController@show')
    ->name('users.show');
    
$url = route('users.show', ['id' => 123]);
$url = route('users.show', 123);

// Em views
<a href="{{ route('profile') }}">Perfil</a>
<a href="{{ route('users.show', $user->id) }}">Ver Usuário</a>
```

## 🛡️ Middleware

### Definir Middleware

```php
// Closure middleware
$router->get('/admin', function () {
    // ...
})->middleware(function ($request, $next) {
    if (!auth()->check()) {
        return redirect('/login');
    }
    return $next($request);
});

// Classe middleware
$router->get('/admin', 'AdminController@index')
    ->middleware(\App\Middleware\AdminMiddleware::class);

// Múltiplos middlewares
$router->get('/dashboard', 'DashboardController@index')
    ->middleware(['auth', 'verified', '2fa']);
```

### Middleware de Parâmetros

```php
// Middleware com parâmetros
$router->get('/posts/{id}', 'PostController@show')
    ->middleware('can:view,post');

// Definir middleware com parâmetros
$router->middleware('role:admin')->group(function ($router) {
    $router->get('/admin', 'AdminController@index');
});
```

## 📝 Rotas Resource

```php
// Resource básico
$router->resource('photos', 'PhotoController');
// Gera:
// GET    /photos              → index
// GET    /photos/create       → create  
// POST   /photos              → store
// GET    /photos/{photo}      → show
// GET    /photos/{photo}/edit → edit
// PUT    /photos/{photo}      → update
// DELETE /photos/{photo}      → destroy

// Resource com opções
$router->resource('photos', 'PhotoController', [
    'only' => ['index', 'show'], // Apenas estas ações
    'except' => ['create', 'store', 'edit', 'update', 'destroy'], // Exceto estas
]);

// API Resource (sem create/edit)
$router->apiResource('photos', 'PhotoController');
// Gera: index, store, show, update, destroy

// Múltiplos resources
$router->resources([
    'photos' => 'PhotoController',
    'videos' => 'VideoController',
]);
```

## 🔄 Fallback Routes

```php
// Rota para 404
$router->fallback(function () {
    return response()->view('errors.404', [], 404);
});

// Ou para controller
$router->fallback('FallbackController@handle');
```

## 🎨 Rotas com Regex Avançado

```php
// Regex em parâmetros
$router->get('/{page}', 'PageController@show')
    ->where('page', 'about|contact|privacy|terms');

// Regex global
$router->pattern('id', '[0-9]+');
$router->pattern('slug', '[a-z0-9\-]+');

// Agora todas as rotas usam estes patterns
$router->get('/posts/{id}', 'PostController@show'); // id deve ser numérico
$router->get('/pages/{slug}', 'PageController@show'); // slug deve ser alfanumérico
```

## 📊 Cache de Rotas

```php
// Cachear rotas (produção)
if (app()->environment('production')) {
    $router->cacheRoutes();
}

// Limpar cache
$router->clearRouteCache();

// Verificar se está em cache
if ($router->routesAreCached()) {
    require $router->getCachedRoutesPath();
} else {
    // Carregar rotas dinamicamente
    require __DIR__ . '/routes/web.php';
}
```

## 🎯 Exemplos Completos

### Exemplo 1: Aplicação Web Completa

```php
<?php
// routes/web.php

use App\Controllers\{
    HomeController,
    UserController,
    PostController,
    CommentController
};

// Rotas públicas
$router->get('/', [HomeController::class, 'index'])->name('home');
$router->get('/about', [HomeController::class, 'about'])->name('about');
$router->get('/contact', [HomeController::class, 'contact'])->name('contact');
$router->post('/contact', [HomeController::class, 'sendContact']);

// Rotas de autenticação
$router->middleware('guest')->group(function ($router) {
    $router->get('/register', [UserController::class, 'create'])->name('register');
    $router->post('/register', [UserController::class, 'store']);
    $router->get('/login', [UserController::class, 'login'])->name('login');
    $router->post('/login', [UserController::class, 'authenticate']);
});

// Rotas protegidas
$router->middleware('auth')->group(function ($router) {
    $router->get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    $router->get('/profile', [UserController::class, 'profile'])->name('profile');
    $router->put('/profile', [UserController::class, 'updateProfile']);
    $router->get('/logout', [UserController::class, 'logout'])->name('logout');
    
    // Posts resource
    $router->resource('posts', PostController::class);
    
    // Comments nested
    $router->post('/posts/{post}/comments', [CommentController::class, 'store']);
    $router->delete('/comments/{comment}', [CommentController::class, 'destroy']);
});

// Admin routes
$router->prefix('admin')->middleware(['auth', 'admin'])->group(function ($router) {
    $router->get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    $router->resource('users', Admin\UserController::class);
    $router->resource('posts', Admin\PostController::class);
    $router->get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
});
```

### Exemplo 2: API RESTful

```php
<?php
// routes/api.php

use App\Controllers\Api\{
    UserController,
    PostController,
    CommentController
};

$router->prefix('api')->middleware('api')->group(function ($router) {
    // API v1
    $router->prefix('v1')->group(function ($router) {
        // Autenticação
        $router->post('/auth/login', [AuthController::class, 'login']);
        $router->post('/auth/register', [AuthController::class, 'register']);
        $router->post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');
        $router->post('/auth/refresh', [AuthController::class, 'refresh']);
        
        // Rotas protegidas
        $router->middleware('auth:api')->group(function ($router) {
            // Users
            $router->get('/users/me', [UserController::class, 'me']);
            $router->apiResource('users', UserController::class);
            
            // Posts
            $router->apiResource('posts', PostController::class);
            
            // Comments
            $router->get('/posts/{post}/comments', [CommentController::class, 'index']);
            $router->post('/posts/{post}/comments', [CommentController::class, 'store']);
            $router->apiResource('comments', CommentController::class)->except(['index']);
        });
        
        // Rotas públicas
        $router->get('/posts', [PostController::class, 'index']);
        $router->get('/posts/{post}', [PostController::class, 'show']);
        $router->get('/posts/{post}/comments', [CommentController::class, 'index']);
    });
    
    // API v2
    $router->prefix('v2')->group(function ($router) {
        // Nova versão da API
        $router->apiResource('users', Api\V2\UserController::class);
    });
});
```

### Exemplo 3: Rotas com Subdomínios

```php
<?php
// routes/console.php

// Rotas para console/CLI
$router->command('make:controller', 'MakeControllerCommand');
$router->command('make:model', 'MakeModelCommand');
$router->command('migrate', 'MigrateCommand');
$router->command('db:seed', 'SeedCommand');
```

## 🔍 Debugging de Rotas

### Listar Todas as Rotas

```bash
# Via CLI
php vendor/bin/coyote route:list

# Output:
# +--------+----------+-----------------+------+---------+
# | Method | URI      | Action          | Name | Middleware |
# +--------+----------+-----------------+------+---------+
# | GET    | /        | HomeController  | home | web      |
# | POST   | /users   | UserController  |      | auth     |
# +--------+----------+-----------------+------+---------+
```

### Testar Rotas

```php
// Em testes
public function test_user_route()
{
    $response = $this->get('/users/123');
    $response->assertStatus(200);
    
    $response = $this->post('/users', ['name' => 'John']);
    $response->assertRedirect('/dashboard');
}

// Testar rotas nomeadas
$this->get(route('users.show', 123))
    ->assertOk();
```

### Log de Rotas

```php
// Habilitar logging de rotas
$router->enableRouteLogging();

// Verificar qual rota foi acessada
if (app()->environment('local')) {
    \Log::info('Rota acessada: ' . request()->path());
}
```

## ⚠️ Boas Práticas

### 1. Organização
- Separe rotas por funcionalidade
- Use grupos para lógica comum
- Mantenha `routes/web.php` para rotas web
- Use `routes/api.php` para rotas API

### 2. Performance
- Cache rotas em produção
- Evite lógica complexa em callbacks de rota
- Use controllers para lógica de negócio

### 3. Segurança
- Aplique middleware apropriado
- Valide parâmetros com regex
- Use HTTPS em produção

### 4. Manutenção
- Nomeie todas as rotas importantes
- Documente rotas complexas
- Mantenha rotas em ordem lógica

## 🔗 Links Relacionados

- [Controladores](controllers.md) - Como criar controllers
- [Middleware](middleware.md) - Sistema de middleware
- [Request & Response](request-response.md) - Manipulação HTTP

---

**Próximo:** [Controladores](controllers.md) ou [Middleware](middleware.md)