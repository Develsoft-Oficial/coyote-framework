<?php
// vendors/coyote/Providers/ServiceProvider.php

namespace Coyote\Providers;

use Coyote\Core\Application;
use Coyote\Core\Container;

/**
 * Classe base para service providers
 */
abstract class ServiceProvider
{
    /**
     * @var Application Instância da aplicação
     */
    protected $app;

    /**
     * @var array Bindings a serem registrados
     */
    public $bindings = [];

    /**
     * @var array Singletons a serem registrados
     */
    public $singletons = [];

    /**
     * @var bool Indica se o provider foi registrado
     */
    protected $registered = false;

    /**
     * @var bool Indica se o provider foi inicializado
     */
    protected $booted = false;

    /**
     * Criar novo service provider
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Registrar o service provider
     */
    public function register(): void
    {
        $this->registered = true;
    }

    /**
     * Inicializar o service provider
     */
    public function boot(): void
    {
        $this->booted = true;
    }

    /**
     * Verificar se o provider foi registrado
     *
     * @return bool
     */
    public function isRegistered(): bool
    {
        return $this->registered;
    }

    /**
     * Verificar se o provider foi inicializado
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
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
     * Registrar bindings
     */
    protected function registerBindings(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    /**
     * Registrar singletons
     */
    protected function registerSingletons(): void
    {
        foreach ($this->singletons as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }
    }

    /**
     * Mesclar configuração
     *
     * @param string $path
     * @param string $key
     */
    protected function mergeConfig(string $path, string $key): void
    {
        $config = $this->app['config']->get($key, []);

        if (file_exists($path)) {
            $this->app['config']->set($key, array_merge(require $path, $config));
        }
    }

    /**
     * Carregar migrations
     *
     * @param string $path
     */
    protected function loadMigrations(string $path): void
    {
        if (method_exists($this->app, 'loadMigrationsFrom')) {
            $this->app->loadMigrationsFrom($path);
        }
    }

    /**
     * Carregar views
     *
     * @param string $path
     * @param string $namespace
     */
    protected function loadViews(string $path, string $namespace): void
    {
        if (method_exists($this->app, 'loadViewsFrom')) {
            $this->app->loadViewsFrom($path, $namespace);
        }
    }

    /**
     * Publicar assets
     *
     * @param array $paths
     * @param string|null $group
     */
    protected function publishes(array $paths, ?string $group = null): void
    {
        if (method_exists($this->app, 'publishes')) {
            $this->app->publishes($paths, $group);
        }
    }

    /**
     * Carregar rotas
     *
     * @param string $path
     */
    protected function loadRoutes(string $path): void
    {
        if (file_exists($path)) {
            require $path;
        }
    }

    /**
     * Carregar comandos
     *
     * @param array $commands
     */
    protected function commands(array $commands): void
    {
        if (method_exists($this->app, 'commands')) {
            $this->app->commands($commands);
        }
    }
}