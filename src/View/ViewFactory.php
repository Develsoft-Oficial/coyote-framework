<?php
// vendors/coyote/View/ViewFactory.php

namespace Coyote\View;

use Coyote\Core\Application;

/**
 * Fábrica de views - Gerencia criação e compartilhamento de views
 */
class ViewFactory
{
    /**
     * @var Application Instância da aplicação
     */
    protected $app;

    /**
     * @var string Diretório base das views
     */
    protected $basePath;

    /**
     * @var array Extensões de view suportadas
     */
    protected $extensions = ['.php', '.html', '.phtml'];

    /**
     * @var array Dados compartilhados com todas as views
     */
    protected $shared = [];

    /**
     * @var array Composers registrados
     */
    protected $composers = [];

    /**
     * @var array Namespaces de view
     */
    protected $namespaces = [];

    /**
     * Criar nova fábrica de views
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->basePath = $app->basePath('resources/views');
    }

    /**
     * Criar nova view
     *
     * @param string $view
     * @param array $data
     * @return View
     */
    public function make(string $view, array $data = []): View
    {
        // Mesclar dados compartilhados
        $data = array_merge($this->shared, $data);

        // Resolver view (suporte a namespaces)
        $resolvedView = $this->resolveView($view);

        // Criar instância da view
        $viewInstance = new View($this->app, $resolvedView, $data);

        // Aplicar composers
        $this->applyComposers($viewInstance, $view);

        return $viewInstance;
    }

    /**
     * Renderizar view diretamente
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    public function render(string $view, array $data = []): string
    {
        return $this->make($view, $data)->render();
    }

    /**
     * Verificar se view existe
     *
     * @param string $view
     * @return bool
     */
    public function exists(string $view): bool
    {
        $resolvedView = $this->resolveView($view);
        $viewInstance = new View($this->app, $resolvedView);
        return $viewInstance->exists();
    }

    /**
     * Resolver nome da view para caminho
     *
     * @param string $view
     * @return string
     */
    protected function resolveView(string $view): string
    {
        // Verificar se é namespace (ex: "admin::dashboard")
        if (strpos($view, '::') !== false) {
            [$namespace, $viewName] = explode('::', $view, 2);
            
            if (isset($this->namespaces[$namespace])) {
                $path = $this->namespaces[$namespace] . '/' . str_replace('.', '/', $viewName);
                
                // Verificar extensão
                foreach ($this->extensions as $extension) {
                    $fullPath = $path . $extension;
                    if (file_exists($fullPath)) {
                        return $fullPath;
                    }
                }
                
                // Se não encontrou com extensão, retornar caminho com .php
                return $path . '.php';
            }
        }

        // View normal
        return $view;
    }

    /**
     * Compartilhar dados com todas as views
     *
     * @param string|array $key
     * @param mixed $value
     * @return self
     */
    public function share($key, $value = null): self
    {
        if (is_array($key)) {
            $this->shared = array_merge($this->shared, $key);
        } else {
            $this->shared[$key] = $value;
        }

        return $this;
    }

    /**
     * Registrar composer para view
     *
     * @param string|array $views
     * @param \Closure|string $callback
     * @return self
     */
    public function composer($views, $callback): self
    {
        foreach ((array) $views as $view) {
            $this->composers[$view][] = $callback;
        }

        return $this;
    }

    /**
     * Aplicar composers à view
     *
     * @param View $view
     * @param string $viewName
     */
    protected function applyComposers(View $view, string $viewName): void
    {
        if (!isset($this->composers[$viewName])) {
            return;
        }

        foreach ($this->composers[$viewName] as $composer) {
            if ($composer instanceof \Closure) {
                $composer($view);
            } elseif (is_string($composer) && class_exists($composer)) {
                $instance = $this->app->make($composer);
                if (method_exists($instance, 'compose')) {
                    $instance->compose($view);
                }
            }
        }
    }

    /**
     * Adicionar namespace para views
     *
     * @param string $namespace
     * @param string $path
     * @return self
     */
    public function addNamespace(string $namespace, string $path): self
    {
        $this->namespaces[$namespace] = rtrim($path, '/\\');
        return $this;
    }

    /**
     * Adicionar extensão de view
     *
     * @param string $extension
     * @return self
     */
    public function addExtension(string $extension): self
    {
        if (!in_array($extension, $this->extensions)) {
            $this->extensions[] = $extension;
        }

        return $this;
    }

    /**
     * Obter diretório base das views
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Definir diretório base das views
     *
     * @param string $path
     * @return self
     */
    public function setBasePath(string $path): self
    {
        $this->basePath = $path;
        return $this;
    }

    /**
     * Obter dados compartilhados
     *
     * @return array
     */
    public function getShared(): array
    {
        return $this->shared;
    }

    /**
     * Obter namespaces registrados
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Criar view a partir de string
     *
     * @param string $content
     * @param array $data
     * @return View
     */
    public function fromString(string $content, array $data = []): View
    {
        // Criar arquivo temporário
        $tempFile = tempnam(sys_get_temp_dir(), 'coyote_view_');
        file_put_contents($tempFile, $content);
        
        // Criar view com arquivo temporário
        $view = new View($this->app, $tempFile, array_merge($this->shared, $data));
        
        // Configurar para excluir arquivo temporário após uso
        register_shutdown_function(function () use ($tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        });
        
        return $view;
    }

    /**
     * Obter primeira view existente
     *
     * @param array $views
     * @param array $data
     * @return View
     * @throws \RuntimeException
     */
    public function first(array $views, array $data = []): View
    {
        foreach ($views as $view) {
            if ($this->exists($view)) {
                return $this->make($view, $data);
            }
        }

        throw new \RuntimeException('None of the views exist: ' . implode(', ', $views));
    }
}