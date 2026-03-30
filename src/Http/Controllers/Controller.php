<?php
// vendors/coyote/Http/Controllers/Controller.php

namespace Coyote\Http\Controllers;

use Coyote\Http\Request;
use Coyote\Http\Response;
use Coyote\Core\Application;
use Coyote\Http\Middleware\MiddlewarePipeline;

/**
 * Classe base para controllers
 */
abstract class Controller
{
    /**
     * @var Application Instância da aplicação
     */
    protected $app;

    /**
     * @var Request Requisição atual
     */
    protected $request;

    /**
     * @var array Middlewares para este controller
     */
    protected $middleware = [];

    /**
     * @var array Middlewares para métodos específicos
     */
    protected $middlewareForMethods = [];

    /**
     * Criar novo controller
     *
     * @param Application $app
     * @param Request $request
     */
    public function __construct(Application $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
    }

    /**
     * Executar middleware antes de chamar método
     *
     * @param string $method
     * @param array $parameters
     * @return Response
     */
    public function callAction(string $method, array $parameters): Response
    {
        $middleware = $this->getMiddlewareForMethod($method);

        if (!empty($middleware)) {
            $pipeline = new MiddlewarePipeline($this->app, $middleware);
            
            return $pipeline->handle($this->request, function ($request) use ($method, $parameters) {
                return $this->{$method}(...$parameters);
            });
        }

        return $this->{$method}(...$parameters);
    }

    /**
     * Obter middlewares para método específico
     *
     * @param string $method
     * @return array
     */
    protected function getMiddlewareForMethod(string $method): array
    {
        $middleware = $this->middleware;

        // Adicionar middlewares específicos do método
        if (isset($this->middlewareForMethods[$method])) {
            $middleware = array_merge($middleware, $this->middlewareForMethods[$method]);
        }

        // Remover middlewares excluídos
        if (isset($this->middlewareForMethods['except'][$method])) {
            $middleware = array_diff($middleware, $this->middlewareForMethods['except'][$method]);
        }

        return array_unique($middleware);
    }

    /**
     * Redirecionar para URL
     *
     * @param string $url
     * @param int $status
     * @return Response
     */
    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }

    /**
     * Redirecionar para rota nomeada
     *
     * @param string $name
     * @param array $parameters
     * @param int $status
     * @return Response
     */
    protected function redirectToRoute(string $name, array $parameters = [], int $status = 302): Response
    {
        if ($this->app->bound('router')) {
            $router = $this->app->make('router');
            $url = $router->generate($name, $parameters);
            return $this->redirect($url, $status);
        }

        throw new \RuntimeException('Router not available for route generation');
    }

    /**
     * Retornar resposta JSON
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @return Response
     */
    protected function json($data, int $status = 200, array $headers = []): Response
    {
        return Response::json($data, $status, $headers);
    }

    /**
     * Retornar resposta HTML
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return Response
     */
    protected function html(string $content, int $status = 200, array $headers = []): Response
    {
        return Response::html($content, $status, $headers);
    }

    /**
     * Retornar resposta de erro
     *
     * @param string $message
     * @param int $status
     * @return Response
     */
    protected function error(string $message, int $status = 500): Response
    {
        return Response::error($message, $status);
    }

    /**
     * Retornar resposta 404
     *
     * @param string $message
     * @return Response
     */
    protected function notFound(string $message = 'Not Found'): Response
    {
        return Response::notFound($message);
    }

    /**
     * Retornar view
     *
     * @param string $view
     * @param array $data
     * @return Response
     */
    protected function view(string $view, array $data = []): Response
    {
        if ($this->app->bound('view')) {
            $viewFactory = $this->app->make('view');
            $content = $viewFactory->render($view, $data);
            return $this->html($content);
        }

        // Fallback simples
        $content = "<h1>View: {$view}</h1>";
        if (!empty($data)) {
            $content .= "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";
        }
        return $this->html($content);
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
     * Obter requisição atual
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}