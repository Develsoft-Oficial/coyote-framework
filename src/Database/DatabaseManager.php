<?php

namespace Coyote\Database;

use Coyote\Core\Config;
use Coyote\Core\Exceptions\DatabaseException;

/**
 * Database Manager
 * 
 * Gerencia múltiplas conexões de banco de dados
 */
class DatabaseManager
{
    /**
     * Conexões ativas
     * 
     * @var array<string, Connection>
     */
    protected $connections = [];

    /**
     * Configurações de conexão
     * 
     * @var array
     */
    protected $config;

    /**
     * Conexão padrão
     * 
     * @var string
     */
    protected $defaultConnection;

    /**
     * Construtor
     * 
     * @param array $config Configurações de banco de dados
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->defaultConnection = $config['default'] ?? 'default';
    }

    /**
     * Obtém uma conexão
     * 
     * @param string|null $name Nome da conexão
     * @return Connection
     * @throws DatabaseException
     */
    public function connection(?string $name = null): Connection
    {
        $name = $name ?: $this->defaultConnection;

        // Se a conexão já existe, retorna
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        // Verifica se a configuração existe
        if (!isset($this->config['connections'][$name])) {
            throw new DatabaseException("Configuração de conexão '{$name}' não encontrada.");
        }

        // Cria a conexão
        $config = $this->config['connections'][$name];
        $connection = new Connection($name, $config);

        // Armazena a conexão
        $this->connections[$name] = $connection;

        return $connection;
    }

    /**
     * Define a conexão padrão
     * 
     * @param string $name Nome da conexão
     * @return self
     */
    public function setDefaultConnection(string $name): self
    {
        $this->defaultConnection = $name;
        return $this;
    }

    /**
     * Obtém a conexão padrão
     * 
     * @return Connection
     */
    public function getDefaultConnection(): Connection
    {
        return $this->connection();
    }

    /**
     * Obtém o PDO da conexão padrão
     * 
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->connection()->getPdo();
    }

    /**
     * Executa uma query na conexão padrão
     * 
     * @param string $sql Query SQL
     * @param array $bindings Parâmetros para bind
     * @return \PDOStatement
     */
    public function query(string $sql, array $bindings = []): \PDOStatement
    {
        return $this->connection()->query($sql, $bindings);
    }

    /**
     * Executa uma query e retorna todos os resultados
     * 
     * @param string $sql Query SQL
     * @param array $bindings Parâmetros para bind
     * @param int $fetchMode Modo de fetch
     * @return array
     */
    public function fetchAll(string $sql, array $bindings = [], int $fetchMode = \PDO::FETCH_ASSOC): array
    {
        return $this->connection()->fetchAll($sql, $bindings, $fetchMode);
    }

    /**
     * Executa uma query e retorna o primeiro resultado
     * 
     * @param string $sql Query SQL
     * @param array $bindings Parâmetros para bind
     * @param int $fetchMode Modo de fetch
     * @return mixed
     */
    public function fetchOne(string $sql, array $bindings = [], int $fetchMode = \PDO::FETCH_ASSOC)
    {
        return $this->connection()->fetchOne($sql, $bindings, $fetchMode);
    }

    /**
     * Executa uma query de inserção/atualização/exclusão
     * 
     * @param string $sql Query SQL
     * @param array $bindings Parâmetros para bind
     * @return int Número de linhas afetadas
     */
    public function execute(string $sql, array $bindings = []): int
    {
        return $this->connection()->execute($sql, $bindings);
    }

    /**
     * Obtém o último ID inserido
     * 
     * @param string|null $name Nome da sequência (para PostgreSQL)
     * @return string
     */
    public function lastInsertId(?string $name = null): string
    {
        return $this->connection()->lastInsertId($name);
    }

    /**
     * Inicia uma transação
     * 
     * @param string|null $connectionName Nome da conexão
     * @return bool
     */
    public function beginTransaction(?string $connectionName = null): bool
    {
        return $this->connection($connectionName)->beginTransaction();
    }

    /**
     * Confirma uma transação
     * 
     * @param string|null $connectionName Nome da conexão
     * @return bool
     */
    public function commit(?string $connectionName = null): bool
    {
        return $this->connection($connectionName)->commit();
    }

    /**
     * Reverte uma transação
     * 
     * @param string|null $connectionName Nome da conexão
     * @return bool
     */
    public function rollback(?string $connectionName = null): bool
    {
        return $this->connection($connectionName)->rollback();
    }

    /**
     * Executa uma função dentro de uma transação
     * 
     * @param callable $callback Função a ser executada
     * @param string|null $connectionName Nome da conexão
     * @return mixed
     * @throws \Throwable
     */
    public function transaction(callable $callback, ?string $connectionName = null)
    {
        $connection = $this->connection($connectionName);
        
        $connection->beginTransaction();

        try {
            $result = $callback($connection);
            $connection->commit();
            return $result;
        } catch (\Throwable $e) {
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * Verifica se uma conexão existe
     * 
     * @param string $name Nome da conexão
     * @return bool
     */
    public function hasConnection(string $name): bool
    {
        return isset($this->config['connections'][$name]);
    }

    /**
     * Obtém os nomes das conexões disponíveis
     * 
     * @return array
     */
    public function getConnectionNames(): array
    {
        return array_keys($this->config['connections'] ?? []);
    }

    /**
     * Desconecta uma conexão
     * 
     * @param string $name Nome da conexão
     * @return void
     */
    public function disconnect(string $name): void
    {
        if (isset($this->connections[$name])) {
            $this->connections[$name]->disconnect();
            unset($this->connections[$name]);
        }
    }

    /**
     * Desconecta todas as conexões
     * 
     * @return void
     */
    public function disconnectAll(): void
    {
        foreach ($this->connections as $connection) {
            $connection->disconnect();
        }
        
        $this->connections = [];
    }

    /**
     * Obtém estatísticas das conexões
     * 
     * @return array
     */
    public function getStats(): array
    {
        $stats = [
            'total_connections' => count($this->connections),
            'connections' => [],
        ];

        foreach ($this->connections as $name => $connection) {
            $stats['connections'][$name] = [
                'connected' => $connection->isConnected(),
                'driver' => $connection->getDriver(),
                'in_transaction' => $connection->inTransaction(),
            ];
        }

        return $stats;
    }

    /**
     * Obtém a configuração
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Define a configuração
     * 
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        
        if (isset($config['default'])) {
            $this->defaultConnection = $config['default'];
        }
        
        return $this;
    }

    /**
     * Adiciona uma nova configuração de conexão
     * 
     * @param string $name Nome da conexão
     * @param array $config Configuração
     * @return self
     */
    public function addConnection(string $name, array $config): self
    {
        $this->config['connections'][$name] = $config;
        
        // Se for a primeira conexão, define como padrão
        if (count($this->config['connections']) === 1) {
            $this->defaultConnection = $name;
        }
        
        return $this;
    }

    /**
     * Remove uma configuração de conexão
     * 
     * @param string $name Nome da conexão
     * @return self
     */
    public function removeConnection(string $name): self
    {
        // Desconecta se estiver ativa
        $this->disconnect($name);
        
        // Remove a configuração
        unset($this->config['connections'][$name]);
        
        // Se era a conexão padrão, redefine
        if ($name === $this->defaultConnection && !empty($this->config['connections'])) {
            $this->defaultConnection = array_key_first($this->config['connections']);
        }
        
        return $this;
    }
}