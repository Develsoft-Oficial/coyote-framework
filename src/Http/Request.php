<?php
// vendors/coyote/Http/Request.php

namespace Coyote\Http;

use ArrayAccess;

/**
 * Classe Request - Manipulação de requisições HTTP
 */
class Request implements ArrayAccess
{
    /**
     * @var array Parâmetros GET
     */
    protected $query;

    /**
     * @var array Parâmetros POST
     */
    protected $request;

    /**
     * @var array Atributos da requisição
     */
    protected $attributes;

    /**
     * @var array Cookies
     */
    protected $cookies;

    /**
     * @var array Arquivos
     */
    protected $files;

    /**
     * @var array Headers
     */
    protected $headers;

    /**
     * @var string Método HTTP
     */
    protected $method;

    /**
     * @var string URI da requisição
     */
    protected $uri;

    /**
     * Criar nova requisição a partir de URI e método
     *
     * @param string $uri
     * @param string $method
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param string $content
     * @return static
     */
    public static function create(
        string $uri,
        string $method = 'GET',
        array $parameters = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null
    ): self {
        // Simular variáveis de servidor
        $server = array_merge([
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $uri,
            'SCRIPT_NAME' => '/index.php',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'HTTP_HOST' => 'localhost',
            'HTTP_USER_AGENT' => 'Coyote-Test/1.0',
        ], $server);

        // Criar instância
        $request = new static();
        
        // Configurar propriedades manualmente (similar a createFromGlobals)
        $request->query = $parameters;
        $request->request = [];
        $request->cookies = $cookies;
        $request->files = $files;
        $request->attributes = [];
        
        // Headers
        $request->headers = [];
        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', strtolower(substr($key, 5)));
                $request->headers[$header] = $value;
            }
        }
        
        // Método HTTP
        $request->method = $server['REQUEST_METHOD'] ?? 'GET';
        
        // URI e Path
        $request->uri = $server['REQUEST_URI'] ?? $uri;
        $request->path = parse_url($request->uri, PHP_URL_PATH) ?? '/';
        
        // Host
        $request->host = $server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? 'localhost';
        
        // IP do cliente
        $request->ip = $server['HTTP_CLIENT_IP']
            ?? $server['HTTP_X_FORWARDED_FOR']
            ?? $server['REMOTE_ADDR']
            ?? '127.0.0.1';
        
        return $request;
    }

    /**
     * @var string Path da requisição
     */
    protected $path;

    /**
     * @var string Host
     */
    protected $host;

    /**
     * @var string IP do cliente
     */
    protected $ip;

    /**
     * @var array Parâmetros da rota
     */
    protected $routeParameters = [];

    /**
     * Criar nova instância de Request a partir de variáveis globais
     *
     * @return self
     */
    public static function createFromGlobals(): self
    {
        $request = new self();
        
        $request->query = $_GET;
        $request->request = $_POST;
        $request->cookies = $_COOKIE;
        $request->files = $_FILES;
        $request->attributes = [];
        
        // Headers
        $request->headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', strtolower(substr($key, 5)));
                $request->headers[$header] = $value;
            }
        }
        
        // Método HTTP
        $request->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Override do método para PUT, PATCH, DELETE
        if ($request->method === 'POST') {
            $method = $request->input('_method', 'POST');
            if (in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE'])) {
                $request->method = strtoupper($method);
            }
        }
        
        // URI e Path
        $request->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $request->path = parse_url($request->uri, PHP_URL_PATH) ?? '/';
        
        // Host
        $request->host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        
        // IP do cliente
        $request->ip = $_SERVER['HTTP_CLIENT_IP'] 
            ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? '127.0.0.1';
        
        return $request;
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
     * Verificar se método é GET
     *
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * Verificar se método é POST
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Verificar se método é PUT
     *
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->method === 'PUT';
    }

    /**
     * Verificar se método é PATCH
     *
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->method === 'PATCH';
    }

    /**
     * Verificar se método é DELETE
     *
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->method === 'DELETE';
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
     * Obter path
     *
     * @return string
     */
    public function getPathInfo(): string
    {
        return $this->path;
    }

    /**
     * Obter host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Obter IP do cliente
     *
     * @return string
     */
    public function getClientIp(): string
    {
        return $this->ip;
    }

    /**
     * Obter parâmetro GET
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Obter todos os parâmetros GET
     *
     * @return array
     */
    public function allQuery(): array
    {
        return $this->query;
    }

    /**
     * Obter parâmetro POST
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, $default = null)
    {
        return $this->request[$key] ?? $default;
    }

    /**
     * Obter todos os parâmetros POST
     *
     * @return array
     */
    public function allInput(): array
    {
        return $this->request;
    }

    /**
     * Obter todos os dados (GET + POST)
     *
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->query, $this->request);
    }

    /**
     * Obter cookie
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Obter header
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function header(string $key, $default = null)
    {
        $key = strtolower(str_replace('_', '-', $key));
        return $this->headers[$key] ?? $default;
    }

    /**
     * Obter todos os headers
     *
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Verificar se é requisição AJAX
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }

    /**
     * Verificar se requisição espera JSON
     *
     * @return bool
     */
    public function wantsJson(): bool
    {
        $accept = $this->header('Accept', '');
        return strpos($accept, 'application/json') !== false 
            || strpos($accept, 'json') !== false;
    }

    /**
     * Verificar se requisição é segura (HTTPS)
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    /**
     * Definir parâmetros da rota
     *
     * @param array $parameters
     */
    public function setRouteParameters(array $parameters): void
    {
        $this->routeParameters = $parameters;
    }

    /**
     * Obter parâmetro da rota
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function route(string $key, $default = null)
    {
        return $this->routeParameters[$key] ?? $default;
    }

    /**
     * Obter todos os parâmetros da rota
     *
     * @return array
     */
    public function routeParameters(): array
    {
        return $this->routeParameters;
    }

    /**
     * Definir atributo
     *
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Obter atributo
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Obter todos os atributos
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Verificar se tem arquivo
     *
     * @param string $key
     * @return bool
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Obter arquivo
     *
     * @param string $key
     * @return array|null
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Obter todos os arquivos
     *
     * @return array
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * Implementação ArrayAccess: offsetExists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->all()[$offset]);
    }

    /**
     * Implementação ArrayAccess: offsetGet
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->all()[$offset] ?? null;
    }

    /**
     * Implementação ArrayAccess: offsetSet
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->request[$offset] = $value;
    }

    /**
     * Implementação ArrayAccess: offsetUnset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->request[$offset], $this->query[$offset]);
    }

    /**
     * Método mágico __get
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * Método mágico __isset
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->all()[$key]);
    }
}