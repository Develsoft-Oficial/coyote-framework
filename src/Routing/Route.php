<?php
// vendors/coyote/Routing/Route.php

namespace Coyote\Routing;

/**
 * Classe Route - Definição de uma rota individual
 */
class Route
{
    /**
     * @var string Método HTTP
     */
    protected $method;

    /**
     * @var string URI da rota
     */
    protected $uri;

    /**
     * @var mixed Ação da rota (closure ou string controller@action)
     */
    protected $action;

    /**
     * @var string Nome da rota
     */
    protected $name;

    /**
     * @var array Middlewares da rota
     */
    protected $middleware = [];

    /**
     * @var array Parâmetros da rota
     */
    protected $parameters = [];

    /**
     * @var array Validações de parâmetros (wheres)
     */
    protected $wheres = [];

    /**
     * @var string Prefixo da rota
     */
    protected $prefix;

    /**
     * @var string Domínio da rota
     */
    protected $domain;

    /**
     * Criar nova rota
     *
     * @param string $method
     * @param string $uri
     * @param mixed $action
     */
    public function __construct(string $method, string $uri, $action)
    {
        $this->method = strtoupper($method);
        $this->uri = $this->normalizeUri($uri);
        $this->action = $action;
    }

    /**
     * Normalizar URI
     *
     * @param string $uri
     * @return string
     */
    protected function normalizeUri(string $uri): string
    {
        return '/' . trim($uri, '/');
    }

    /**
     * Definir método HTTP
     *
     * @param string $method
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Definir nome da rota
     *
     * @param string $name
     * @return self
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Adicionar middleware
     *
     * @param mixed $middleware
     * @return self
     */
    public function middleware($middleware): self
    {
        $this->middleware = array_merge(
            $this->middleware,
            is_array($middleware) ? $middleware : [$middleware]
        );
        return $this;
    }

    /**
     * Definir validação de parâmetro
     *
     * @param string $parameter
     * @param string $pattern
     * @return self
     */
    public function where(string $parameter, string $pattern): self
    {
        $this->wheres[$parameter] = $pattern;
        return $this;
    }

    /**
     * Definir múltiplas validações
     *
     * @param array $wheres
     * @return self
     */
    public function whereArray(array $wheres): self
    {
        $this->wheres = array_merge($this->wheres, $wheres);
        return $this;
    }

    /**
     * Definir prefixo
     *
     * @param string $prefix
     * @return self
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Definir domínio
     *
     * @param string $domain
     * @return self
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * Verificar se rota corresponde à requisição
     *
     * @param string $method
     * @param string $uri
     * @return bool
     */
    public function matches(string $method, string $uri): bool
    {
        if ($this->method !== $method && $this->method !== 'ANY') {
            return false;
        }

        return $this->matchesUri($uri);
    }

    /**
     * Verificar se URI corresponde
     *
     * @param string $uri
     * @return bool
     */
    protected function matchesUri(string $uri): bool
    {
        $pattern = $this->compilePattern();
        return preg_match($pattern, $this->normalizeUri($uri)) === 1;
    }

    /**
     * Compilar padrão de regex para a rota
     *
     * @return string
     */
    protected function compilePattern(): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $this->getFullUri());
        
        foreach ($this->wheres as $param => $regex) {
            $pattern = str_replace(
                "(?P<$param>[^/]+)",
                "(?P<$param>$regex)",
                $pattern
            );
        }
        
        return '#^' . $pattern . '$#';
    }

    /**
     * Obter parâmetros da URI
     *
     * @param string $uri
     * @return array
     */
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

    /**
     * Obter nomes dos parâmetros
     *
     * @return array
     */
    protected function getParameterNames(): array
    {
        preg_match_all('/\{(\w+)\}/', $this->uri, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Obter URI completa (com prefixo)
     *
     * @return string
     */
    public function getFullUri(): string
    {
        $uri = $this->uri;
        
        if ($this->prefix) {
            $uri = $this->normalizeUri($this->prefix . $uri);
        }
        
        return $uri;
    }

    /**
     * Obter método HTTP
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Obter URI
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Obter ação
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Obter nome
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Obter middlewares
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Obter parâmetros
     *
     * @return array
     */
    public function getParametersArray(): array
    {
        return $this->parameters;
    }

    /**
     * Obter validações
     *
     * @return array
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * Obter prefixo
     *
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * Obter domínio
     *
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Verificar se ação é closure
     *
     * @return bool
     */
    public function isClosure(): bool
    {
        return $this->action instanceof \Closure;
    }

    /**
     * Verificar se ação é controller
     *
     * @return bool
     */
    public function isController(): bool
    {
        return is_string($this->action) && strpos($this->action, '@') !== false;
    }

    /**
     * Obter controller e método
     *
     * @return array|null
     */
    public function getControllerInfo(): ?array
    {
        if (!$this->isController()) {
            return null;
        }
        
        return explode('@', $this->action, 2);
    }
}