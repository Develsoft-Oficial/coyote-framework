<?php
// vendors/coyote/Database/Migrations/MigrationRepository.php

namespace Coyote\Database\Migrations;

use Coyote\Database\Connection;
use Coyote\Database\QueryBuilder;
use Coyote\Database\DatabaseManager;
use InvalidArgumentException;

/**
 * Repositório de migrations
 * 
 * Gerencia o armazenamento e recuperação de metadados
 * sobre quais migrations foram aplicadas.
 */
class MigrationRepository
{
    /**
     * Conexão com o banco de dados
     * 
     * @var \Coyote\Database\Connection
     */
    protected $connection;

    /**
     * Query builder para construção de queries
     * 
     * @var \Coyote\Database\QueryBuilder
     */
    protected $builder;

    /**
     * Nome da tabela de migrations
     * 
     * @var string
     */
    protected $table = 'migrations';

    /**
     * Construtor do repositório
     * 
     * @param \Coyote\Database\Connection $connection
     * @param string|null $table Nome da tabela de migrations
     */
    public function __construct(Connection $connection, ?string $table = null)
    {
        $this->connection = $connection;
        $this->builder = new QueryBuilder($connection);
        
        if ($table !== null) {
            $this->table = $table;
        }
    }

    /**
     * Criar a tabela de migrations se não existir
     * 
     * @return bool
     */
    public function createRepository(): bool
    {
        if ($this->repositoryExists()) {
            return true;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL,
            `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `migration_index` (`migration`),
            INDEX `batch_index` (`batch`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        return $this->builder->statement($sql) !== false;
    }

    /**
     * Verificar se a tabela de migrations existe
     * 
     * @return bool
     */
    public function repositoryExists(): bool
    {
        try {
            $result = $this->builder->statement("SHOW TABLES LIKE '{$this->table}'");
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obter todas as migrations que foram aplicadas
     * 
     * @return array
     */
    public function getRan(): array
    {
        $results = $this->builder
            ->table($this->table)
            ->select('migration')
            ->orderBy('id', 'asc')
            ->get();

        return array_column($results, 'migration');
    }

    /**
     * Obter o último batch de migrations
     * 
     * @return int
     */
    public function getLastBatchNumber(): int
    {
        $result = $this->builder
            ->table($this->table)
            ->selectRaw('MAX(batch) as last_batch')
            ->first();

        return $result ? (int) $result->last_batch : 0;
    }

    /**
     * Obter migrations por batch
     * 
     * @param int $batch Número do batch
     * @return array
     */
    public function getMigrationsByBatch(int $batch): array
    {
        $results = $this->builder
            ->table($this->table)
            ->select('migration')
            ->where('batch', $batch)
            ->orderBy('id', 'asc')
            ->get();

        return array_column($results, 'migration');
    }

    /**
     * Obter todas as migrations com seus batches
     * 
     * @return array
     */
    public function getMigrationBatches(): array
    {
        $results = $this->builder
            ->table($this->table)
            ->select(['migration', 'batch'])
            ->orderBy('id', 'asc')
            ->get();

        $batches = [];
        foreach ($results as $row) {
            $batches[$row->migration] = (int) $row->batch;
        }

        return $batches;
    }

    /**
     * Registrar que uma migration foi aplicada
     * 
     * @param string $migration Nome da migration
     * @param int $batch Número do batch
     * @return bool
     */
    public function log(string $migration, int $batch): bool
    {
        return $this->builder
            ->table($this->table)
            ->insert([
                'migration' => $migration,
                'batch' => $batch,
                'applied_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Remover registro de uma migration (rollback)
     * 
     * @param string $migration Nome da migration
     * @return bool
     */
    public function delete(string $migration): bool
    {
        return $this->builder
            ->table($this->table)
            ->where('migration', $migration)
            ->delete() > 0;
    }

    /**
     * Remover todas as migrations de um batch
     * 
     * @param int $batch Número do batch
     * @return int Número de registros removidos
     */
    public function deleteBatch(int $batch): int
    {
        return $this->builder
            ->table($this->table)
            ->where('batch', $batch)
            ->delete();
    }

    /**
     * Limpar todo o repositório (remover todos os registros)
     * 
     * @return int Número de registros removidos
     */
    public function clear(): int
    {
        return $this->builder
            ->table($this->table)
            ->delete();
    }

    /**
     * Obter o próximo número de batch
     * 
     * @return int
     */
    public function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * Verificar se uma migration específica foi aplicada
     * 
     * @param string $migration Nome da migration
     * @return bool
     */
    public function hasBeenRun(string $migration): bool
    {
        $result = $this->builder
            ->table($this->table)
            ->select('id')
            ->where('migration', $migration)
            ->first();

        return $result !== null;
    }

    /**
     * Obter o batch de uma migration específica
     * 
     * @param string $migration Nome da migration
     * @return int|null Número do batch ou null se não encontrada
     */
    public function getBatchForMigration(string $migration): ?int
    {
        $result = $this->builder
            ->table($this->table)
            ->select('batch')
            ->where('migration', $migration)
            ->first();

        return $result ? (int) $result->batch : null;
    }

    /**
     * Obter todas as migrations pendentes
     * 
     * @param array $available Migrations disponíveis
     * @return array
     */
    public function getPending(array $available): array
    {
        $ran = $this->getRan();
        return array_diff($available, $ran);
    }

    /**
     * Obter migrations que precisam ser revertidas
     * 
     * @param int $steps Número de batches para reverter
     * @return array
     */
    public function getMigrationsToRollback(int $steps = 1): array
    {
        $lastBatch = $this->getLastBatchNumber();
        $targetBatch = max(0, $lastBatch - $steps);
        
        $results = $this->builder
            ->table($this->table)
            ->select('migration')
            ->where('batch', '>', $targetBatch)
            ->orderBy('batch', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return array_column($results, 'migration');
    }

    /**
     * Obter migrations que precisam ser resetadas (todas)
     * 
     * @return array
     */
    public function getMigrationsToReset(): array
    {
        $results = $this->builder
            ->table($this->table)
            ->select('migration')
            ->orderBy('batch', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return array_column($results, 'migration');
    }

    /**
     * Obter estatísticas das migrations
     * 
     * @return array
     */
    public function getStats(): array
    {
        $total = $this->builder
            ->table($this->table)
            ->selectRaw('COUNT(*) as total')
            ->first();

        $batches = $this->builder
            ->table($this->table)
            ->selectRaw('COUNT(DISTINCT batch) as batches')
            ->first();

        $latest = $this->builder
            ->table($this->table)
            ->selectRaw('MAX(applied_at) as latest')
            ->first();

        return [
            'total' => $total ? (int) $total->total : 0,
            'batches' => $batches ? (int) $batches->batches : 0,
            'latest' => $latest ? $latest->latest : null,
            'last_batch' => $this->getLastBatchNumber()
        ];
    }

    /**
     * Obter nome da tabela de migrations
     * 
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Definir nome da tabela de migrations
     * 
     * @param string $table
     * @return self
     */
    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Obter conexão com o banco de dados
     * 
     * @return \Coyote\Database\Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }
}