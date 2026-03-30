# Sistema de Roteamento

## Visão Geral
Sistema de roteamento leve e rápido com suporte a rotas nomeadas, parâmetros, middlewares e grupos.

## Componentes Principais

### 1. Route (Rota Individual)
```php
namespace Coyote\Routing;

class Route
{
    private string $method;
    private string $uri;
    private $action;
    private string $name;
    private array $middleware = [];
    private array $parameters = [];
    private array $wheres = [];
    private ?string $prefix = null;
    private ?string $domain = null;

    public function __construct(string $method, string $uri, $action)
    {
        $this->method = strtoupper($method);
        $this->uri = $this->normalizeUri($uri);
        $this->action = $action;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function middleware($middleware): self
    {
        $this->middleware = array_merge(
            $this->middleware,
            is_array($middleware) ? $middleware : [$middleware]
        );
        return $this;
    }

    public function where(string $parameter, string $pattern): self
    {
        $this->wheres[$parameter] = $pattern;
        return $this;
    }

    public function matches(string $method, string $uri): bool
    {
        if ($this->method !== $method && $this->method !== 'ANY') {
            return false;
        }

        return $this->matchesUri($uri);
    }

    private function matchesUri(string $uri): bool
    {
        $pattern = $this->compilePattern();
        return preg_match($pattern, $this->normalizeUri($uri)) === 1;
    }

    private function compilePattern(): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $this->uri);
        
        foreach ($this->wheres as $param => $regex) {
            $pattern = str_replace(
                "(?P<$param>[^/]+)",
                "(?P<$param>$regex)",
                $pattern
            );
        }
        
        return '#^' . $pattern . '$#';
    }

    public function getParameters(string $uri): array
    {
        $pattern = $this->compilePattern();
        preg_match($pattern, $this->normalizeUri($uri), $matches);
        
        $parameters = [];
        foreach ($this->getParameterNames() as $name) {
            if (isset($matches[$name])) {
                $parameters[$name] = $matches[$name];
            }
        }
        
        return $parameters;
    }

    private function getParameterNames(): array
    {
        preg_match_all('/\{(\w+)\}/', $this->uri, $matches);
        return $matches[1] ?? [];
    }

    private function normalizeUri(string $uri): string
    {
        return '/' . trim($uri, '/');
    }

    // Getters
    public function getMethod(): string { return $this->method; }
    public function getUri(): string { return $this->uri; }
    public function getAction() { return $this->action; }
    public function getName(): ?string { return $this->name; }
    public function getMiddleware(): array { return $this->middleware; }
    public function getParameters(): array { return $this->parameters; }
}
```

### 2. RouteCollection (Coleção de Rotas)
```php
namespace Coyote\Routing;

class RouteCollection
{
    private array $routes = [];
    private array $namedRoutes = [];
    private array $groupStack = [];

    public function add(Route $route): Route
    {
        $this->applyGroupAttributes($route);
        $this->routes[] = $route;

        if ($name = $route->getName()) {
            $this->namedRoutes[$name] = $route;
        }

        return $route;
    }

    public function match(string $method, string $uri): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($method, $uri)) {
                return $route;
            }
        }

        return null;
    }

    public function get(string $uri, $action): Route
    {
        return $this->add(new Route('GET', $uri, $action));
    }

    public function post(string $uri, $action): Route
    {
        return $this->add(new Route('POST', $uri, $action));
    }

    public function put(string $uri, $action): Route
    {
        return $this->add(new Route('PUT', $uri, $action));
    }

    public function patch(string $uri, $action): Route
    {
        return $this->add(new Route('PATCH', $uri, $action));
    }

    public function delete(string $uri, $action): Route
    {
        return $this->add(new Route('DELETE', $uri, $action));
    }

    public function any(string $uri, $action): Route
    {
        return $this->add(new Route('ANY', $uri, $action));
    }

    public function group(array $attributes, \Closure $callback): void
    {
        $this->groupStack[] = $attributes;
        
        $callback($this);
        
        array_pop($this->groupStack);
    }

    private function applyGroupAttributes(Route $route): void
    {
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $route->setPrefix($group['prefix']);
            }
            
            if (isset($group['middleware'])) {
                $route->middleware($group['middleware']);
            }
            
            if (isset($group['domain'])) {
                $route->setDomain($group['domain']);
            }
            
            if (isset($group['name'])) {
                $route->name($group['name'] . '.' . $route->getName());
            }
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRouteByName(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }

    public function url(string $name, array $parameters = []): string
    {
        if (!$route = $this->getRouteByName($name)) {
            throw new \InvalidArgumentException("Route {$name} not found");
        }

        $uri = $route->getUri();
        
        foreach ($parameters as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }

        return $uri;
    }
}
```

