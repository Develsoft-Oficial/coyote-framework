<?php

namespace Coyote\Http\Middleware;

use Coyote\Http\Csrf\CsrfService;
use Coyote\Http\Request;
use Coyote\Http\Response;
use Closure;
use Coyote\Http\Csrf\Exceptions\TokenMismatchException;

class VerifyCsrfToken
{
    protected $csrf;
    protected $except = [];
    
    public function __construct(CsrfService $csrf)
    {
        $this->csrf = $csrf;
        
        // Load excluded URIs from config
        $this->except = config('csrf.except', []);
    }
    
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }
        
        if (!$this->csrf->validateRequest($request->all())) {
            return $this->handleTokenMismatch($request);
        }
        
        return $next($request);
    }
    
    protected function shouldPassThrough(Request $request): bool
    {
        // Skip for read-only methods
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }
        
        // Check except routes
        foreach ($this->except as $except) {
            if ($this->matchesExcept($request, $except)) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function matchesExcept(Request $request, string $except): bool
    {
        $path = $request->path();
        
        // Simple wildcard matching
        if (strpos($except, '*') !== false) {
            $pattern = preg_quote($except, '/');
            $pattern = str_replace('\*', '.*', $pattern);
            return preg_match('/^' . $pattern . '$/', $path);
        }
        
        return $path === $except;
    }
    
    protected function handleTokenMismatch(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'CSRF token mismatch',
                'errors' => ['_token' => 'Invalid CSRF token']
            ], 419);
        }
        
        // For web requests, redirect back with error
        $response = response()->redirectBack();
        $response->withErrors(['_token' => 'The CSRF token is invalid. Please try again.']);
        
        return $response;
    }
    
    public function setExcept(array $except): self
    {
        $this->except = $except;
        return $this;
    }
    
    public function addExcept(string $except): self
    {
        $this->except[] = $except;
        return $this;
    }
}