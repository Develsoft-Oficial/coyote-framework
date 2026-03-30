<?php
// vendors/coyote/Http/Middleware/MiddlewarePipeline.php

namespace Coyote\Http\Middleware;

use Coyote\Http\Request;
use Coyote\Http\Response;
use Coyote\Core\Application;
use Closure;

/**
 * Pipeline de execução de middlewares
 */
class MiddlewarePipeline
{
    /**
     * @var Application Instância da aplicação
     */
    protected $app;

    /**
     * @var array Middlewares a serem executados
     */
    protected $middleware = [];

    /**
     * @var Closure Método final (controller/closure)
     */
    protected $destination;

    /**
     * Criar novo pipeline
     *
     * @param Application $app
     * @param array $middleware
     */
    public function __construct(Application $app, array $middleware = [])
    {
        $this->app = $app;
        $this->middleware = $middleware;
    }

    /**
     * Definir middlewares
     *
     * @param array $middleware
     * @return self
     */
    public function setMiddleware(array $middleware): self
    {
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * Adicionar middleware
     *
     * @param mixed $middleware
     * @return self
     */
    public function addMiddleware($middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Definir destino (controller/closure)
     *
     * @param Closure $destination
     * @return self
     */
    public function setDestination(Closure $destination): self
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * Executar pipeline
     *
     * @param Request $request
     * @param Closure|null $destination
     * @return Response
     */
    public function handle(Request $request, Closure $destination = null): Response
    {
        if ($destination !== null) {
            $this->destination = $destination;
        }

        if (empty($this->middleware)) {
            return $this->callDestination($request);
        }

        $pipeline = array_reduce(
            array_reverse($this->middleware),
            $this->carry(),
            $this->prepareDestination()
        );

        return $pipeline($request);
    }

    /**
     * Preparar closure de destino
     *
     * @return Closure
     */
    protected function prepareDestination(): Closure
    {
        return function ($request) {
            return $this->callDestination($request);
        };
    }

    /**
     * Chamar destino final
     *
     * @param Request $request
     * @return Response
     */
    protected function callDestination(Request $request): Response
    {
        if ($this->destination instanceof Closure) {
            $result = ($this->destination)($request);
            
            if ($result instanceof Response) {
                return $result;
            }
            
            return new Response($result);
        }
        
        throw new \RuntimeException('No destination set for middleware pipeline');
    }

    /**
     * Criar closure para encadear middlewares
     *
     * @return Closure
     */
    protected function carry(): Closure
    {
        return function ($stack, $middleware) {
            return function ($request) use ($stack, $middleware) {
                return $this->callMiddleware($middleware, $request, $stack);
            };
        };
    }

    /**
     * Chamar middleware individual
     *
     * @param mixed $middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    protected function callMiddleware($middleware, Request $request, Closure $next): Response
    {
        // Resolver middleware
        $instance = $this->resolveMiddleware($middleware);
        
        // Verificar se implementa a interface
        if ($instance instanceof MiddlewareInterface) {
            return $instance->handle($request, $next);
        }
        
        // Se for closure, executar diretamente
        if ($instance instanceof Closure) {
            return $instance($request, $next);
        }
        
        // Se for string de classe, tentar chamar método handle
        if (is_string($instance) && class_exists($instance)) {
            $instance = $this->app->make($instance);
            return $instance->handle($request, $next);
        }
        
        throw new \InvalidArgumentException(
            'Middleware must be an instance of MiddlewareInterface, a Closure, or a class name'
        );
    }

    /**
     * Resolver middleware
     *
     * @param mixed $middleware
     * @return mixed
     */
    protected function resolveMiddleware($middleware)
    {
        // Se já for objeto ou closure, retornar
        if (is_object($middleware) || $middleware instanceof Closure) {
            return $middleware;
        }
        
        // Se for string, verificar se é alias
        if (is_string($middleware)) {
            // Verificar se é alias no kernel
            if ($this->app->bound('kernel')) {
                $kernel = $this->app->make('kernel');
                $resolved = $kernel->resolveMiddleware($middleware);
                
                if ($resolved !== $middleware) {
                    $middleware = $resolved;
                }
            }
            
            // Verificar se é closure no container
            if ($this->app->bound($middleware)) {
                return $this->app->make($middleware);
            }
            
            // Verificar se é classe
            if (class_exists($middleware)) {
                return $middleware;
            }
        }
        
        return $middleware;
    }

    /**
     * Obter lista de middlewares
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Limpar middlewares
     */
    public function clear(): void
    {
        $this->middleware = [];
        $this->destination = null;
    }
}