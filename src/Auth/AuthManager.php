<?php

namespace Coyote\Auth;

use Coyote\Auth\Contracts\Authenticatable;
use Coyote\Auth\Contracts\UserProvider;
use Coyote\Auth\Guards\Guard;
use Coyote\Config\Repository as ConfigRepository;
use InvalidArgumentException;

class AuthManager
{
    /**
     * The configuration repository.
     *
     * @var \Coyote\Config\Repository
     */
    protected $config;

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * The default guard name.
     *
     * @var string
     */
    protected $defaultGuard;

    /**
     * Create a new Auth manager instance.
     *
     * @param  mixed  $config
     * @return void
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->defaultGuard = $config->get('auth.defaults.guard', 'web');
    }

    /**
     * Attempt to get the guard from the local cache.
     *
     * @param  string|null  $name
     * @return \Coyote\Auth\Guards\Guard
     */
    public function guard(?string $name = null): Guard
    {
        $name = $name ?: $this->defaultGuard;

        if (!isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    /**
     * Create a new driver instance.
     *
     * @param  string  $name
     * @return \Coyote\Auth\Guards\Guard
     *
     * @throws \InvalidArgumentException
     */
    protected function createDriver(string $name): Guard
    {
        $config = $this->getConfig($name);

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }

        $driverMethod = 'create' . ucfirst($config['driver']) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($name, $config);
        }

        throw new InvalidArgumentException(
            "Auth driver [{$config['driver']}] is not supported."
        );
    }

    /**
     * Call a custom driver creator.
     *
     * @param  array  $config
     * @return \Coyote\Auth\Guards\Guard
     */
    protected function callCustomCreator(array $config): Guard
    {
        return $this->customCreators[$config['driver']]($this, $config);
    }

    /**
     * Create a session based authentication guard.
     *
     * @param  string  $name
     * @param  array  $config
     * @return \Coyote\Auth\Guards\Guard
     */
    protected function createSessionDriver(string $name, array $config): Guard
    {
        $provider = $this->createUserProvider($config['provider'] ?? null);

        $guard = new \Coyote\Auth\Guards\SessionGuard(
            $name,
            $provider,
            $this->config->get('session') ?: []
        );

        // Set the cookie jar if available
        if (isset($config['cookie'])) {
            $guard->setCookieJar($config['cookie']);
        }

        return $guard;
    }

    /**
     * Create a token based authentication guard.
     *
     * @param  string  $name
     * @param  array  $config
     * @return \Coyote\Auth\Guards\Guard
     */
    protected function createTokenDriver(string $name, array $config): Guard
    {
        $provider = $this->createUserProvider($config['provider'] ?? null);

        return new \Coyote\Auth\Guards\TokenGuard(
            $name,
            $provider,
            $config['input_key'] ?? 'api_token',
            $config['storage_key'] ?? 'api_token',
            $config['hash'] ?? false
        );
    }

    /**
     * Create a user provider implementation.
     *
     * @param  string|null  $provider
     * @return \Coyote\Auth\Contracts\UserProvider
     *
     * @throws \InvalidArgumentException
     */
    public function createUserProvider(?string $provider = null): UserProvider
    {
        if (is_null($config = $this->getProviderConfiguration($provider))) {
            throw new InvalidArgumentException(
                "Authentication user provider [{$provider}] is not defined."
            );
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomProviderCreator($config);
        }

        $driverMethod = 'create' . ucfirst($config['driver']) . 'Provider';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }

        throw new InvalidArgumentException(
            "Authentication user provider [{$config['driver']}] is not supported."
        );
    }

    /**
     * Call a custom provider creator.
     *
     * @param  array  $config
     * @return \Coyote\Auth\Contracts\UserProvider
     */
    protected function callCustomProviderCreator(array $config): UserProvider
    {
        return $this->customCreators[$config['driver']]($config);
    }

    /**
     * Create an Eloquent user provider.
     *
     * @param  array  $config
     * @return \Coyote\Auth\Contracts\UserProvider
     */
    protected function createEloquentProvider(array $config): UserProvider
    {
        return new \Coyote\Auth\Providers\EloquentProvider($config['model']);
    }

    /**
     * Create a database user provider.
     *
     * @param  array  $config
     * @return \Coyote\Auth\Contracts\UserProvider
     */
    protected function createDatabaseProvider(array $config): UserProvider
    {
        $connection = $config['connection'] ?? null;
        $table = $config['table'] ?? 'users';
        $identifier = $config['identifier'] ?? 'id';

        return new \Coyote\Auth\Providers\DatabaseProvider(
            $this->config->get('database'),
            $connection,
            $table,
            $identifier
        );
    }

    /**
     * Get the guard configuration.
     *
     * @param  string  $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getConfig(string $name): array
    {
        $config = $this->config->get("auth.guards.{$name}");

        if (is_null($config)) {
            throw new InvalidArgumentException(
                "Auth guard [{$name}] is not defined."
            );
        }

        return $config;
    }

    /**
     * Get the user provider configuration.
     *
     * @param  string|null  $provider
     * @return array|null
     */
    protected function getProviderConfiguration(?string $provider = null): ?array
    {
        if (is_null($provider)) {
            $provider = $this->config->get('auth.defaults.provider');
        }

        return $this->config->get("auth.providers.{$provider}");
    }

    /**
     * Get the default guard name.
     *
     * @return string
     */
    public function getDefaultGuard(): string
    {
        return $this->defaultGuard;
    }

    /**
     * Set the default guard name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultGuard(string $name): void
    {
        $this->defaultGuard = $name;
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend(string $driver, \Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Register a custom provider creator Closure.
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function provider(string $driver, \Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }
}