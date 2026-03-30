<?php
// vendors/coyote/Providers/ConfigServiceProvider.php

namespace Coyote\Providers;

use Coyote\Core\Config;

/**
 * Service provider para configuração
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Registrar o service provider
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton('config', function ($app) {
            $config = new Config([], $app->configPath());
            
            // Carregar configurações do diretório config
            if (is_dir($app->configPath())) {
                $config->loadFromPath($app->configPath());
            }
            
            return $config;
        });

        $this->app->alias('config', Config::class);
    }

    /**
     * Inicializar o service provider
     */
    public function boot(): void
    {
        parent::boot();

        // Obter configuração
        $config = $this->app->make('config');

        // Definir timezone padrão
        $timezone = $config->get('app.timezone', 'UTC');
        date_default_timezone_set($timezone);

        // Definir locale padrão
        $locale = $config->get('app.locale', 'en');
        if (function_exists('setlocale')) {
            setlocale(LC_ALL, $locale);
        }

        // Definir encoding
        mb_internal_encoding('UTF-8');
    }
}