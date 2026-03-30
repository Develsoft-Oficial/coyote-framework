<?php
// vendors/coyote/Routing/Router.php

namespace Coyote\Routing;

use Coyote\Http\Request;
use Coyote\Http\Response;

/**
 * Classe Router - Gerenciador principal de roteamento
 */
class Router
{
    /**
     * @var RouteCollection Coleção de rotas
     */
    protected $routes;

    /**
     * @var array Padrões comuns para parâmetros
     */
    protected $patterns = [
        'id' => '\d+',
        'slug' => '[a-z0-9-]+',
        'uuid' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}',
        'hash' => '[a-f0-9]{32}',
        'token' => '[a-zA-Z0-9]{32}',
    ];

    /**
     * @var mixed Container da aplicação
     */
    protected $container;

    /**
     * Criar novo router
     *
     * @param mixed $container
     */
    public function __construct($container = null)
    {
        $this->routes = new RouteCollection();
        $this->container = $container;
    }

    /**
     * Definir container
     *
     * @param mixed $container
     */
    public function setContainer($container): void
    {
        $this->container = $container;
    }

    /**
     * Registrar rota GET
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function get(string $uri, $action): Route
    {
        return $this->routes->get($uri, $action);
    }

    /**
     * Registrar rota POST
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function post(string $uri, $action): Route
    {
        return $this->routes->post($uri, $action);
    }

    /**
     * Registrar rota PUT
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function put(string $uri, $action): Route
    {
        return $this->routes->put($uri, $action);
    }

    /**
     * Registrar rota PATCH
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function patch(string $uri, $action): Route
    {
        return $this->routes->patch($uri, $action);
    }

    /**
     * Registrar rota DELETE
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function delete(string $uri, $action): Route
    {
        return $this->routes->delete($uri, $action);
    }

    /**
     * Registrar rota para qualquer método
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function any(string $uri, $action): Route
    {
        return $this->routes->any($uri, $action);
    }

    /**
     * Registrar rota para métodos específicos
     *
     * @param array $methods
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function match(array $methods, string $uri, $action): Route
    {
        return $this->routes->matchMethods($methods, $uri, $action);
    }

    /**
     * Registrar recurso RESTful
     *
     * @param string $name
     * @param string $controller
     */
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

    /**
     * Registrar recurso API RESTful
     *
     * @param string $name
     * @param string $controller
     */
    public function apiResource(string $name, string $controller): void
    {
        $this->get("/{$name}", "{$controller}@index")->name("{$name}.index");
        $this->post("/{$name}", "{$controller}@store")->name("{$name}.store");
        $this->get("/{$name}/{id}", "{$controller}@show")->name("{$name}.show");
        $this->put("/{$name}/{id}", "{$controller}@update")->name("{$name}.update");
        $this->delete("/{$name}/{id}", "{$controller}@destroy")->name("{$name}.destroy");
    }

    /**
     * Criar grupo de rotas
     *
     * @param array $attributes
     * @param \Closure $callback
     */
    public function group(array $attributes, \Closure $callback): void
    {
        $this->routes->group($attributes, $callback);
    }

    /**
     * Despachar requisição
     *
     * @param Request $request
     * @return Response
     * @throws \Coyote\Core\Exceptions\NotFoundException
     */
    public function dispatch(Request $request): Response
    {
        $route = $this->routes->match(
            $request->getMethod(),
            $request->getPathInfo()
        );

        if (!$route) {
            throw new \Coyote\Core\Exceptions\NotFoundException(
                "Route not found for {$request->getMethod()} {$request->getPathInfo()}"
            );
        }

        // Extrair parâmetros da rota
        $parameters = $route->getParameters($request->getPathInfo());
        $request->setRouteParameters($parameters);

        // Executar ação da rota
        return $this->runRoute($route, $request, $parameters);
    }

    /**
     * Executar ação da rota
     *
     * @param Route $route
     * @param Request $request
     * @param array $parameters
     * @return Response
     */
    protected function runRoute(Route $route, Request $request, array $parameters)
    {
        $action = $route->getAction();

        if ($action instanceof \Closure) {
            return $this->runClosure($action, $request, $parameters);
        }

        if (is_string($action)) {
            return $this->runController($action, $request, $parameters);
        }

        throw new \InvalidArgumentException('Invalid route action');
    }

    /**
     * Executar closure
     *
     * @param \Closure $closure
     * @param Request $request
     * @param array $parameters
     * @return mixed
     */
    protected function runClosure(\Closure $closure, Request $request, array $parameters)
    {
        return $closure(...array_values($parameters));
    }

    /**
     * Executar controller
     *
     * @param string $action
     * @param Request $request
     * @param array $parameters
     * @return mixed
     */
    protected function runController(string $action, Request $request, array $parameters)
    {
        [$controller, $method] = explode('@', $action);
        
        // Resolver controller do container
        if ($this->container) {
            // Verificar se o controller é uma subclasse de Controller
            if (is_subclass_of($controller, 'Coyote\Http\Controllers\Controller')) {
                // Criar controller com injeção de dependências
                $controllerInstance = $this->container->make($controller, [
                    'request' => $request
                ]);
                
                // Usar callAction para suporte a middleware
                return $controllerInstance->callAction($method, array_values($parameters));
            } else {
                // Controller tradicional
                $controllerInstance = $this->container->make($controller);
            }
        } else {
            // Sem container, criar instância diretamente
            if (is_subclass_of($controller, 'Coyote\Http\Controllers\Controller')) {
                $controllerInstance = new $controller(null, $request);
                return $controllerInstance->callAction($method, array_values($parameters));
            } else {
                $controllerInstance = new $controller();
            }
        }
        
        // Verificar se método existe
        if (!method_exists($controllerInstance, $method)) {
            throw new \BadMethodCallException(
                "Method {$method} does not exist on controller {$controller}"
            );
        }
        
        // Chamar método tradicional
        return $controllerInstance->$method($request, ...array_values($parameters));
    }

    /**
     * Obter coleção de rotas
     *
     * @return RouteCollection
     */
    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * Gerar URL para rota nomeada
     *
     * @param string $name
     * @param array $parameters
     * @return string
     */
    public function url(string $name, array $parameters = []): string
    {
        return $this->routes->url($name, $parameters);
    }

    /**
     * Definir padrão para parâmetro
     *
     * @param string $key
     * @param string $pattern
     */
    public function pattern(string $key, string $pattern): void
    {
        $this->patterns[$key] = $pattern;
    }

    /**
     * Obter padrões
     *
     * @return array
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * Carregar rotas de arquivo
     *
     * @param string $file
     */
    public function loadRoutes(string $file): void
    {
        if (file_exists($file)) {
            // Tornar router disponível no escopo do arquivo
            $router = $this;
            require $file;
        }
    }

    /**
     * Obter rotas como array para debug
     *
     * @return array
     */
    public function toArray(): array
    {
        $routes = [];
        
        foreach ($this->routes->getRoutes() as $route) {
            $routes[] = [
                'method' => $route->getMethod(),
                'uri' => $route->getFullUri(),
                'action' => $route->getAction(),
                'name' => $route->getName(),
                'middleware' => $route->getMiddleware(),
            ];
        }
        
        return $routes;
    }
}