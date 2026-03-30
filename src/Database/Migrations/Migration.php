<?php
// vendors/coyote/Database/Migrations/Migration.php

namespace Coyote\Database\Migrations;

use Coyote\Database\Connection;
use Coyote\Database\DatabaseManager;
use Coyote\Database\QueryBuilder;

/**
 * Classe base para todas as migrations
 * 
 * Cada migration deve estender esta classe e implementar
 * os métodos up() e down().
 */
abstract class Migration
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
     * Schema builder para criação de tabelas
     * 
     * @var \Coyote\Database\Schema\Builder|null
     */
    protected $schema;

    /**
     * Nome da migration (nome do arquivo sem extensão)
     * 
     * @var string
     */
    protected $name;

    /**
     * Construtor da migration
     * 
     * @param \Coyote\Database\Connection $connection
     * @param string $name Nome da migration
     */
    public function __construct(Connection $connection, string $name = '')
    {
        $this->connection = $connection;
        $this->builder = new QueryBuilder($connection);
        $this->name = $name;
        
        // Inicializar schema builder se disponível
        if (class_exists('Coyote\Database\Schema\Builder')) {
            $this->schema = new \Coyote\Database\Schema\Builder($connection);
        }
    }

    /**
     * Executar a migration (aplicar mudanças)
     * 
     * @return void
     */
    abstract public function up();

    /**
     * Reverter a migration (desfazer mudanças)
     * 
     * @return void
     */
    abstract public function down();

    /**
     * Obter nome da migration
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Obter timestamp da migration a partir do nome
     * 
     * @return string|null
     */
    public function getTimestamp(): ?string
    {
        if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_/', $this->name, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Executar a migration dentro de uma transação
     * 
     * @param bool $up Se true executa up(), senão down()
     * @return bool
     */
    public function run(bool $up = true): bool
    {
        try {
            $this->connection->beginTransaction();
            
            if ($up) {
                $this->up();
            } else {
                $this->down();
            }
            
            $this->connection->commit();
            return true;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Criar uma nova tabela
     * 
     * @param string $table Nome da tabela
     * @param callable $callback Callback para definir colunas
     * @return void
     */
    protected function create(string $table, callable $callback): void
    {
        if ($this->schema) {
            $this->schema->create($table, $callback);
        } else {
            // Fallback básico se schema builder não estiver disponível
            $blueprint = new \stdClass();
            $callback($blueprint);
            // Implementação básica seria adicionada aqui
        }
    }

    /**
     * Modificar uma tabela existente
     * 
     * @param string $table Nome da tabela
     * @param callable $callback Callback para modificar colunas
     * @return void
     */
    protected function table(string $table, callable $callback): void
    {
        if ($this->schema) {
            $this->schema->table($table, $callback);
        }
    }

    /**
     * Renomear uma tabela
     * 
     * @param string $from Nome atual da tabela
     * @param string $to Novo nome da tabela
     * @return void
     */
    protected function rename(string $from, string $to): void
    {
        $this->builder->statement("RENAME TABLE `{$from}` TO `{$to}`");
    }

    /**
     * Remover uma tabela
     * 
     * @param string $table Nome da tabela
     * @return void
     */
    protected function drop(string $table): void
    {
        $this->builder->statement("DROP TABLE IF EXISTS `{$table}`");
    }

    /**
     * Remover uma tabela se ela existir
     * 
     * @param string $table Nome da tabela
     * @return void
     */
    protected function dropIfExists(string $table): void
    {
        $this->drop($table);
    }

    /**
     * Executar uma query SQL bruta
     * 
     * @param string $sql Query SQL
     * @param array $bindings Parâmetros para bind
     * @return mixed
     */
    protected function statement(string $sql, array $bindings = [])
    {
        return $this->builder->statement($sql, $bindings);
    }

    /**
     * Executar múltiplas queries SQL
     * 
     * @param string $sql Queries SQL separadas por ponto e vírgula
     * @return void
     */
    protected function statements(string $sql): void
    {
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($queries as $query) {
            if (!empty($query)) {
                $this->statement($query);
            }
        }
    }

    /**
     * Verificar se uma tabela existe
     * 
     * @param string $table Nome da tabela
     * @return bool
     */
    protected function hasTable(string $table): bool
    {
        try {
            $result = $this->builder->statement("SHOW TABLES LIKE '{$table}'");
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar se uma coluna existe em uma tabela
     * 
     * @param string $table Nome da tabela
     * @param string $column Nome da coluna
     * @return bool
     */
    protected function hasColumn(string $table, string $column): bool
    {
        try {
            $result = $this->builder->statement("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
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

    /**
     * Obter query builder
     * 
     * @return \Coyote\Database\QueryBuilder
     */
    public function getBuilder(): QueryBuilder
    {
        return $this->builder;
    }
}