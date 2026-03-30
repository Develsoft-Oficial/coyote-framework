<?php

namespace Coyote\Auth\Middleware;

use Coyote\Http\Request;
use Coyote\Http\Response;
use Coyote\Auth\AuthManager;

class RedirectIfAuthenticated
{
    /**
     * The authentication manager instance.
     *
     * @var \Coyote\Auth\AuthManager
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Coyote\Auth\AuthManager  $auth
     * @return void
     */
    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Coyote\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, ?string $guard = null)
    {
        if ($this->auth->guard($guard)->check()) {
            return $this->redirectTo($request, $guard);
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are authenticated.
     *
     * @param  \Coyote\Http\Request  $request
     * @param  string|null  $guard
     * @return \Coyote\Http\Response
     */
    protected function redirectTo(Request $request, ?string $guard = null): Response
    {
        // Default redirect path
        $path = '/dashboard';
        
        // You could customize based on guard or user role
        // if ($guard === 'admin') {
        //     $path = '/admin/dashboard';
        // }
        
        return new Response('', 302, ['Location' => $path]);
    }
}