<?php

namespace Coyote\Database;

use Coyote\Core\Exceptions\DatabaseException;

/**
 * Query Builder
 * 
 * Construtor fluente de queries SQL
 */
class QueryBuilder
{
    /**
     * Database Connection
     *
     * @var Connection
     */
    protected $db;

    /**
     * Nome da tabela
     * 
     * @var string
     */
    protected $table;

    /**
     * Colunas para seleção
     * 
     * @var array
     */
    protected $columns = ['*'];

    /**
     * Cláusula WHERE
     * 
     * @var array
     */
    protected $wheres = [];

    /**
     * Cláusula ORDER BY
     * 
     * @var array
     */
    protected $orders = [];

    /**
     * Cláusula GROUP BY
     * 
     * @var array
     */
    protected $groups = [];

    /**
     * Cláusula HAVING
     * 
     * @var array
     */
    protected $havings = [];

    /**
     * Limite de resultados
     * 
     * @var int|null
     */
    protected $limit;

    /**
     * Offset para paginação
     * 
     * @var int|null
     */
    protected $offset;

    /**
     * Joins
     * 
     * @var array
     */
    protected $joins = [];

    /**
     * Bindings
     * 
     * @var array
     */
    protected $bindings = [
        'select' => [],
        'where' => [],
        'having' => [],
        'order' => [],
        'join' => [],
        'insert' => [],
        'update' => [],
    ];

    /**
     * Tipo de query
     * 
     * @var string
     */
    protected $type = 'select';

    /**
     * Valores para INSERT/UPDATE
     * 
     * @var array
     */
    protected $values = [];

    /**
     * Construtor
     *
     * @param Connection $db Database Connection
     * @param string|null $table Nome da tabela
     */
    public function __construct(Connection $db, ?string $table = null)
    {
        $this->db = $db;
        
        if ($table !== null) {
            $this->table($table);
        }
    }

    /**
     * Define a tabela
     * 
     * @param string $table Nome da tabela
     * @return self
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Define as colunas para seleção
     * 
     * @param array|string $columns Colunas
     * @return self
     */
    public function select($columns = ['*']): self
    {
        $this->type = 'select';
        
        if (is_string($columns)) {
            $columns = func_get_args();
        }
        
        $this->columns = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    /**
     * Adiciona uma cláusula WHERE básica
     * 
     * @param string $column Coluna
     * @param mixed $operator Operador ou valor
     * @param mixed $value Valor (se operator não for operador)
     * @param string $boolean AND ou OR
     * @return self
     */
    public function where(string $column, $operator = null, $value = null, string $boolean = 'AND'): self
    {
        // Se apenas 2 argumentos foram passados, assume-se operador '='
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean,
        ];

        $this->addBinding($value, 'where');

        return $this;
    }

    /**
     * Adiciona uma cláusula WHERE OR
     * 
     * @param string $column Coluna
     * @param mixed $operator Operador ou valor
     * @param mixed $value Valor (se operator não for operador)
     * @return self
     */
    public function orWhere(string $column, $operator = null, $value = null): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Adiciona uma cláusula WHERE IN
     * 
     * @param string $column Coluna
     * @param array $values Valores
     * @param string $boolean AND ou OR
     * @param bool $not NOT IN
     * @return self
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND', bool $not = false): self
    {
        $type = $not ? 'NotIn' : 'In';

        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
        ];

        foreach ($values as $value) {
            $this->addBinding($value, 'where');
        }

