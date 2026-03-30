<?php
// vendors/coyote/Routing/RouteCollection.php

namespace Coyote\Routing;

/**
 * Classe RouteCollection - Coleção de rotas
 */
class RouteCollection
{
    /**
     * @var array Rotas
     */
    protected $routes = [];

    /**
     * @var array Rotas nomeadas
     */
    protected $namedRoutes = [];

    /**
     * @var array Pilha de grupos
     */
    protected $groupStack = [];

    /**
     * Adicionar rota à coleção
     *
     * @param Route $route
     * @return Route
     */
    public function add(Route $route): Route
    {
        $this->applyGroupAttributes($route);
        $this->routes[] = $route;

        if ($name = $route->getName()) {
            $this->namedRoutes[$name] = $route;
        }

        return $route;
    }

    /**
     * Encontrar rota que corresponde à requisição
     *
     * @param string $method
     * @param string $uri
     * @return Route|null
     */
    public function match(string $method, string $uri): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($method, $uri)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Criar rota GET
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function get(string $uri, $action): Route
    {
        return $this->add(new Route('GET', $uri, $action));
    }

    /**
     * Criar rota POST
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function post(string $uri, $action): Route
    {
        return $this->add(new Route('POST', $uri, $action));
    }

    /**
     * Criar rota PUT
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function put(string $uri, $action): Route
    {
        return $this->add(new Route('PUT', $uri, $action));
    }

    /**
     * Criar rota PATCH
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function patch(string $uri, $action): Route
    {
        return $this->add(new Route('PATCH', $uri, $action));
    }

    /**
     * Criar rota DELETE
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function delete(string $uri, $action): Route
    {
        return $this->add(new Route('DELETE', $uri, $action));
    }

    /**
     * Criar rota para qualquer método
     *
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function any(string $uri, $action): Route
    {
        return $this->add(new Route('ANY', $uri, $action));
    }

    /**
     * Criar rota para métodos específicos
     *
     * @param array $methods
     * @param string $uri
     * @param mixed $action
     * @return Route
     */
    public function matchMethods(array $methods, string $uri, $action): Route
    {
        $route = new Route('ANY', $uri, $action);
        
        foreach ($methods as $method) {
            $route->setMethod($method);
        }
        
        return $this->add($route);
    }

    /**
     * Criar grupo de rotas
     *
     * @param array $attributes
     * @param \Closure $callback
     */
    public function group(array $attributes, \Closure $callback): void
    {
        $this->groupStack[] = $attributes;
        
        $callback($this);
        
        array_pop($this->groupStack);
    }

    /**
     * Aplicar atributos do grupo à rota
     *
     * @param Route $route
     */
    protected function applyGroupAttributes(Route $route): void
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
                $currentName = $route->getName();
                if ($currentName) {
                    $route->name($group['name'] . '.' . $currentName);
                }
            }
        }
    }

    /**
     * Obter todas as rotas
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Obter rota pelo nome
     *
     * @param string $name
     * @return Route|null
     */
    public function getRouteByName(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Gerar URL para rota nomeada
     *
     * @param string $name
     * @param array $parameters
     * @return string
     * @throws \InvalidArgumentException
     */
    public function url(string $name, array $parameters = []): string
    {
        if (!$route = $this->getRouteByName($name)) {
            throw new \InvalidArgumentException("Route {$name} not found");
        }

        $uri = $route->getFullUri();
        
        foreach ($parameters as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }

        // Verificar se todos os parâmetros foram substituídos
        if (preg_match('/\{(\w+)\}/', $uri)) {
            throw new \InvalidArgumentException("Missing parameters for route {$name}");
        }

        return $uri;
    }

    /**
     * Obter rotas nomeadas
     *
     * @return array
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }

    /**
     * Contar rotas
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->routes);
    }

    /**
     * Limpar todas as rotas
     */
    public function clear(): void
    {
        $this->routes = [];
        $this->namedRoutes = [];
        $this->groupStack = [];
    }

    /**
     * Obter rotas por método HTTP
     *
     * @param string $method
     * @return array
     */
    public function getRoutesByMethod(string $method): array
    {
        return array_filter($this->routes, function ($route) use ($method) {
            return $route->getMethod() === $method || $route->getMethod() === 'ANY';
        });
    }
}