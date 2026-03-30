<?php
// vendors/coyote/Http/Kernel.php

namespace Coyote\Http;

use Coyote\Routing\Router;
use Coyote\Core\Application;
use Coyote\Core\Exceptions\NotFoundException;

/**
 * Classe Kernel - Núcleo HTTP do framework
 */
class Kernel
{
    /**
     * @var Application Instância da aplicação
     */
    protected $app;

    /**
     * @var Router Instância do router
     */
    protected $router;

    /**
     * @var array Middlewares globais
     */
    protected $middleware = [];

    /**
     * @var array Middlewares por grupo
     */
    protected $middlewareGroups = [];

    /**
     * @var array Aliases de middleware
     */
    protected $middlewareAliases = [];

    /**
     * Criar novo kernel HTTP
     *
     * @param Application $app
     * @param Router $router
     */
    public function __construct(Application $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;
        
        $this->registerDefaultMiddleware();
    }

    /**
     * Registrar middlewares padrão
     */
    protected function registerDefaultMiddleware(): void
    {
        $this->middleware = [
            // Middlewares globais serão adicionados aqui
        ];

        $this->middlewareGroups = [
            'web' => [
                'csrf',
            ],
            'api' => [
                // Middlewares para rotas API
            ],
        ];

        $this->middlewareAliases = [
            'auth' => \Coyote\Http\Middleware\Authenticate::class,
            'guest' => \Coyote\Http\Middleware\RedirectIfAuthenticated::class,
            'csrf' => \Coyote\Http\Middleware\VerifyCsrfToken::class,
        ];
    }

    /**
     * Manipular requisição HTTP
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        try {
            $response = $this->sendRequestThroughRouter($request);
        } catch (\Throwable $e) {
            $response = $this->handleException($request, $e);
        }

        return $response;
    }

    /**
     * Enviar requisição através do router
     *
     * @param Request $request
     * @return Response
     */
    protected function sendRequestThroughRouter(Request $request): Response
    {
        // Inicializar aplicação se necessário
        if (!$this->app->isBooted()) {
            $this->app->boot();
        }

        // Despachar requisição
        return $this->router->dispatch($request);
    }

    /**
     * Manipular exceção
     *
     * @param Request $request
     * @param \Throwable $e
     * @return Response
     */
    protected function handleException(Request $request, \Throwable $e): Response
    {
        // Log da exceção
        if ($this->app->bound('log')) {
            $this->app->make('log')->error('Unhandled exception in HTTP kernel', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => [
                    'method' => $request->getMethod(),
                    'uri' => $request->getUri(),
                    'ip' => $request->getClientIp(),
                ],
            ]);
        }

        // Tratar diferentes tipos de exceção
        if ($e instanceof NotFoundException) {
            return $this->handleNotFound($request, $e);
        }

        if ($e instanceof \Coyote\Core\Exceptions\ContainerException) {
            return $this->handleContainerException($request, $e);
        }

        // Exceção genérica
        return $this->handleGenericException($request, $e);
    }

    /**
     * Manipular erro 404
     *
     * @param Request $request
     * @param NotFoundException $e
     * @return Response
     */
    protected function handleNotFound(Request $request, NotFoundException $e): Response
    {
        if ($request->wantsJson()) {
            return Response::json([
                'error' => 'Not Found',
                'message' => $e->getMessage(),
                'path' => $request->getPathInfo(),
            ], 404);
        }

        return Response::notFound($e->getMessage());
    }

    /**
     * Manipular erro do container
     *
     * @param Request $request
     * @param \Coyote\Core\Exceptions\ContainerException $e
     * @return Response
     */
    protected function handleContainerException(Request $request, \Coyote\Core\Exceptions\ContainerException $e): Response
    {
        if ($request->wantsJson()) {
            return Response::json([
                'error' => 'Service Unavailable',
                'message' => 'An internal error occurred.',
            ], 503);
        }

        return Response::error('Service Unavailable', 503);
    }

    /**
     * Manipular exceção genérica
     *
     * @param Request $request
     * @param \Throwable $e
     * @return Response
     */
    protected function handleGenericException(Request $request, \Throwable $e): Response
    {
        // Em modo debug, mostrar detalhes
        if ($this->app->isDebug()) {
            if ($request->wantsJson()) {
                return Response::json([
                    'error' => 'Internal Server Error',
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace(),
                ], 500);
            }

            // HTML com detalhes
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <title>Error 500 - Internal Server Error</title>
                <style>
                    body { font-family: monospace; margin: 40px; background: #f5f5f5; }
                    .error { background: #fff; border: 1px solid #ddd; border-radius: 5px; padding: 20px; }
                    .error h1 { color: #c00; margin-top: 0; }
                    .error pre { background: #f9f9f9; padding: 10px; border-radius: 3px; overflow: auto; }
                </style>
            </head>
            <body>
                <div class="error">
                    <h1>' . htmlspecialchars(get_class($e)) . '</h1>
                    <p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                    <p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>
                    <h3>Stack Trace:</h3>
                    <pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>
                </div>
            </body>
            </html>';
            
            return Response::html($html, 500);
        }

        // Em produção, mostrar erro genérico
        if ($request->wantsJson()) {
            return Response::json([
                'error' => 'Internal Server Error',
                'message' => 'An unexpected error occurred.',
            ], 500);
        }

        return Response::error('Internal Server Error', 500);
    }

    /**
     * Obter instância da aplicação
     *
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * Obter instância do router
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Adicionar middleware global
     *
     * @param string $middleware
     */
    public function pushMiddleware(string $middleware): void
    {
        if (!in_array($middleware, $this->middleware)) {
            $this->middleware[] = $middleware;
        }
    }

    /**
     * Obter middlewares globais
     *
     * @return array
     */
    public function getGlobalMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Adicionar middleware a grupo
     *
     * @param string $group
     * @param string $middleware
     */
    public function addMiddlewareToGroup(string $group, string $middleware): void
    {
        if (!isset($this->middlewareGroups[$group])) {
            $this->middlewareGroups[$group] = [];
        }

        if (!in_array($middleware, $this->middlewareGroups[$group])) {
            $this->middlewareGroups[$group][] = $middleware;
        }
    }

    /**
     * Obter middlewares por grupo
     *
     * @param string $group
     * @return array
     */
    public function getMiddlewareForGroup(string $group): array
    {
        return $this->middlewareGroups[$group] ?? [];
    }

    /**
     * Registrar alias de middleware
     *
     * @param string $alias
     * @param string $middleware
     */
    public function aliasMiddleware(string $alias, string $middleware): void
    {
        $this->middlewareAliases[$alias] = $middleware;
    }

    /**
     * Resolver middleware pelo alias
     *
     * @param string $alias
     * @return string|null
     */
    public function resolveMiddleware(string $alias): ?string
    {
        return $this->middlewareAliases[$alias] ?? $alias;
    }

    /**
     * Obter todos os aliases de middleware
     *
     * @return array
     */
    public function getMiddlewareAliases(): array
    {
        return $this->middlewareAliases;
    }

    /**
     * Obter todos os grupos de middleware
     *
     * @return array
     */
    public function getMiddlewareGroups(): array
    {
        return $this->middlewareGroups;
    }

    /**
     * Terminar ciclo de vida da requisição
     *
     * @param Request $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response): void
    {
        // Executar terminators se existirem
        // Por enquanto, apenas limpar buffers
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
    }
}