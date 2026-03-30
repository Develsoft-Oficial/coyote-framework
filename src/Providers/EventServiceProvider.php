<?php
// vendors/coyote/Providers/EventServiceProvider.php

namespace Coyote\Providers;

/**
 * Service provider para eventos
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array Listeners de eventos
     */
    protected $listen = [];

    /**
     * @var array Subscribers de eventos
     */
    protected $subscribe = [];

    /**
     * Registrar o service provider
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton('events', function ($app) {
            return new \Coyote\Events\Dispatcher($app);
        });
    }

    /**
     * Inicializar o service provider
     */
    public function boot(): void
    {
        parent::boot();

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->app['events']->listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            $this->app['events']->subscribe($subscriber);
        }
    }

    /**
     * Obter eventos e listeners
     *
     * @return array
     */
    public function getEvents(): array
    {
        return $this->listen;
    }

    /**
     * Obter subscribers
     *
     * @return array
     */
    public function getSubscribers(): array
    {
        return $this->subscribe;
    }
}