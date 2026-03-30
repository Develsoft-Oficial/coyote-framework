<?php
// vendors/coyote/Http/Response.php

namespace Coyote\Http;

/**
 * Classe Response - Manipulação de respostas HTTP
 */
class Response
{
    /**
     * @var string Conteúdo da resposta
     */
    protected $content;

    /**
     * @var int Status code HTTP
     */
    protected $statusCode;

    /**
     * @var array Headers HTTP
     */
    protected $headers;

    /**
     * @var array Cookies
     */
    protected $cookies;

    /**
     * @var string Versão do protocolo HTTP
     */
    protected $protocolVersion = '1.1';

    /**
     * @var string Charset
     */
    protected $charset = 'UTF-8';

    /**
     * Criar nova instância de Response
     *
     * @param mixed $content
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct($content = '', int $statusCode = 200, array $headers = [])
    {
        $this->setContent($content);
        $this->setStatusCode($statusCode);
        $this->headers = $headers;
        $this->cookies = [];
    }

    /**
     * Definir conteúdo
     *
     * @param mixed $content
     * @return self
     */
    public function setContent($content): self
    {
        if (is_array($content) || is_object($content)) {
            $this->content = json_encode($content);
            $this->setHeader('Content-Type', 'application/json; charset=' . $this->charset);
        } else {
            $this->content = (string) $content;
        }
        
        return $this;
    }

    /**
     * Obter conteúdo
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Definir status code
     *
     * @param int $statusCode
     * @return self
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Obter status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Definir header
     *
     * @param string $key
     * @param string $value
     * @param bool $replace
     * @return self
     */
    public function setHeader(string $key, string $value, bool $replace = true): self
    {
        if ($replace || !isset($this->headers[$key])) {
            $this->headers[$key] = $value;
        }
        
        return $this;
    }

    /**
     * Obter header
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getHeader(string $key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Obter todos os headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Remover header
     *
     * @param string $key
     * @return self
     */
    public function removeHeader(string $key): self
    {
        unset($this->headers[$key]);
        return $this;
    }

    /**
     * Definir cookie
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return self
     */
    public function setCookie(
        string $name,
        string $value = '',
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true
    ): self {
        $this->cookies[$name] = [
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
        ];
        
        return $this;
    }

    /**
     * Remover cookie
     *
     * @param string $name
     * @return self
     */
    public function removeCookie(string $name): self
    {
        unset($this->cookies[$name]);
        return $this;
    }

    /**
     * Criar resposta JSON
     *
     * @param mixed $data
     * @param int $statusCode
     * @param array $headers
     * @return self
     */
    public static function json($data, int $statusCode = 200, array $headers = []): self
    {
        $response = new self($data, $statusCode, $headers);
        $response->setHeader('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    /**
     * Criar resposta de texto
     *
     * @param string $text
     * @param int $statusCode
     * @param array $headers
     * @return self
     */
    public static function text(string $text, int $statusCode = 200, array $headers = []): self
    {
        $response = new self($text, $statusCode, $headers);
        $response->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        return $response;
    }

    /**
     * Criar resposta HTML
     *
     * @param string $html
     * @param int $statusCode
     * @param array $headers
     * @return self
     */
    public static function html(string $html, int $statusCode = 200, array $headers = []): self
    {
        $response = new self($html, $statusCode, $headers);
        $response->setHeader('Content-Type', 'text/html; charset=UTF-8');
        return $response;
    }

    /**
     * Criar redirecionamento
     *
     * @param string $url
     * @param int $statusCode
     * @return self
     */
    public static function redirect(string $url, int $statusCode = 302): self
    {
        $response = new self('', $statusCode);
        $response->setHeader('Location', $url);
        return $response;
    }

    /**
     * Criar resposta de erro
     *
     * @param string $message
     * @param int $statusCode
     * @return self
     */
    public static function error(string $message, int $statusCode = 500): self
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Error ' . $statusCode . '</title>
            <style>
                body { font-family: sans-serif; text-align: center; padding: 50px; }
                h1 { color: #c00; }
            </style>
        </head>
        <body>
            <h1>Error ' . $statusCode . '</h1>
            <p>' . htmlspecialchars($message) . '</p>
        </body>
        </html>';
        
        return self::html($html, $statusCode);
    }

    /**
     * Criar resposta de não encontrado
     *
     * @param string $message
     * @return self
     */
    public static function notFound(string $message = 'Page not found'): self
    {
        return self::error($message, 404);
    }

    /**
     * Criar resposta de acesso negado
     *
     * @param string $message
     * @return self
     */
    public static function forbidden(string $message = 'Access denied'): self
    {
        return self::error($message, 403);
    }

    /**
     * Enviar resposta
     */
    public function send(): void
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    /**
     * Enviar headers
     */
    protected function sendHeaders(): void
    {
        // Status code
        if (headers_sent() === false) {
            http_response_code($this->statusCode);
            
            // Headers
            foreach ($this->headers as $key => $value) {
                header($key . ': ' . $value);
            }
            
            // Cookies
            foreach ($this->cookies as $name => $cookie) {
                setcookie(
                    $name,
                    $cookie['value'],
                    $cookie['expire'],
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httponly']
                );
            }
        }
    }

    /**
     * Enviar conteúdo
     */
    protected function sendContent(): void
    {
        echo $this->content;
    }

    /**
     * Obter resposta como string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Verificar se resposta está vazia
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->content) && $this->statusCode === 204;
    }

    /**
     * Verificar se é redirecionamento
     *
     * @return bool
     */
    public function isRedirect(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400 
            && $this->getHeader('Location') !== null;
    }

    /**
     * Verificar se é sucesso
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Verificar se é erro do cliente
     *
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Verificar se é erro do servidor
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Definir charset
     *
     * @param string $charset
     * @return self
     */
    public function setCharset(string $charset): self
    {
        $this->charset = $charset;
        
        // Atualizar header Content-Type se existir
        if ($contentType = $this->getHeader('Content-Type')) {
            if (preg_match('/charset=\S+/', $contentType)) {
                $this->setHeader('Content-Type', preg_replace('/charset=\S+/', 'charset=' . $charset, $contentType));
            } else {
                $this->setHeader('Content-Type', $contentType . '; charset=' . $charset);
            }
        }
        
        return $this;
    }

    /**
     * Obter charset
     *
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }
}