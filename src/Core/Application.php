<?php
// vendors/coyote/Core/Application.php

namespace Coyote\Core;

use Coyote\Core\Container;
use Coyote\Core\Config;
use Coyote\Core\Exceptions\ApplicationException;

/**
 * Classe principal da aplicação
 */
class Application extends Container
{
    /**
     * @var string Caminho base da aplicação
     */
    protected $basePath;

    /**
     * @var string Caminho do diretório de aplicação
     */
    protected $appPath;

    /**
     * @var string Caminho do diretório de configuração
     */
    protected $configPath;

    /**
     * @var string Caminho do diretório de storage
     */
    protected $storagePath;

    /**
     * @var string Caminho do diretório público
     */
    protected $publicPath;

    /**
     * @var string Caminho do diretório de recursos
     */
    protected $resourcesPath;

    /**
     * @var string Ambiente da aplicação
     */
    protected $environment = 'production';

    /**
     * @var bool Flag de debug
     */
    protected $debug = false;

    /**
     * @var bool Flag indicando se a aplicação foi inicializada
     */
    protected $booted = false;

    /**
     * @var array Service providers registrados
     */
    protected $serviceProviders = [];

    /**
     * @var array Service providers carregados
     */
    protected $loadedProviders = [];

    /**
     * @var array Aliases de classes
     */
    protected $aliases = [];

    /**
     * Criar uma nova instância da aplicação
     *
     * @param string $basePath Caminho base da aplicação
     */
    public function __construct(string $basePath)
    {
        $this->setBasePath($basePath);
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

    /**
     * Definir caminho base da aplicação
     *
     * @param string $basePath
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '\/');
        $this->appPath = $this->basePath . '/app';
        $this->configPath = $this->basePath . '/config';
        $this->storagePath = $this->basePath . '/storage';
        $this->publicPath = $this->basePath . '/public';
        $this->resourcesPath = $this->basePath . '/resources';
    }

    /**
     * Registrar bindings base
     */
    protected function registerBaseBindings(): void
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(Container::class, $this);
        $this->instance(Application::class, $this);
    }

    /**
     * Registrar service providers base
     */
    protected function registerBaseServiceProviders(): void
    {
        $this->register(new \Coyote\Providers\ConfigServiceProvider($this));
        $this->register(new \Coyote\Providers\EventServiceProvider($this));
        $this->register(new \Coyote\Providers\LogServiceProvider($this));
        $this->register(new \Coyote\Providers\ViewServiceProvider($this));
    }

    /**
     * Registrar um service provider
     *
     * @param mixed $provider
     * @param bool $force Forçar registro mesmo se já registrado
     */
    public function register($provider, bool $force = false): void
    {
        if (($registered = $this->getProvider($provider)) && !$force) {
            return;
        }

        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        $provider->register();

        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings as $key => $value) {
                $this->bind($key, $value);
            }
        }

        if (property_exists($provider, 'singletons')) {
            foreach ($provider->singletons as $key => $value) {
                $this->singleton($key, $value);
            }
        }

        $this->markAsRegistered($provider);

        if ($this->booted) {
            $this->bootProvider($provider);
        }
    }

    /**
     * Resolver provider pelo nome da classe
     *
     * @param string $provider
     * @return mixed
     */
    public function resolveProvider(string $provider)
    {
        return new $provider($this);
    }

    /**
     * Marcar provider como registrado
     *
     * @param mixed $provider
     */
    protected function markAsRegistered($provider): void
    {
        $this->serviceProviders[] = $provider;
        $this->loadedProviders[get_class($provider)] = true;
    }

    /**
     * Obter provider registrado
     *
     * @param mixed $provider
     * @return mixed|null
     */
    public function getProvider($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);
        
        foreach ($this->serviceProviders as $value) {
            if ($value instanceof $name) {
                return $value;
            }
        }
        
        return null;
    }

    /**
     * Inicializar aplicação
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->serviceProviders as $provider) {
            $this->bootProvider($provider);
        }

        $this->booted = true;
    }

    /**
     * Inicializar provider
     *
     * @param mixed $provider
     */
    protected function bootProvider($provider): void
    {
        if (method_exists($provider, 'boot')) {
            $provider->boot();
        }
    }

    /**
     * Registrar aliases core do container
     */
    protected function registerCoreContainerAliases(): void
    {
        // Registrar aliases individuais
        $this->alias('app', self::class);
        $this->alias('app', \Coyote\Core\Container::class);
        $this->alias('config', \Coyote\Core\Config::class);
        
        // Nota: Environment::class não existe ainda, vamos comentar por enquanto
        // $this->alias('env', \Coyote\Core\Environment::class);
    }

    /**
     * Executar aplicação
     *
     * @return mixed
     */
    public function run()
    {
        $this->boot();
        
        // Aqui será implementado o kernel HTTP
        // Por enquanto retorna uma resposta básica
        return $this->make('response', [
            'content' => 'Coyote Framework is running!',
            'status' => 200,
        ]);
    }

    /**
     * Obter caminho base
     *
     * @param string $path Caminho relativo
     * @return string
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Obter caminho da aplicação
     *
     * @param string $path Caminho relativo
     * @return string
     */
    public function appPath(string $path = ''): string
    {
        return $this->appPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Obter caminho de configuração
     *
     * @param string $path Caminho relativo
     * @return string
     */
    public function configPath(string $path = ''): string
    {
        return $this->configPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Obter caminho de storage
     *
     * @param string $path Caminho relativo
     * @return string
     */
    public function storagePath(string $path = ''): string
    {
        return $this->storagePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Obter caminho público
     *
     * @param string $path Caminho relativo
     * @return string
     */
    public function publicPath(string $path = ''): string
    {
        return $this->publicPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Obter caminho de recursos
     *
     * @param string $path Caminho relativo
     * @return string
     */
    public function resourcesPath(string $path = ''): string
    {
        return $this->resourcesPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Definir ambiente
     *
     * @param string $environment
     */
    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    /**
     * Obter ambiente
     *
     * @return string
     */
    public function environment(): string
    {
        return $this->environment;
    }

    /**
     * Verificar se está em ambiente específico
     *
     * @param string|array $environments
     * @return bool
     */
    public function environmentIs($environments): bool
    {
        $environments = (array) $environments;
        return in_array($this->environment, $environments);
    }

    /**
     * Definir modo debug
     *
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * Verificar se está em modo debug
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Verificar se aplicação está inicializada
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Obter versão do framework
     *
     * @return string
     */
    public function version(): string
    {
        return '1.0.0-dev';
    }

    /**
     * Obter todos os service providers registrados
     *
     * @return array
     */
    public function getProviders(): array
    {
        return $this->serviceProviders;
    }

    /**
     * Obter aliases registrados
     *
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }
}