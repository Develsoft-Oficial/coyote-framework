<?php
// vendors/coyote/Database/Migrations/Migrator.php

namespace Coyote\Database\Migrations;

use Coyote\Database\Connection;
use Coyote\Database\DatabaseManager;
use Coyote\Database\QueryBuilder;
use InvalidArgumentException;
use RuntimeException;
use Exception;

/**
 * Migrator - Executor de migrations
 * 
 * Responsável por executar, reverter e gerenciar
 * o ciclo de vida das migrations.
 */
class Migrator
{
    /**
     * Repositório de migrations
     * 
     * @var \Coyote\Database\Migrations\MigrationRepository
     */
    protected $repository;

    /**
     * Gerenciador de banco de dados
     * 
     * @var \Coyote\Database\DatabaseManager
     */
    protected $db;

    /**
     * Resolvedor de arquivos de migration
     * 
     * @var \Coyote\Database\Migrations\MigrationResolver
     */
    protected $resolver;

    /**
     * Caminhos onde procurar por migrations
     * 
     * @var array
     */
    protected $paths = [];

    /**
     * Construtor do migrator
     * 
     * @param \Coyote\Database\Migrations\MigrationRepository $repository
     * @param \Coyote\Database\DatabaseManager $db
     */
    public function __construct(MigrationRepository $repository, DatabaseManager $db)
    {
        $this->repository = $repository;
        $this->db = $db;
        $this->resolver = new MigrationResolver();
    }

