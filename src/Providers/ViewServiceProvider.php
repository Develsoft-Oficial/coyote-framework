<?php
// vendors/coyote/Providers/ViewServiceProvider.php

namespace Coyote\Providers;

use Coyote\View\ViewFactory;

/**
 * Service Provider para sistema de views
 */
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Registrar serviços
     */
    public function register(): void
    {
        $this->app->singleton('view', function ($app) {
            return new ViewFactory($app);
        });

        $this->app->alias('view', ViewFactory::class);
    }

    /**
     * Inicializar serviços
     */
    public function boot(): void
    {
        // Compartilhar dados com todas as views
        $view = $this->app->make('view');
        
        // Compartilhar instância da aplicação
        $view->share('app', $this->app);
        
        // Compartilhar configurações
        if ($this->app->bound('config')) {
            $config = $this->app->make('config');
            $view->share('config', $config);
        }
        
        // Compartilhar request atual se disponível
        if ($this->app->bound('request')) {
            $request = $this->app->make('request');
            $view->share('request', $request);
        }
    }
}