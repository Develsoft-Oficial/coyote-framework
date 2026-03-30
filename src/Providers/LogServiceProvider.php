<?php
// vendors/coyote/Providers/LogServiceProvider.php

namespace Coyote\Providers;

/**
 * Service provider para logging
 */
class LogServiceProvider extends ServiceProvider
{
    /**
     * Registrar o service provider
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton('log', function ($app) {
            // Usar o caminho de storage da aplicação
            $logFile = $app->storagePath('logs/coyote.log');
            return new \Coyote\Log\Logger($logFile);
        });
    }

    /**
     * Inicializar o service provider
     */
    public function boot(): void
    {
        parent::boot();

        // Configurar handler de exceções
        $this->configureExceptionHandling();

        // Configurar handler de erros
        $this->configureErrorHandling();

        // Configurar handler de shutdown
        $this->configureShutdownHandling();
    }

    /**
     * Configurar tratamento de exceções
     */
    protected function configureExceptionHandling(): void
    {
        set_exception_handler(function ($exception) {
            if ($this->app->bound('log')) {
                $this->app->make('log')->error('Uncaught exception', [
                    'exception' => $exception,
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ]);
            }

            if ($this->app->isDebug()) {
                throw $exception;
            }

            // Em produção, mostrar erro genérico
            http_response_code(500);
            echo 'An error occurred. Please try again later.';
        });
    }

    /**
     * Configurar tratamento de erros
     */
    protected function configureErrorHandling(): void
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            // Converter erro para exceção
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    /**
     * Configurar tratamento de shutdown
     */
    protected function configureShutdownHandling(): void
    {
        register_shutdown_function(function () {
            $error = error_get_last();
            
            if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
                if ($this->app->bound('log')) {
                    $this->app->make('log')->critical('Fatal error', [
                        'type' => $error['type'],
                        'message' => $error['message'],
                        'file' => $error['file'],
                        'line' => $error['line'],
                    ]);
                }
            }
        });
    }
}