    /**
     * Executar migrations pendentes
     * 
     * @param array $paths Caminhos para procurar migrations
     * @param array $options Opções de execução
     * @return array
     */
    public function run(array $paths = [], array $options = []): array
    {
        $this->paths = $paths ?: $this->paths;
        
        // Garantir que o repositório existe
        $this->repository->createRepository();
        
        // Obter migrations disponíveis e pendentes
        $files = $this->getMigrationFiles($this->paths);
        $pending = $this->repository->getPending($files);
        
        if (empty($pending)) {
            return ['migrations' => [], 'batch' => 0];
        }
        
        // Ordenar migrations por timestamp
        $pending = $this->sortMigrations($pending);
        
        // Obter próximo batch number
        $batch = $this->repository->getNextBatchNumber();
        
        $runMigrations = [];
        
        foreach ($pending as $migration) {
            try {
                $this->runMigration($migration, $batch, $options);
                $runMigrations[] = $migration;
            } catch (Exception $e) {
                // Se uma migration falhar, parar a execução
                throw new RuntimeException(
                    "Migration {$migration} failed: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }
        
        return [
            'migrations' => $runMigrations,
            'batch' => $batch,
            'total' => count($runMigrations)
        ];
    }

    /**
     * Executar uma migration específica
     * 
     * @param string $migration Nome da migration
     * @param int $batch Número do batch
     * @param array $options Opções de execução
     * @return bool
     */
    protected function runMigration(string $migration, int $batch, array $options = []): bool
    {
        $file = $this->getMigrationFile($migration);
        
        if (!$file) {
            throw new RuntimeException("Migration file not found: {$migration}");
        }
        
        // Carregar a classe da migration
        $instance = $this->resolve($migration);
        
        if (!$instance) {
            throw new RuntimeException("Failed to resolve migration: {$migration}");
        }
        
        // Executar a migration
        $pretend = $options['pretend'] ?? false;
        
        if ($pretend) {
            $this->pretendToRun($instance, $migration, $batch);
            return true;
        }
        
        // Executar migration real
        $connection = $this->getConnection($options);
        $instance->setConnection($connection);
        
        $instance->run(true); // up()
        
        // Registrar no repositório
        $this->repository->log($migration, $batch);
        
        return true;
    }

    /**
     * Reverter migrations (rollback)
     * 
     * @param int $steps Número de batches para reverter
     * @param array $options Opções de rollback
     * @return array
     */
    public function rollback(int $steps = 1, array $options = []): array
    {
        $migrations = $this->repository->getMigrationsToRollback($steps);
        
        if (empty($migrations)) {
            return ['migrations' => [], 'rolled_back' => 0];
        }
        
        $rolledBack = [];
        
        foreach ($migrations as $migration) {
            try {
                $this->rollbackMigration($migration, $options);
                $rolledBack[] = $migration;
            } catch (Exception $e) {
                throw new RuntimeException(
                    "Rollback of migration {$migration} failed: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }
        
        return [
            'migrations' => $rolledBack,
            'rolled_back' => count($rolledBack)
        ];
    }

    /**
     * Reverter todas as migrations (reset)
     * 
     * @param array $options Opções de reset
     * @return array
     */
    public function reset(array $options = []): array
    {
        $migrations = $this->repository->getMigrationsToReset();
        
        if (empty($migrations)) {
            return ['migrations' => [], 'reset' => 0];
        }
        
        $reset = [];
        
        foreach ($migrations as $migration) {
            try {
                $this->rollbackMigration($migration, $options);
                $reset[] = $migration;
            } catch (Exception $e) {
                throw new RuntimeException(
                    "Reset of migration {$migration} failed: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }
        
        return [
            'migrations' => $reset,
            'reset' => count($reset)
        ];
    }

    /**
     * Reverter uma migration específica
     * 
     * @param string $migration Nome da migration
     * @param array $options Opções de rollback
     * @return bool
     */
    protected function rollbackMigration(string $migration, array $options = []): bool
    {
        $file = $this->getMigrationFile($migration);
        
        if (!$file) {
            throw new RuntimeException("Migration file not found for rollback: {$migration}");
        }
        
        // Carregar a classe da migration
        $instance = $this->resolve($migration);
        
        if (!$instance) {
            throw new RuntimeException("Failed to resolve migration for rollback: {$migration}");
        }
        
        // Executar rollback
        $pretend = $options['pretend'] ?? false;
        
        if ($pretend) {
            $this->pretendToRollback($instance, $migration);
            return true;
        }
        
        // Executar rollback real
        $connection = $this->getConnection($options);
        $instance->setConnection($connection);
        
        $instance->run(false); // down()
        
        // Remover do repositório
        $this->repository->delete($migration);
        
        return true;
    }

    /**
     * Obter status das migrations
     * 
     * @param array $paths Caminhos para procurar migrations
     * @return array
     */
    public function status(array $paths = []): array
    {
        $this->paths = $paths ?: $this->paths;
        
        // Garantir que o repositório existe
        if (!$this->repository->repositoryExists()) {
            return ['repository' => 'not created'];
        }
        
        $files = $this->getMigrationFiles($this->paths);
        $ran = $this->repository->getRan();
        $batches = $this->repository->getMigrationBatches();
        
        $status = [];
        
        foreach ($files as $file) {
            $migration = $this->getMigrationName($file);
            $status[] = [
                'migration' => $migration,
                'status' => in_array($migration, $ran) ? 'Ran' : 'Pending',
                'batch' => $batches[$migration] ?? null,
                'file' => $file
            ];
        }
        
        // Ordenar por migration name
        usort($status, function ($a, $b) {
            return strcmp($a['migration'], $b['migration']);
        });
        
        return $status;
    }

    /**
     * Simular execução de migration (pretend)
     * 
     * @param Migration $instance Instância da migration
     * @param string $migration Nome da migration
     * @param int $batch Número do batch
     * @return void
     */
    protected function pretendToRun(Migration $instance, string $migration, int $batch): void
    {
        $queries = [];
        
        // Aqui seria implementada a coleta de queries
        // que a migration executaria
        $queries[] = "Running migration: {$migration} (batch: {$batch})";
        
        // Para fins de demonstração
        echo "Would run migration: {$migration} in batch {$batch}\n";
        
        foreach ($queries as $query) {
            echo "  {$query}\n";
        }
    }

    /**
     * Simular rollback de migration (pretend)
     * 
     * @param Migration $instance Instância da migration
     * @param string $migration Nome da migration
     * @return void
     */
    protected function pretendToRollback(Migration $instance, string $migration): void
    {
        $queries = [];
        
        // Aqui seria implementada a coleta de queries
        // que o rollback executaria
        $queries[] = "Rolling back migration: {$migration}";
        
        // Para fins de demonstração
        echo "Would rollback migration: {$migration}\n";
        
        foreach ($queries as $query) {
            echo "  {$query}\n";
        }
    }

    /**
     * Obter arquivos de migration
     * 
     * @param array $paths Caminhos para procurar
     * @return array
     */
    protected function getMigrationFiles(array $paths): array
    {
        $files = [];
        
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }
            
            $pathFiles = glob($path . '/*.php');
            
            foreach ($pathFiles as $file) {
                $files[] = $file;
            }
        }
        
        return $files;
    }

    /**
     * Obter arquivo de migration pelo nome
     * 
     * @param string $migration Nome da migration
     * @return string|null
     */
    protected function getMigrationFile(string $migration): ?string
    {
        foreach ($this->paths as $path) {
            $file = $path . '/' . $migration . '.php';
            
            if (file_exists($file)) {
                return $file;
            }
        }
        
        return null;
    }

    /**
     * Obter nome da migration a partir do arquivo
     * 
     * @param string $file Caminho do arquivo
     * @return string
     */
    protected function getMigrationName(string $file): string
    {
        return pathinfo($file, PATHINFO_FILENAME);
    }

    /**
     * Resolver uma migration (carregar a classe)
     * 
     * @param string $migration Nome da migration
     * @return Migration|null
     */
    protected function resolve(string $migration): ?Migration
    {
        return $this->resolver->resolve($migration, $this->paths);
    }

    /**
     * Ordenar migrations por timestamp
     * 
     * @param array $migrations Lista de migrations
     * @return array
     */
    protected function sortMigrations(array $migrations): array
    {
        usort($migrations, function ($a, $b) {
            return strcmp($a, $b);
        });
        
        return $migrations;
    }

    /**
     * Obter conexão com o banco de dados
     * 
     * @param array $options Opções
     * @return \Coyote\Database\Connection
     */
    protected function getConnection(array $options = []): Connection
    {
        $connection = $options['connection'] ?? null;
        
        if ($connection) {
            return $this->db->connection($connection);
        }
        
        return $this->db->connection();
    }

    /**
     * Definir caminhos para procurar migrations
     * 
     * @param array $paths
     * @return self
     */
    public function setPaths(array $paths): self
    {
        $this->paths = $paths;
        return $this;
    }

    /**
     * Adicionar caminho para procurar migrations
     * 
     * @param string $path
     * @return self
     */
    public function addPath(string $path): self
    {
        $this->paths[] = $path;
        return $this;
    }

    /**
     * Obter estatísticas das migrations
     * 
     * @return array
     */
    public function getStats(): array
    {
        return $this->repository->getStats();
    }

    /**
     * Verificar se há migrations pendentes
     * 
     * @param array $paths Caminhos para procurar
     * @return bool
     */
    public function hasPending(array $paths = []): bool
    {
        $this->paths = $paths ?: $this->paths;
        $files = $this->getMigrationFiles($this->paths);
        $pending = $this->repository->getPending($files);
        
        return !empty($pending);
    }

    /**
     * Obter número total de migrations pendentes
     * 
     * @param array $paths Caminhos para procurar
     * @return int
     */
    public function pendingCount(array $paths = []): int
    {
        $this->paths = $paths ?: $this->paths;
        $files = $this->getMigrationFiles($this->paths);
        $pending = $this->repository->getPending($files);
        
        return count($pending);
    }
}

/**
 * Resolvedor de migrations
 * 
 * Classe auxiliar para carregar classes de migration
 */
class MigrationResolver
{
    /**
     * Resolver uma migration (carregar a classe)
     * 
     * @param string $migration Nome da migration
     * @param array $paths Caminhos para procurar
     * @return Migration|null
     */
    public function resolve(string $migration, array $paths): ?Migration
    {
        foreach ($paths as $path) {
            $file = $path . '/' . $migration . '.php';
            
            if (file_exists($file)) {
                require_once $file;
                
                $className = $this->getClassName($migration);
                
                if (class_exists($className)) {
                    // Nota: A instância real seria criada com a conexão apropriada
                    // Esta é uma implementação simplificada
                    return new $className();
                }
            }
        }
        
        return null;
    }

    /**
     * Obter nome da classe a partir do nome do arquivo
     * 
     * @param string $migration Nome da migration
     * @return string
     */
    protected function getClassName(string $migration): string
    {
        // Converter snake_case para PascalCase
        $parts = explode('_', $migration);
        $className = '';
        
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }
        
        return $className;
    }
}