        return $this;
    }

    /**
     * Adiciona uma cláusula WHERE NOT IN
     * 
     * @param string $column Coluna
     * @param array $values Valores
     * @param string $boolean AND ou OR
     * @return self
     */
    public function whereNotIn(string $column, array $values, string $boolean = 'AND'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Adiciona uma cláusula WHERE NULL
     * 
     * @param string $column Coluna
     * @param string $boolean AND ou OR
     * @param bool $not NOT NULL
     * @return self
     */
    public function whereNull(string $column, string $boolean = 'AND', bool $not = false): self
    {
        $type = $not ? 'NotNull' : 'Null';

        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'boolean' => $boolean,
        ];

        return $this;
    }

    /**
     * Adiciona uma cláusula WHERE NOT NULL
     * 
     * @param string $column Coluna
     * @param string $boolean AND ou OR
     * @return self
     */
    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * Adiciona uma cláusula ORDER BY
     * 
     * @param string $column Coluna
     * @param string $direction ASC ou DESC
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtoupper($direction),
        ];

        return $this;
    }

    /**
     * Adiciona uma cláusula GROUP BY
     * 
     * @param string|array $columns Colunas
     * @return self
     */
    public function groupBy($columns): self
    {
        if (is_string($columns)) {
            $columns = [$columns];
        }

        $this->groups = array_merge($this->groups, $columns);
        return $this;
    }

    /**
     * Adiciona uma cláusula HAVING
     * 
     * @param string $column Coluna
     * @param mixed $operator Operador ou valor
     * @param mixed $value Valor (se operator não for operador)
     * @param string $boolean AND ou OR
     * @return self
     */
    public function having(string $column, $operator = null, $value = null, string $boolean = 'AND'): self
    {
        // Se apenas 2 argumentos foram passados, assume-se operador '='
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean,
        ];

        $this->addBinding($value, 'having');

        return $this;
    }

    /**
     * Define o limite de resultados
     * 
     * @param int $limit Limite
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Define o offset para paginação
     * 
     * @param int $offset Offset
     * @return self
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Adiciona um JOIN
     * 
     * @param string $table Tabela para join
     * @param string $first Primeira coluna
     * @param string $operator Operador
     * @param string $second Segunda coluna
     * @param string $type Tipo de join (INNER, LEFT, RIGHT)
     * @return self
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
        ];

        return $this;
    }

    /**
     * Adiciona um LEFT JOIN
     * 
     * @param string $table Tabela para join
     * @param string $first Primeira coluna
     * @param string $operator Operador
     * @param string $second Segunda coluna
     * @return self
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Adiciona um RIGHT JOIN
     * 
     * @param string $table Tabela para join
     * @param string $first Primeira coluna
     * @param string $operator Operador
     * @param string $second Segunda coluna
     * @return self
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * Executa a query SELECT e retorna todos os resultados
     * 
     * @return array
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        return $this->db->fetchAll($sql, $bindings);
    }

    /**
     * Executa a query SELECT e retorna o primeiro resultado
     * 
     * @return mixed
     */
    public function first()
    {
        $this->limit(1);
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $results = $this->db->fetchAll($sql, $bindings);
        return $results[0] ?? null;
    }

    /**
     * Executa a query SELECT e retorna um valor específico
     * 
     * @param string $column Coluna
     * @return mixed
     */
    public function value(string $column)
    {
        $this->select($column);
        $result = $this->first();
        
        return $result[$column] ?? null;
    }

    /**
     * Executa a query SELECT e retorna os resultados como array associativo
     * 
     * @param string $key Coluna para chave
     * @param string|null $value Coluna para valor
     * @return array
     */
    public function pluck(string $key, ?string $value = null): array
    {
        if ($value === null) {
            $this->select($key);
            $results = $this->get();
            
            return array_column($results, $key);
        }
        
        $this->select([$key, $value]);
        $results = $this->get();
        
        $plucked = [];
        foreach ($results as $row) {
            $plucked[$row[$key]] = $row[$value];
        }
        
        return $plucked;
    }

    /**
     * Conta o número de registros
     * 
     * @param string $column Coluna para contar (default: *)
     * @return int
     */
    public function count(string $column = '*'): int
    {
        $this->selectRaw("COUNT({$column}) as aggregate");
        $result = $this->first();
        
        return (int) ($result['aggregate'] ?? 0);
    }

    /**
     * Soma os valores de uma coluna
     * 
     * @param string $column Coluna
     * @return float
     */
    public function sum(string $column): float
    {
        $this->selectRaw("SUM({$column}) as aggregate");
        $result = $this->first();
        
        return (float) ($result['aggregate'] ?? 0);
    }

    /**
     * Média dos valores de uma coluna
     * 
     * @param string $column Coluna
     * @return float
     */
    public function avg(string $column): float
    {
        $this->selectRaw("AVG({$column}) as aggregate");
        $result = $this->first();
        
        return (float) ($result['aggregate'] ?? 0);
    }

    /**
     * Valor máximo de uma coluna
     * 
     * @param string $column Coluna
     * @return mixed
     */
    public function max(string $column)
    {
        $this->selectRaw("MAX({$column}) as aggregate");
        $result = $this->first();
        
        return $result['aggregate'] ?? null;
    }

    /**
     * Valor mínimo de uma coluna
     * 
     * @param string $column Coluna
     * @return mixed
     */
    public function min(string $column)
    {
        $this->selectRaw("MIN({$column}) as aggregate");
        $result = $this->first();
        
        return $result['aggregate'] ?? null;
    }

    /**
     * Insere dados na tabela
     * 
     * @param array $values Valores
     * @return bool
     */
    public function insert(array $values): bool
    {
        $this->type = 'insert';
        $this->values = $values;
        
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $affected = $this->db->execute($sql, $bindings);
        return $affected > 0;
    }

    /**
     * Insere dados e retorna o ID inserido
     * 
     * @param array $values Valores
     * @return string
     */
    public function insertGetId(array $values): string
    {
        $this->insert($values);
        return $this->db->lastInsertId();
    }

    /**
     * Atualiza dados na tabela
     * 
     * @param array $values Valores
     * @return int Número de linhas afetadas
     */
    public function update(array $values): int
    {
        $this->type = 'update';
        $this->values = $values;
        
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        return $this->db->execute($sql, $bindings);
    }

    /**
     * Exclui dados da tabela
     * 
     * @return int Número de linhas afetadas
     */
    public function delete(): int
    {
        $this->type = 'delete';
        
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        return $this->db->execute($sql, $bindings);
    }

    /**
     * Executa uma query SQL bruta (statement)
     *
     * @param string $sql SQL
     * @param array $bindings Bindings
     * @return mixed Resultado da execução
     */
    public function statement(string $sql, array $bindings = [])
    {
        try {
            // Para queries que retornam resultados (SELECT, SHOW, etc.)
            if (preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)/i', $sql)) {
                return $this->db->fetchAll($sql, $bindings);
            }
            
            // Para queries que não retornam resultados (INSERT, UPDATE, DELETE, CREATE, etc.)
            $result = $this->db->execute($sql, $bindings);
            return $result !== false ? $result : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Executa uma query SQL bruta
     *
     * @param string $sql SQL
     * @param array $bindings Bindings
     * @return array
     */
    public function raw(string $sql, array $bindings = []): array
    {
        return $this->db->fetchAll($sql, $bindings);
    }

    /**
     * Define uma coluna SQL bruta
     * 
     * @param string $expression Expressão SQL
     * @return self
     */
    public function selectRaw(string $expression): self
    {
        $this->columns = [$expression];
        return $this;
    }

    /**
     * Gera a SQL da query
     * 
     * @return string
     */
    public function toSql(): string
    {
        switch ($this->type) {
            case 'select':
                return $this->compileSelect();
            case 'insert':
                return $this->compileInsert();
            case 'update':
                return $this->compileUpdate();
            case 'delete':
                return $this->compileDelete();
            default:
                throw new DatabaseException("Tipo de query não suportado: {$this->type}");
        }
    }

    /**
     * Compila a query SELECT
     *
     * @return string
     */
    protected function compileSelect(): string
    {
        $sql = 'SELECT ';
        
        // Compilar colunas
        $sql .= $this->compileColumns();
        
        // Compilar FROM
        $sql .= ' FROM ' . $this->wrapTable($this->table);
        
        // Compilar JOINs
        if (!empty($this->joins)) {
            $sql .= ' ' . $this->compileJoins();
        }
        
        // Compilar WHERE
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        // Compilar GROUP BY
        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . $this->compileGroups();
        }
        
        // Compilar HAVING
        if (!empty($this->havings)) {
            $sql .= ' HAVING ' . $this->compileHavings();
        }
        
        // Compilar ORDER BY
        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . $this->compileOrders();
        }
        
        // Compilar LIMIT e OFFSET
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
            
            if ($this->offset !== null) {
                $sql .= ' OFFSET ' . $this->offset;
            }
        }
        
        return $sql;
    }
    
    /**
     * Compila a query INSERT
     *
     * @return string
     */
    protected function compileInsert(): string
    {
        if (empty($this->values)) {
            throw new DatabaseException('Nenhum valor especificado para INSERT.');
        }
        
        $columns = array_keys($this->values);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = 'INSERT INTO ' . $this->wrapTable($this->table);
        $sql .= ' (' . implode(', ', array_map([$this, 'wrapColumn'], $columns)) . ')';
        $sql .= ' VALUES (' . implode(', ', $placeholders) . ')';
        
        return $sql;
    }
    
    /**
     * Compila a query UPDATE
     *
     * @return string
     */
    protected function compileUpdate(): string
    {
        if (empty($this->values)) {
            throw new DatabaseException('Nenhum valor especificado para UPDATE.');
        }
        
        $sets = [];
        foreach ($this->values as $column => $value) {
            $sets[] = $this->wrapColumn($column) . ' = ?';
        }
        
        $sql = 'UPDATE ' . $this->wrapTable($this->table);
        $sql .= ' SET ' . implode(', ', $sets);
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        return $sql;
    }
    
    /**
     * Compila a query DELETE
     *
     * @return string
     */
    protected function compileDelete(): string
    {
        $sql = 'DELETE FROM ' . $this->wrapTable($this->table);
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        return $sql;
    }
    
    /**
     * Compila as colunas para SELECT
     *
     * @return string
     */
    protected function compileColumns(): string
    {
        if ($this->columns === ['*']) {
            return '*';
        }
        
        return implode(', ', array_map([$this, 'wrapColumn'], $this->columns));
    }
    
    /**
     * Compila as cláusulas WHERE
     *
     * @return string
     */
    protected function compileWheres(): string
    {
        $whereClauses = [];
        
        foreach ($this->wheres as $index => $where) {
            $boolean = $index > 0 ? ' ' . $where['boolean'] . ' ' : '';
            
            switch ($where['type']) {
                case 'basic':
                    $whereClauses[] = $boolean . $this->compileBasicWhere($where);
                    break;
                case 'in':
                    $whereClauses[] = $boolean . $this->compileInWhere($where);
                    break;
                case 'null':
                    $whereClauses[] = $boolean . $this->compileNullWhere($where);
                    break;
                case 'raw':
                    $whereClauses[] = $boolean . $where['sql'];
                    break;
            }
        }
        
        return ltrim(implode('', $whereClauses), 'AND OR ');
    }
    
    /**
     * Compila uma cláusula WHERE básica
     *
     * @param array $where
     * @return string
     */
    protected function compileBasicWhere(array $where): string
    {
        return $this->wrapColumn($where['column']) . ' ' . $where['operator'] . ' ?';
    }
    
    /**
     * Compila uma cláusula WHERE IN
     *
     * @param array $where
     * @return string
     */
    protected function compileInWhere(array $where): string
    {
        $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
        $not = $where['not'] ? 'NOT ' : '';
        
        return $this->wrapColumn($where['column']) . ' ' . $not . 'IN (' . $placeholders . ')';
    }
    
    /**
     * Compila uma cláusula WHERE NULL
     *
     * @param array $where
     * @return string
     */
    protected function compileNullWhere(array $where): string
    {
        $not = $where['not'] ? 'NOT ' : '';
        return $this->wrapColumn($where['column']) . ' IS ' . $not . 'NULL';
    }
    
    /**
     * Compila as cláusulas JOIN
     *
     * @return string
     */
    protected function compileJoins(): string
    {
        $joins = [];
        
        foreach ($this->joins as $join) {
            $joins[] = $join['type'] . ' JOIN ' . $this->wrapTable($join['table']) .
                      ' ON ' . $this->wrapColumn($join['first']) . ' ' .
                      $join['operator'] . ' ' . $this->wrapColumn($join['second']);
        }
        
        return implode(' ', $joins);
    }
    
    /**
     * Compila as cláusulas GROUP BY
     *
     * @return string
     */
    protected function compileGroups(): string
    {
        return implode(', ', array_map([$this, 'wrapColumn'], $this->groups));
    }
    
    /**
     * Compila as cláusulas HAVING
     *
     * @return string
     */
    protected function compileHavings(): string
    {
        // Implementação simplificada - similar ao WHERE
        $havings = [];
        
        foreach ($this->havings as $index => $having) {
            $boolean = $index > 0 ? ' ' . $having['boolean'] . ' ' : '';
            $havings[] = $boolean . $this->wrapColumn($having['column']) . ' ' .
                        $having['operator'] . ' ?';
        }
        
        return ltrim(implode('', $havings), 'AND OR ');
    }
    
    /**
     * Compila as cláusulas ORDER BY
     *
     * @return string
     */
    protected function compileOrders(): string
    {
        $orders = [];
        
        foreach ($this->orders as $order) {
            $direction = isset($order['direction']) ? ' ' . strtoupper($order['direction']) : '';
            $orders[] = $this->wrapColumn($order['column']) . $direction;
        }
        
        return implode(', ', $orders);
    }
    
    /**
     * Envolve um nome de tabela com caracteres de escape
     *
     * @param string $table
     * @return string
     */
    protected function wrapTable(string $table): string
    {
        // Implementação básica - pode ser estendida para diferentes bancos
        return '`' . str_replace('`', '``', $table) . '`';
    }
    
    /**
     * Envolve um nome de coluna com caracteres de escape
     *
     * @param string $column
     * @return string
     */
    protected function wrapColumn(string $column): string
    {
        // Se a coluna contiver um alias (AS) ou função, não envolver
        if (stripos($column, ' as ') !== false || preg_match('/[()]/', $column)) {
            return $column;
        }
        
        // Separar por pontos para tabela.coluna
        $parts = explode('.', $column);
        $wrapped = [];
        
        foreach ($parts as $part) {
            $wrapped[] = '`' . str_replace('`', '``', $part) . '`';
        }
        
        return implode('.', $wrapped);
    }
    
    /**
     * Adiciona um binding à query
     *
     * @param mixed $value Valor do binding
     * @param string $type Tipo de binding (where, having, join, etc.)
     * @return void
     */
    protected function addBinding($value, string $type = 'where'): void
    {
        if (!isset($this->bindings[$type])) {
            $this->bindings[$type] = [];
        }
        
        $this->bindings[$type][] = $value;
    }
    
    /**
     * Obtém todos os bindings da query
     *
     * @return array
     */
    public function getBindings(): array
    {
        $bindings = [];
        
        // Bindings para SELECT
        if ($this->type === 'select') {
            // WHERE bindings
            foreach ($this->wheres as $where) {
                if (in_array($where['type'], ['basic', 'in'])) {
                    if ($where['type'] === 'basic') {
                        $bindings[] = $where['value'];
                    } elseif ($where['type'] === 'in') {
                        $bindings = array_merge($bindings, $where['values']);
                    }
                }
            }
            
            // HAVING bindings
            foreach ($this->havings as $having) {
                $bindings[] = $having['value'];
            }
            
            // JOIN bindings
            foreach ($this->joins as $join) {
                foreach ($join['bindings'] as $binding) {
                    $bindings[] = $binding;
                }
            }
        }
        
        // Bindings para INSERT
        if ($this->type === 'insert') {
            $bindings = array_values($this->values);
        }
        
        // Bindings para UPDATE
        if ($this->type === 'update') {
            $bindings = array_values($this->values);
            
            // Adicionar WHERE bindings
            foreach ($this->wheres as $where) {
                if ($where['type'] === 'basic') {
                    $bindings[] = $where['value'];
                }
            }
        }
        
        // Bindings para DELETE
        if ($this->type === 'delete') {
            foreach ($this->wheres as $where) {
                if ($where['type'] === 'basic') {
                    $bindings[] = $where['value'];
                }
            }
        }
        
        return $bindings;
    }
}