### 3. Router (Gerenciador Principal)
```php
namespace Coyote\Routing;

class Router
{
    private RouteCollection $routes;
    private array $patterns = [
        'id' => '\d+',
        'slug' => '[a-z0-9-]+',
        'uuid' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
    ];

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    public function get(string $uri, $action): Route
    {
        return $this->routes->get($uri, $action);
    }

    public function post(string $uri, $action): Route
    {
        return $this->routes->post($uri, $action);
    }

    public function put(string $uri, $action): Route
    {
        return $this->routes->put($uri, $action);
    }

    public function patch(string $uri, $action): Route
    {
        return $this->routes->patch($uri, $action);
    }

    public function delete(string $uri, $action): Route
    {
        return $this->routes->delete($uri, $action);
    }

    public function any(string $uri, $action): Route
    {
        return $this->routes->any($uri, $action);
    }

    public function match(array $methods, string $uri, $action): Route
    {
        $route = new Route('ANY', $uri, $action);
        
        foreach ($methods as $method) {
            $route->setMethod($method);
        }
        
        return $this->routes->add($route);
    }

    public function resource(string $name, string $controller): void
    {
        $this->get("/{$name}", "{$controller}@index")->name("{$name}.index");
        $this->get("/{$name}/create", "{$controller}@create")->name("{$name}.create");
        $this->post("/{$name}", "{$controller}@store")->name("{$name}.store");
        $this->get("/{$name}/{id}", "{$controller}@show")->name("{$name}.show");
        $this->get("/{$name}/{id}/edit", "{$controller}@edit")->name("{$name}.edit");
        $this->put("/{$name}/{id}", "{$controller}@update")->name("{$name}.update");
        $this->patch("/{$name}/{id}", "{$controller}@update");
        $this->delete("/{$name}/{id}", "{$controller}@destroy")->name("{$name}.destroy");
    }

    public function apiResource(string $name, string $controller): void
    {
        $this->get("/{$name}", "{$controller}@index")->name("{$name}.index");
        $this->post("/{$name}", "{$controller}@store")->name("{$name}.store");
        $this->get("/{$name}/{id}", "{$controller}@show")->name("{$name}.show");
        $this->put("/{$name}/{id}", "{$controller}@update")->name("{$name}.update");
        $this->delete("/{$name}/{id}", "{$controller}@destroy")->name("{$name}.destroy");
    }

    public function group(array $attributes, \Closure $callback): void
    {
        $this->routes->group($attributes, $callback);
    }

    public function dispatch(\Coyote\Http\Request $request): \Coyote\Http\Response
    {
        $route = $this->routes->match(
            $request->getMethod(),
            $request->getPathInfo()
        );

        if (!$route) {
            throw new \Coyote\Exceptions\NotFoundException(
                "Route not found for {$request->getMethod()} {$request->getPathInfo()}"
            );
        }

        // Extrair parâmetros da rota
        $parameters = $route->getParameters($request->getPathInfo());
        $request->setRouteParameters($parameters);

        // Executar middlewares da rota
        $middlewarePipeline = new \Coyote\Http\MiddlewarePipeline(
            $route->getMiddleware()
        );

        return $middlewarePipeline->handle($request, function ($request) use ($route, $parameters) {
            return $this->runRoute($route, $request, $parameters);
        });
    }

    private function runRoute(Route $route, \Coyote\Http\Request $request, array $parameters)
    {
        $action = $route->getAction();

        if ($action instanceof \Closure) {
            return $action(...array_values($parameters));
        }

        if (is_string($action)) {
            return $this->runController($action, $parameters);
        }

        throw new \InvalidArgumentException('Invalid route action');
    }

    private function runController(string $action, array $parameters)
    {
        [$controller, $method] = explode('@', $action);
        
        $controllerInstance = $this->container->make($controller);
        
        return $controllerInstance->$method(...array_values($parameters));
    }

    public function getRoutes(): array
    {
        return $this->routes->getRoutes();
    }

    public function url(string $name, array $parameters = []): string
    {
        return $this->routes->url($name, $parameters);
    }

    public function pattern(string $key, string $pattern): void
    {
        $this->patterns[$key] = $pattern;
    }

    public function getPatterns(): array
    {
        return $this->patterns;
    }
}
```

