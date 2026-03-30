<?php
// src/Support/Autoloader.php

namespace Coyote;

/**
 * Autoloader PSR-4 para o framework Coyote
 *
 * Esta classe fornece autoloading otimizado para produção
 * e é registrada automaticamente via Composer scripts
 */
class Autoloader
{
    /**
     * @var array Mapeamento de namespaces para diretórios
     */
    private static $prefixes = [];

    /**
     * @var bool Flag de registro
     */
    private static $registered = false;

    /**
     * @var array Cache de classmap para produção
     */
    private static $classMap = [];

    /**
     * @var string Caminho do arquivo de cache
     */
    private static $classMapFile = '';

    /**
     * Registrar um namespace PSR-4
     *
     * @param string $prefix Prefixo do namespace
     * @param string $baseDir Diretório base
     * @param bool $prepend Adicionar no início da pilha
     */
    public static function addNamespace(string $prefix, string $baseDir, bool $prepend = false): void
    {
        // Normalizar prefixo
        $prefix = trim($prefix, '\\') . '\\';
        
        // Normalizar diretório
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!isset(self::$prefixes[$prefix])) {
            self::$prefixes[$prefix] = [];
        }

        if ($prepend) {
            array_unshift(self::$prefixes[$prefix], $baseDir);
        } else {
            self::$prefixes[$prefix][] = $baseDir;
        }
    }

    /**
     * Registrar autoloader
     */
    public static function register(): void
    {
        if (!self::$registered) {
            spl_autoload_register([self::class, 'loadClass'], true, true);
            self::$registered = true;
            
            // Inicializar caminho do cache
            self::$classMapFile = dirname(__DIR__) . '/storage/cache/classmap.php';
            
            // Tentar carregar cache se existir
            self::loadFromCache();
        }
    }

    /**
     * Carregar classe
     *
     * @param string $class Nome da classe
     * @return bool Sucesso
     */
    public static function loadClass(string $class): bool
    {
        // Primeiro tentar classmap cache
        if (!empty(self::$classMap) && isset(self::$classMap[$class])) {
            require self::$classMap[$class];
            return true;
        }

        $prefix = $class;
        
        // Encontrar o prefixo mais específico
        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);
            $relativeClass = substr($class, $pos + 1);

            if (isset(self::$prefixes[$prefix])) {
                foreach (self::$prefixes[$prefix] as $baseDir) {
                    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
                    
                    if (self::requireFile($file)) {
                        return true;
                    }
                }
            }

            $prefix = rtrim($prefix, '\\');
        }

        return false;
    }

    /**
     * Requisitar arquivo se existir
     *
     * @param string $file Caminho do arquivo
     * @return bool Sucesso
     */
    private static function requireFile(string $file): bool
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        
        return false;
    }

    /**
     * Construir mapa de classes para cache
     */
    public static function buildClassMap(): void
    {
        $map = [];
        
        foreach (self::$prefixes as $prefix => $directories) {
            foreach ($directories as $directory) {
                self::scanDirectory($directory, $prefix, $map);
            }
        }
        
        // Salvar cache
        $content = '<?php return ' . var_export($map, true) . ';';
        file_put_contents(self::$classMapFile, $content);
        
        self::$classMap = $map;
    }

    /**
     * Escanear diretório recursivamente
     */
    private static function scanDirectory(string $dir, string $prefix, array &$map): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace(
                    [$dir . DIRECTORY_SEPARATOR, '.php', '/'],
                    ['', '', '\\'],
                    $file->getPathname()
                );
                
                $className = $prefix . $relativePath;
                $map[$className] = $file->getPathname();
            }
        }
    }

    /**
     * Carregar cache do arquivo
     */
    public static function loadFromCache(): bool
    {
        if (file_exists(self::$classMapFile)) {
            self::$classMap = require self::$classMapFile;
            return true;
        }
        
        return false;
    }

    /**
     * Limpar cache
     */
    public static function clearCache(): void
    {
        if (file_exists(self::$classMapFile)) {
            unlink(self::$classMapFile);
        }
        
        self::$classMap = [];
    }

    /**
     * Obter todos os prefixes registrados
     *
     * @return array
     */
    public static function getPrefixes(): array
    {
        return self::$prefixes;
    }

    /**
     * Obter mapa de classes
     *
     * @return array
     */
    public static function getClassMap(): array
    {
        return self::$classMap;
    }
}

// Registrar namespaces padrão do framework
Autoloader::addNamespace('Coyote', __DIR__ . '/coyote');
Autoloader::addNamespace('App', dirname(__DIR__) . '/app');

// Registrar autoloader
Autoloader::register();

// Carregar helpers
require_once __DIR__ . '/coyote/Support/helpers.php';

// Função helper global
if (!function_exists('coyote_autoload')) {
    /**
     * Função helper para carregar classes manualmente
     *
     * @param string $class Nome da classe
     */
    function coyote_autoload(string $class): void
    {
        Coyote\Autoloader::loadClass($class);
    }
}