<?php

namespace Coyote\Auth\Middleware;

use Coyote\Http\Request;
use Coyote\Http\Response;
use Coyote\Auth\AuthManager;

class Authenticate
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
        if ($this->auth->guard($guard)->guest()) {
            return $this->unauthenticated($request, $guard);
        }

        return $next($request);
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Coyote\Http\Request  $request
     * @param  string|null  $guard
     * @return \Coyote\Http\Response
     */
    protected function unauthenticated(Request $request, ?string $guard = null): Response
    {
        if ($request->expectsJson()) {
            return new Response('Unauthorized', 401);
        }

        // Redirect to login page
        return new Response('', 302, ['Location' => '/login']);
    }
}