### 4. RouteServiceProvider (Provedor de Serviço)
```php
namespace Coyote\Providers;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('router', function ($app) {
            return new \Coyote\Routing\Router();
        });
    }

    public function boot(): void
    {
        $this->loadRoutes();
    }

    protected function loadRoutes(): void
    {
        $router = $this->app['router'];

        // Carregar rotas da aplicação
        if (file_exists($routesFile = $this->app->basePath('routes/web.php'))) {
            require $routesFile;
        }

        if (file_exists($routesFile = $this->app->basePath('routes/api.php'))) {
            $router->group(['prefix' => 'api'], function () use ($routesFile) {
                require $routesFile;
            });
        }

        // Carregar rotas de módulos
        $this->loadModuleRoutes($router);
    }

    protected function loadModuleRoutes(Router $router): void
    {
        $modules = $this->app['modules']->getActiveModules();
        
        foreach ($modules as $module) {
            $routesFile = $module->getPath() . '/routes.php';
            
            if (file_exists($routesFile)) {
                require $routesFile;
            }
        }
    }
}
```

## Exemplos de Uso

### 1. Definição Básica de Rotas
```php
// routes/web.php
$router = $app['router'];

$router->get('/', 'HomeController@index')->name('home');
$router->get('/about', 'AboutController@index')->name('about');
$router->get('/contact', 'ContactController@show')->name('contact.show');
$router->post('/contact', 'ContactController@store')->name('contact.store');

// Rota com parâmetros
$router->get('/users/{id}', 'UserController@show')->name('users.show');
$router->get('/posts/{slug}', 'PostController@show')->name('posts.show');

// Rota com validação de parâmetro
$router->get('/users/{id}', 'UserController@show')
    ->where('id', '\d+')
    ->name('users.show');

// Múltiplos métodos
$router->match(['GET', 'POST'], '/login', 'AuthController@login');
```

### 2. Grupos de Rotas
```php
$router->group(['prefix' => 'admin', 'middleware' => 'auth'], function ($router) {
    $router->get('/dashboard', 'AdminController@dashboard')->name('admin.dashboard');
    $router->get('/users', 'AdminController@users')->name('admin.users');
    $router->resource('posts', 'AdminPostController');
});

$router->group(['prefix' => 'api/v1', 'middleware' => 'api'], function ($router) {
    $router->apiResource('users', 'Api\UserController');
    $router->apiResource('posts', 'Api\PostController');
});
```

### 3. Rotas de Recursos
```php
// Resource completo (7 rotas)
$router->resource('posts', 'PostController');

// API Resource (5 rotas)
$router->apiResource('users', 'UserController');

// Resource com opções
$router->resource('photos', 'PhotoController')->only(['index', 'show']);
$router->resource('videos', 'VideoController')->except(['destroy']);
```

### 4. Middlewares em Rotas
```php
$router->get('/profile', 'ProfileController@show')
    ->middleware(['auth', 'verified'])
    ->name('profile');

$router->post('/admin/users', 'AdminController@store')
    ->middleware(['auth', 'role:admin']);
```

## Sistema de Cache de Rotas

### 1. Cache de Configuração
```php
class RouteCache
{
    public function cacheRoutes(RouteCollection $routes): void
    {
        $cached = [
            'routes' => serialize($routes),
            'timestamp' => time(),
        ];
        
        file_put_contents(
            $this->getCachePath(),
            '<?php return ' . var_export($cached, true) . ';'
        );
    }

    public function loadCachedRoutes(): ?RouteCollection
    {
        if (!$this->isCached()) {
            return null;
        }
        
        $cached = require $this->getCachePath();
        
        if (time() - $cached['timestamp'] > 3600) {
            // Cache expirado
            return null;
        }
        
        return unserialize($cached['routes']);
    }

    private function getCachePath(): string
    {
        return storage_path('cache/routes.php');
    }

    private function isCached(): bool
    {
        return file_exists($this->getCachePath());
    }
}
```

### 2. Comando CLI para Cache
```php
class RouteCacheCommand extends Command
{
    public function handle(): int
    {
        $this->info('Caching routes...');
        
        $router = $this->app['router'];
        $cache = new RouteCache();
        $cache->cacheRoutes($router->getRoutes());
        
        $this->info('Routes cached successfully!');
        return 0;
    }
}
```

## Performance e Otimização

### 1. Compilação de Padrões
- Pré-compilação de expressões regulares
- Cache de padrões de rota
- Otimização de busca com índice de prefixos

### 2. Busca Hierárquica
```php
class OptimizedRouter extends Router
{
    private array $routeMap = [];

    public function add(Route $route): Route
    {
        parent::add($route);
        
        //