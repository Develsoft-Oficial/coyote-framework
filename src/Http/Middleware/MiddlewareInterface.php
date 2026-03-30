<?php
// vendors/coyote/Http/Middleware/MiddlewareInterface.php

namespace Coyote\Http\Middleware;

use Coyote\Http\Request;
use Closure;

/**
 * Interface para middlewares
 */
interface MiddlewareInterface
{
    /**
     * Manipular requisição
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next);
}