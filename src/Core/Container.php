<?php
// vendors/coyote/Core/Container.php

namespace Coyote\Core;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use Coyote\Core\Exceptions\ContainerException;
use Coyote\Core\Exceptions\NotFoundException;

/**
 * Container de Injeção de Dependências
 */
class Container
{
    /**
     * @var Container Instância singleton
     */
    protected static $instance;

    /**
     * @var array Bindings registrados
     */
    protected $bindings = [];

    /**
     * @var array Instâncias singleton
     */
    protected $instances = [];

    /**
     * @var array Aliases registrados
     */
    protected $aliases = [];

    /**
     * @var array Contextual bindings
     */
    protected $contextual = [];

    /**
     * @var array Extensões registradas
     */
    protected $extenders = [];

    /**
     * @var array Callbacks de resolução
     */
    protected $resolvingCallbacks = [];

    /**
     * @var array Callbacks após resolução
     */
    protected $afterResolvingCallbacks = [];

    /**
     * Definir instância singleton do container
     *
     * @param Container|null $container
     * @return Container
     */
    public static function setInstance(?Container $container = null): Container
    {
        return static::$instance = $container;
    }

    /**
     * Obter instância singleton do container
     *
     * @return Container
     */
    public static function getInstance(): Container
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Registrar um binding
     *
     * @param string $abstract
     * @param mixed $concrete
     * @param bool $shared
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        $this->dropStaleInstances($abstract);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (!$concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Registrar um binding singleton
     *
     * @param string $abstract
     * @param mixed $concrete
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Registrar uma instância existente como singleton
     *
     * @param string $abstract
     * @param mixed $instance
     */
    public function instance(string $abstract, $instance): void
    {
        $this->removeAlias($abstract);

        $this->instances[$abstract] = $instance;
    }

    /**
     * Registrar um alias
     *
     * @param string $abstract
     * @param string $alias
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * Obter closure para binding
     *
     * @param string $abstract
     * @param mixed $concrete
     * @return Closure
     */
    protected function getClosure(string $abstract, $concrete): Closure
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            if ($abstract == $concrete) {
                return $container->build($concrete);
            }

            return $container->make($concrete, $parameters);
        };
    }

    /**
     * Resolver uma instância do container
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function make(string $abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        // Se já existe uma instância singleton, retornar
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Obter concrete para o abstract
        $concrete = $this->getConcrete($abstract);

        // Verificar se é possível construir
        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $this->make($concrete, $parameters);
        }

        // Se for shared, armazenar como instância
        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        $this->fireResolvingCallbacks($abstract, $object);

        return $object;
    }

    /**
     * Obter concrete para abstract
     *
     * @param string $abstract
     * @return mixed
     */
    protected function getConcrete(string $abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Verificar se é buildable
     *
     * @param mixed $concrete
     * @param string $abstract
     * @return bool
     */
    protected function isBuildable($concrete, string $abstract): bool
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * Verificar se é shared
     *
     * @param string $abstract
     * @return bool
     */
    protected function isShared(string $abstract): bool
    {
        return isset($this->instances[$abstract]) ||
               (isset($this->bindings[$abstract]['shared']) &&
                $this->bindings[$abstract]['shared'] === true);
    }

    /**
     * Construir uma instância
     *
     * @param mixed $concrete
     * @param array $parameters
     * @return mixed
     * @throws ContainerException
     */
    public function build($concrete, array $parameters = [])
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new NotFoundException("Target class [$concrete] does not exist.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new ContainerException("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();

        $instances = $this->resolveDependencies($dependencies, $parameters);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolver dependências
     *
     * @param array $dependencies
     * @param array $parameters
     * @return array
     * @throws ContainerException
     */
    protected function resolveDependencies(array $dependencies, array $parameters): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            // Verificar se parâmetro foi fornecido
            if (array_key_exists($dependency->name, $parameters)) {
                $results[] = $parameters[$dependency->name];
                continue;
            }

            // Verificar se parâmetro tem valor padrão
            if ($dependency->isDefaultValueAvailable()) {
                $results[] = $dependency->getDefaultValue();
                continue;
            }

            // Verificar se é uma classe
            if ($dependency->hasType()) {
                $type = $dependency->getType();
                
                if (!$type->isBuiltin()) {
                    $results[] = $this->make($type->getName());
                    continue;
                }
            }

            throw new ContainerException(
                "Unresolvable dependency [$dependency] in class {$dependency->getDeclaringClass()->getName()}"
            );
        }

        return $results;
    }

    /**
     * Obter alias para abstract
     *
     * @param string $abstract
     * @return string
     */
    protected function getAlias(string $abstract): string
    {
        if (!isset($this->aliases[$abstract])) {
            return $abstract;
        }

        return $this->getAlias($this->aliases[$abstract]);
    }

    /**
     * Remover alias
     *
     * @param string $searched
     */
    protected function removeAlias(string $searched): void
    {
        foreach ($this->aliases as $alias => $abstract) {
            if ($abstract == $searched) {
                unset($this->aliases[$alias]);
            }
        }
    }

    /**
     * Remover instâncias stale
     *
     * @param string $abstract
     */
    protected function dropStaleInstances(string $abstract): void
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }

    /**
     * Registrar callback de resolução
     *
     * @param string $abstract
     * @param Closure $callback
     */
    public function resolving(string $abstract, Closure $callback): void
    {
        $this->resolvingCallbacks[$abstract][] = $callback;
    }

    /**
     * Registrar callback após resolução
     *
     * @param string $abstract
     * @param Closure $callback
     */
    public function afterResolving(string $abstract, Closure $callback): void
    {
        $this->afterResolvingCallbacks[$abstract][] = $callback;
    }

    /**
     * Executar callbacks de resolução
     *
     * @param string $abstract
     * @param mixed $object
     */
    protected function fireResolvingCallbacks(string $abstract, $object): void
    {
        $this->fireCallbackArray($object, $this->resolvingCallbacks[$abstract] ?? []);
        $this->fireCallbackArray($object, $this->afterResolvingCallbacks[$abstract] ?? []);
    }

    /**
     * Executar array de callbacks
     *
     * @param mixed $object
     * @param array $callbacks
     */
    protected function fireCallbackArray($object, array $callbacks): void
    {
        foreach ($callbacks as $callback) {
            $callback($object, $this);
        }
    }

    /**
     * Verificar se abstract está vinculado
     *
     * @param string $abstract
     * @return bool
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) ||
               isset($this->instances[$abstract]) ||
               isset($this->aliases[$abstract]);
    }

    /**
     * Verificar se abstract foi resolvido
     *
     * @param string $abstract
     * @return bool
     */
    public function resolved(string $abstract): bool
    {
        return isset($this->instances[$abstract]) || $this->bound($abstract);
    }

    /**
     * Obter todos os bindings
     *
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Obter todas as instâncias
     *
     * @return array
     */
    public function getInstances(): array
    {
        return $this->instances;
    }

    /**
     * Obter todos os aliases
     *
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Limpar container
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->aliases = [];
        $this->resolvingCallbacks = [];
        $this->afterResolvingCallbacks = [];
    }
}