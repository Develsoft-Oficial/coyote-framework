<?php

namespace Coyote\Http\Csrf;

use Coyote\Session\SessionManager;
use Coyote\Config\Repository as ConfigRepository;
use Coyote\Http\Csrf\Exceptions\TokenMismatchException;

class CsrfService
{
    protected $session;
    protected $config;
    protected $tokenName = '_token';
    
    public function __construct(SessionManager $session, ConfigRepository $config)
    {
        $this->session = $session;
        $this->config = $config;
        
        // Load configuration
        $this->tokenName = $config->get('csrf.token_name', '_token');
    }
    
    public function generateToken(): CsrfToken
    {
        $token = bin2hex(random_bytes(32));
        $this->session->put($this->tokenName, $token);
        
        return new CsrfToken($token);
    }
    
    public function getToken(): ?CsrfToken
    {
        $token = $this->session->get($this->tokenName);
        
        return $token ? new CsrfToken($token) : null;
    }
    
    public function token(): string
    {
        $token = $this->getToken();
        
        if (!$token) {
            $token = $this->generateToken();
        }
        
        return $token->getValue();
    }
    
    public function validateToken(string $inputToken): bool
    {
        $storedToken = $this->session->get($this->tokenName);
        
        if (!$storedToken) {
            return false;
        }
        
        // Use hash_equals for timing attack protection
        return hash_equals($storedToken, $inputToken);
    }
    
    public function validateRequest(array $input): bool
    {
        $token = $this->getTokenFromInput($input);
        
        if (!$token) {
            return false;
        }
        
        return $this->validateToken($token);
    }
    
    public function validateOrFail(array $input): void
    {
        if (!$this->validateRequest($input)) {
            throw new TokenMismatchException('CSRF token validation failed');
        }
    }
    
    public function getTokenName(): string
    {
        return $this->tokenName;
    }
    
    public function setTokenName(string $name): self
    {
        $this->tokenName = $name;
        return $this;
    }
    
    protected function getTokenFromInput(array $input): ?string
    {
        // Check POST data first
        $token = $input[$this->tokenName] ?? null;
        
        if ($token) {
            return $token;
        }
        
        // Check GET data
        $token = $_GET[$this->tokenName] ?? null;
        
        if ($token) {
            return $token;
        }
        
        // Check HTTP headers
        $headers = $this->getHeaders();
        foreach ($this->config->get('csrf.headers', ['X-CSRF-TOKEN', 'X-XSRF-TOKEN']) as $header) {
            if (isset($headers[$header])) {
                return $headers[$header];
            }
        }
        
        return null;
    }
    
    protected function getHeaders(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        
        return $headers;
    }
    
    public function clearToken(): void
    {
        $this->session->forget($this->tokenName);
    }
    
    public function regenerateToken(): CsrfToken
    {
        $this->clearToken();
        return $this->generateToken();
    }
}