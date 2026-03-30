# Sistema de Autoload PSR-4

## Visão Geral
Implementação de autoloader PSR-4 otimizado para performance e modularidade.

## Estrutura do Autoloader

### 1. Arquivo Principal: `vendors/autoload.php`
```php
<?php
// vendors/autoload.php

namespace Coyote;

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
     * Obter todos os prefixes registrados
     *
     * @return array
     */
    public static function getPrefixes(): array
    {
        return self::$prefixes;
    }
}

// Registrar namespaces padrão do framework
Autoloader::addNamespace('Coyote', __DIR__ . '/coyote');
Autoloader::addNamespace('App', dirname(__DIR__) . '/app');

// Registrar autoloader
Autoloader::register();

// Função helper global
if (!function_exists('coyote_autoload')) {
    function coyote_autoload(string $class): void
    {
        Coyote\Autoloader::loadClass($class);
    }
}
```

### 2. Arquivo de Configuração: `composer.json`
```json
{
    "name": "coyote/framework",
    "description": "PHP Micro Framework Leve",
    "type": "library",
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "Coyote\\": "vendors/coyote/",
            "App\\": "app/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "require": {
        "php": ">=8.1",
        "ext-pdo": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    },
    "scripts": {
        "post-autoload-dump": [
            "Coyote\\Autoloader::register"
        ]
    }
}
```

### 3. Arquivo de Bootstrap: `public/index.php`
```php
<?php
// public/index.php

// Definir constantes
define('COYOTE_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VENDOR_PATH', BASE_PATH . '/vendors');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Carregar autoloader
require VENDOR_PATH . '/autoload.php';

// Inicializar aplicação
$app = new Coyote\Core\Application(BASE_PATH);

// Executar aplicação
$app->run();
```

## Otimizações de Performance

### 1. Cache de Mapeamento
```php
class Autoloader
{
    private static $classMap = [];
    private static $classMapFile = STORAGE_PATH . '/cache/classmap.php';

    public static function buildClassMap(): void
    {
        $map = [];
        
        foreach (self::$prefixes as $prefix => $directories) {
            foreach ($directories as $directory) {
                self::scanDirectory($directory, $prefix, $map);
            }
        }
        
        // Salvar cache
        file_put_contents(
            self::$classMapFile,
            '<?php return ' . var_export($map, true) . ';'
        );
        
        self::$classMap = $map;
    }

    private static function scanDirectory(string $dir, string $prefix, array &$map): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace(
                    [$dir, '.php', '/'],
                    ['', '', '\\'],
                    $file->getPathname()
                );
                
                $className = $prefix . $relativePath;
                $map[$className] = $file->getPathname();
            }
        }
    }

    public static function loadFromCache(): bool
    {
        if (file_exists(self::$classMapFile)) {
            self::$classMap = require self::$classMapFile;
            return true;
        }
        
        return false;
    }
}
```

### 2. Autoloader Otimizado para Produção
```php
// vendors/autoload_prod.php
<?php

// Carregar mapa de classes em cache
$classMap = require STORAGE_PATH . '/cache/classmap.php';

spl_autoload_register(function ($class) use ($classMap) {
    if (isset($classMap[$class])) {
        require $classMap[$class];
        return true;
    }
    
    return false;
}, true, true);
```

## Sistema de Módulos com Autoload

### 1. Registro Dinâmico de Módulos
```php
class ModuleManager
{
    public function registerModuleAutoload(string $moduleName, string $modulePath): void
    {
        $namespace = "Modules\\{$moduleName}\\";
        Autoloader::addNamespace($namespace, $modulePath);
    }

    public function loadModulesAutoload(): void
    {
        $modulesPath = BASE_PATH . '/modules';
        
        if (is_dir($modulesPath)) {
            $modules = scandir($modulesPath);
            
            foreach ($modules as $module) {
                if ($module !== '.' && $module !== '..' && is_dir($modulesPath . '/' . $module)) {
                    $this->registerModuleAutoload(
                        $module,
                        $modulesPath . '/' . $module . '/src'
                    );
                }
            }
        }
    }
}
```

### 2. Configuração de Módulos
```php
// config/modules.php
return [
    'active' => [
        'Blog',
        'Shop',
        'Admin',
    ],
    
    'paths' => [
        'modules' => BASE_PATH . '/modules',
        'cache' => STORAGE_PATH . '/modules',
    ],
    
    'autoload' => [
        'psr-4' => [
            'Modules\\' => 'modules/',
        ],
    ],
];
```

## Integração com Composer

### 1. Scripts de Otimização
```json
{
    "scripts": {
        "post-install-cmd": [
            "Coyote\\Autoloader::buildClassMap"
        ],
        "post-update-cmd": [
            "Coyote\\Autoloader::buildClassMap"
        ],
        "optimize": [
            "php vendors/coyote/Cli/Commands/OptimizeAutoload.php"
        ]
    }
}
```

### 2. Comando CLI para Otimização
```php
// vendors/coyote/Cli/Commands/OptimizeAutoload.php
class OptimizeAutoload extends Command
{
    public function handle(): int
    {
        $this->info('Building class map...');
        Autoloader::buildClassMap();
        
        $this->info('Generating optimized autoloader...');
        $this->generateOptimizedLoader();
        
        $this->info('Autoloader optimized successfully!');
        return 0;
    }
}
```

## Testes do Autoloader

### 1. Testes Unitários
```php
// tests/AutoloaderTest.php
class AutoloaderTest extends TestCase
{
    public function testNamespaceRegistration(): void
    {
        Autoloader::addNamespace('Test\\', __DIR__ . '/Test');
        
        $prefixes = Autoloader::getPrefixes();
        $this->assertArrayHasKey('Test\\', $prefixes);
    }
    
    public function testClassLoading(): void
    {
        // Criar classe de teste
        $testClass = 'Test\\Example';
        $testFile = __DIR__ . '/Test/Example.php';
        
        file_put_contents($testFile, '<?php namespace Test; class Example {}');
        
        $this->assertTrue(Autoloader::loadClass($testClass));
        $this->assertTrue(class_exists($testClass));
        
        unlink($testFile);
    }
}
```

## Próximos Passos
1. Implementar classe Autoloader completa
2. Criar sistema de cache de classmap
3. Integrar com sistema de módulos
4. Otimizar para ambiente de produção
5. Criar comandos CLI para gerenciamento
6. Documentar uso para desenvolvedores