<?php

namespace Coyote\Database;

use PDO;
use PDOException;
use Coyote\Core\Config;
use Coyote\Core\Exceptions\DatabaseException;

/**
 * Database Connection
 * 
 * Gerencia uma conexão PDO com o banco de dados
 */
class Connection
{
    /**
     * Instância PDO
     * 
     * @var PDO|null
     */
    protected $pdo;

    /**
     * Configuração da conexão
     * 
     * @var array
     */
    protected $config;

    /**
     * Nome da conexão
     * 
     * @var string
     */
    protected $name;

    /**
     * Número de transações ativas
     * 
     * @var int
     */
    protected $transactions = 0;

    /**
     * Construtor
     * 
     * @param string $name Nome da conexão
     * @param array $config Configuração da conexão
     */
    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = $this->parseConfig($config);
    }

    /**
     * Conecta ao banco de dados
     * 
     * @return void
     * @throws DatabaseException
     */
    public function connect(): void
    {
        if ($this->pdo !== null) {
            return;
        }

        try {
            $dsn = $this->getDsn();
            $options = $this->getOptions();

            $this->pdo = new PDO(
                $dsn,
                $this->config['username'] ?? null,
                $this->config['password'] ?? null,
                $options
            );

            // Configurar atributos PDO
            $this->configurePdo();
        } catch (PDOException $e) {
            throw new DatabaseException(
                "Falha ao conectar ao banco de dados [{$this->name}]: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Desconecta do banco de dados
     * 
     * @return void
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }

    /**
     * Retorna a instância PDO
     * 
     * @return PDO
     */
    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo;
    }

    /**
     * Executa uma query SQL
     * 
     * @param string $sql Query SQL
     * @param array $bindings Parâmetros para bind
     * @return \PDOStatement
     * @throws DatabaseException
     */
    public function query(string $sql, array $bindings = []): \PDOStatement
    {
        try {
            $statement = $this->getPdo()->prepare($sql);
            
            foreach ($bindings as $key => $value) {
                $this->bindValue($statement, $key, $value);
            }

            $statement->execute();
            return $statement;
        } catch (PDOException $e) {
            // PDOException::getCode() returns string SQLSTATE, need to convert to int
            $code = $e->getCode();
            if (is_string($code) && preg_match('/^\d+$/', $code)) {
                $code = (int) $code;
            } elseif (is_string($code)) {
                // Convert SQLSTATE like 'HY000' to numeric
                $code = 0; // Default to 0 for non-numeric SQLSTATE
            }
            
            throw new DatabaseException(
                "Erro ao executar query [{$this->name}]: " . $e->getMessage() . " | SQL: " . $sql,
                $code,
                $e
            );
        }
    }

    /**
     * Executa uma query e retorna todos os resultados
     * 
     * @param string $sql Query SQL
     * @param array $bindings Parâmetros para bind
     * @param int $fetchMode Modo de fetch
     * @return array
     */
    public function fetchAll(string $sql, array $bindings = [], int $fetchMode = PDO::FETCH_ASSOC): array
    {
        $statement = $this->query($sql, $bindings);
        return $statement->fetchAll($fetchMode);
    }

    /**
     * Executa uma query e retorna o primeiro resultado
     * 
     * @param string $sql Query SQL
     * @param array $bindings Parâmetros para bind
     * @param int $fetchMode Modo de fetch
     * @return mixed
     */
    public function fetchOne(string $sql, array $bindings = [], int $fetchMode = PDO::FETCH_ASSOC)
    {
        $statement = $this->query($sql, $bindings);
        return $statement->fetch($fetchMode);
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
        $statement = $this->query($sql, $bindings);
        return $statement->rowCount();
    }

    /**
     * Obtém o último ID inserido
     * 
     * @param string|null $name Nome da sequência (para PostgreSQL)
     * @return string
     */
    public function lastInsertId(?string $name = null): string
    {
        return $this->getPdo()->lastInsertId($name);
    }

    /**
     * Inicia uma transação
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        if ($this->transactions === 0) {
            $this->getPdo()->beginTransaction();
        }

        $this->transactions++;
        return true;
    }

    /**
     * Confirma uma transação
     * 
     * @return bool
     */
    public function commit(): bool
    {
        if ($this->transactions === 1) {
            $this->getPdo()->commit();
        }

        $this->transactions = max(0, $this->transactions - 1);
        return true;
    }

    /**
     * Reverte uma transação
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        if ($this->transactions === 1) {
            $this->getPdo()->rollBack();
        }

        $this->transactions = max(0, $this->transactions - 1);
        return true;
    }

    /**
     * Verifica se está em transação
     * 
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->transactions > 0;
    }

    /**
     * Obtém o nome da conexão
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * Obtém o driver do banco de dados
     * 
     * @return string
     */
    public function getDriver(): string
    {
        return $this->config['driver'] ?? 'mysql';
    }

    /**
     * Verifica se está conectado
     * 
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }

    /**
     * Parse da configuração
     * 
     * @param array $config
     * @return array
     */
    protected function parseConfig(array $config): array
    {
        $defaults = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => null,
            'database' => '',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => [],
        ];

        return array_merge($defaults, $config);
    }

    /**
     * Gera o DSN para a conexão PDO
     * 
     * @return string
     */
    protected function getDsn(): string
    {
        $driver = $this->config['driver'];
        $host = $this->config['host'];
        $port = $this->config['port'];
        $database = $this->config['database'];
        $charset = $this->config['charset'];

        switch ($driver) {
            case 'mysql':
                $dsn = "mysql:host={$host}";
                if ($port) {
                    $dsn .= ";port={$port}";
                }
                $dsn .= ";dbname={$database};charset={$charset}";
                break;

            case 'pgsql':
                $dsn = "pgsql:host={$host}";
                if ($port) {
                    $dsn .= ";port={$port}";
                }
                $dsn .= ";dbname={$database}";
                break;

            case 'sqlite':
                $dsn = "sqlite:{$database}";
                break;

            case 'sqlsrv':
                $dsn = "sqlsrv:Server={$host}";
                if ($port) {
                    $dsn .= ",{$port}";
                }
                $dsn .= ";Database={$database}";
                break;

            default:
                throw new DatabaseException("Driver de banco de dados não suportado: {$driver}");
        }

        return $dsn;
    }

    /**
     * Obtém as opções PDO
     * 
     * @return array
     */
    protected function getOptions(): array
    {
        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];

        $customOptions = $this->config['options'] ?? [];

        return array_replace($defaultOptions, $customOptions);
    }

    /**
     * Configura atributos adicionais do PDO
     * 
     * @return void
     */
    protected function configurePdo(): void
    {
        if ($this->config['driver'] === 'mysql') {
            // Configurar timezone para UTC
            $this->pdo->exec("SET time_zone = '+00:00'");
            
            // Configurar modo SQL se strict estiver habilitado
            if ($this->config['strict'] ?? true) {
                $this->pdo->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");
            }
        }
    }

    /**
     * Faz bind de um valor em um statement PDO
     * 
     * @param \PDOStatement $statement
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    protected function bindValue(\PDOStatement $statement, $key, $value): void
    {
        $param = is_int($key) ? $key + 1 : $key;
        
        if (is_int($value)) {
            $type = PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            $type = PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            $type = PDO::PARAM_NULL;
        } else {
            $type = PDO::PARAM_STR;
        }

        $statement->bindValue($param, $value, $type);
    }
}