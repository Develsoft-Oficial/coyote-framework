<?php
// vendors/coyote/Core/Config.php

namespace Coyote\Core;

use ArrayAccess;
use Coyote\Core\Exceptions\ConfigException;

/**
 * Sistema de configuração
 */
class Config implements ArrayAccess
{
    /**
     * @var array Itens de configuração
     */
    protected $items = [];

    /**
     * @var array Cache de configurações
     */
    protected $cache = [];

    /**
     * @var string Caminho do diretório de configuração
     */
    protected $configPath;

    /**
     * Criar nova instância de configuração
     *
     * @param array $items
     * @param string $configPath
     */
    public function __construct(array $items = [], string $configPath = '')
    {
        $this->items = $items;
        $this->configPath = $configPath;
    }

    /**
     * Carregar configurações de arquivos
     *
     * @param string $path Caminho do diretório de configuração
     */
    public function loadFromPath(string $path): void
    {
        $this->configPath = $path;

        if (!is_dir($path)) {
            throw new ConfigException("Configuration path [$path] does not exist.");
        }

        $files = glob($path . '/*.php');

        foreach ($files as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            $this->set($key, require $file);
        }
    }

    /**
     * Obter valor de configuração
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $value = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                $this->cache[$key] = $default;
                return $default;
            }

            $value = $value[$segment];
        }

        $this->cache[$key] = $value;
        return $value;
    }

    /**
     * Definir valor de configuração
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $array = &$this->items;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        // Limpar cache para esta chave
        $this->clearCache($key);
    }

    /**
     * Verificar se chave existe
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $value = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    /**
     * Obter todos os itens de configuração
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Limpar cache
     *
     * @param string|null $key Chave específica ou null para limpar tudo
     */
    public function clearCache(?string $key = null): void
    {
        if ($key === null) {
            $this->cache = [];
            return;
        }

        // Limpar cache para esta chave e prefixos relacionados
        foreach (array_keys($this->cache) as $cachedKey) {
            if (strpos($cachedKey, $key . '.') === 0 || $cachedKey === $key) {
                unset($this->cache[$cachedKey]);
            }
        }
    }

    /**
     * Salvar configuração em arquivo
     *
     * @param string $key
     * @param array $data
     */
    public function save(string $key, array $data): void
    {
        if (!$this->configPath) {
            throw new ConfigException("Configuration path not set.");
        }

        $file = $this->configPath . '/' . $key . '.php';
        $content = '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($data, true) . ';' . PHP_EOL;

        if (file_put_contents($file, $content) === false) {
            throw new ConfigException("Failed to save configuration to [$file].");
        }

        // Recarregar configuração
        $this->set($key, $data);
    }

    /**
     * Recarregar configurações do disco
     */
    public function reload(): void
    {
        if ($this->configPath) {
            $this->loadFromPath($this->configPath);
        }
    }

    /**
     * Obter ambiente
     *
     * @return string
     */
    public function environment(): string
    {
        return $this->get('app.env', 'production');
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
        return in_array($this->environment(), $environments);
    }

    /**
     * Verificar se está em modo debug
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return (bool) $this->get('app.debug', false);
    }

    /**
     * Implementação ArrayAccess: offsetExists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Implementação ArrayAccess: offsetGet
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Implementação ArrayAccess: offsetSet
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Implementação ArrayAccess: offsetUnset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        $this->set($offset, null);
    }

    /**
     * Método mágico __get
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Método mágico __set
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Método mágico __isset
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Método mágico __unset
     *
     * @param string $key
     */
    public function __unset(string $key): void
    {
        $this->set($key, null);
    }
}