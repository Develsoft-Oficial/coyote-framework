<?php

namespace Coyote\Database;

use Coyote\Core\Exceptions\DatabaseException;
use ArrayAccess;
use JsonSerializable;

/**
 * Model Base Class
 * 
 * Classe base para Object-Relational Mapping (ORM)
 */
abstract class Model implements ArrayAccess, JsonSerializable
{
    /**
     * Nome da tabela associada ao modelo
     * 
     * @var string
     */
    protected $table;

    /**
     * Chave primária da tabela
     * 
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Atributos que podem ser preenchidos em massa
     * 
     * @var array
     */
    protected $fillable = [];

    /**
     * Atributos que devem ser ocultados em arrays/JSON
     * 
     * @var array
     */
    protected $hidden = [];

    /**
     * Atributos que devem ser convertidos para tipos nativos
     * 
     * @var array
     */
    protected $casts = [];

    /**
     * Indica se o modelo deve ter timestamps automáticos
     * 
     * @var bool
     */
    public $timestamps = true;

    /**
     * Nome da coluna para created_at
     * 
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * Nome da coluna para updated_at
     * 
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Atributos do modelo
     * 
     * @var array
     */
    protected $attributes = [];

    /**
     * Atributos originais do modelo
     * 
     * @var array
     */
    protected $original = [];

    /**
     * Atributos que foram modificados
     * 
     * @var array
     */
    protected $changes = [];

    /**
     * Indica se o modelo existe no banco de dados
     * 
     * @var bool
     */
    protected $exists = false;

    /**
     * Database Manager
     * 
     * @var DatabaseManager
     */
    protected static $db;

    /**
     * Boot do modelo
     * 
     * @var bool
     */
    protected static $booted = [];

    /**
     * Construtor
     * 
     * @param array $attributes Atributos iniciais
     */
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        
        $this->fill($attributes);
    }

    /**
     * Inicializa o modelo se não foi inicializado
     * 
     * @return void
     */
    protected function bootIfNotBooted(): void
    {
        $class = static::class;

        if (!isset(static::$booted[$class])) {
            static::$booted[$class] = true;
            static::boot();
        }
    }

    /**
     * Método de inicialização do modelo
     * 
     * @return void
     */
    protected static function boot(): void
    {
        // Pode ser sobrescrito por modelos filhos
    }

    /**
     * Preenche o modelo com atributos
     * 
     * @param array $attributes Atributos
     * @return self
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * Verifica se um atributo é preenchível
     * 
     * @param string $key Nome do atributo
     * @return bool
     */
    public function isFillable(string $key): bool
    {
        if (in_array($key, $this->fillable)) {
            return true;
        }

        return empty($this->fillable) && !str_starts_with($key, '_');
    }

    /**
     * Define um atributo
     * 
     * @param string $key Nome do atributo
     * @param mixed $value Valor do atributo
     * @return void
     */
    public function setAttribute(string $key, $value): void
    {
        // Aplica cast se definido
        if (isset($this->casts[$key])) {
            $value = $this->castAttribute($key, $value);
        }

        $this->attributes[$key] = $value;
        $this->changes[$key] = $value;
    }

    /**
     * Obtém um atributo
     * 
     * @param string $key Nome do atributo
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        // Verifica se é um método de relação
        if (method_exists($this, $key)) {
            return $this->$key();
        }

        return null;
    }

    /**
     * Converte um atributo conforme o cast definido
     * 
     * @param string $key Nome do atributo
     * @param mixed $value Valor do atributo
     * @return mixed
     */
    protected function castAttribute(string $key, $value)
    {
        $castType = $this->casts[$key];

        switch ($castType) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'json':
                return is_array($value) ? $value : json_decode($value, true);
            case 'datetime':
            case 'date':
                // Implementação simplificada
                return $value;
            default:
                return $value;
        }
    }

    /**
     * Obtém todos os atributos do modelo
     * 
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Obtém os atributos modificados
     * 
     * @return array
     */
    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Verifica se o modelo foi modificado
     * 
     * @return bool
     */
    public function isDirty(): bool
    {
        return !empty($this->getDirty());
    }

    /**
     * Obtém o nome da tabela do modelo
     * 
     * @return string
     */
    public function getTable(): string
    {
        if (isset($this->table)) {
            return $this->table;
        }

        // Deriva o nome da tabela do nome da classe
        $class = static::class;
        $class = basename(str_replace('\\', '/', $class));
        
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
    }

    /**
     * Obtém a chave primária do modelo
     * 
     * @return string
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Obtém o valor da chave primária
     * 
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Define o Database Manager
     * 
     * @param DatabaseManager $db
     * @return void
     */
    public static function setDatabaseManager(DatabaseManager $db): void
    {
        static::$db = $db;
    }

    /**
     * Obtém o Database Manager
     * 
     * @return DatabaseManager
     * @throws DatabaseException
     */
    protected static function getDatabaseManager(): DatabaseManager
    {
        if (!static::$db) {
            // Tenta obter do container ou cria um padrão
            throw new DatabaseException('Database Manager não configurado para o Model.');
        }

        return static::$db;
    }

    /**
     * Cria uma nova instância do QueryBuilder para este modelo
     *
     * @return QueryBuilder
     */
    public function newQuery(): QueryBuilder
    {
        return new QueryBuilder(static::getDatabaseManager()->connection(), $this->getTable());
    }

    /**
     * Obtém todos os registros da tabela
     * 
     * @return ModelCollection
     */
    public static function all(): ModelCollection
    {
        $instance = new static();
        $results = $instance->newQuery()->get();
        
        return $instance->newCollection($results);
    }

    /**
     * Encontra um modelo pelo ID
     * 
     * @param mixed $id ID do modelo
     * @return static|null
     */
    public static function find($id): ?static
    {
        $instance = new static();
        
        $result = $instance->newQuery()
            ->where($instance->getKeyName(), $id)
            ->first();
        
        if ($result) {
            return $instance->newFromBuilder($result);
        }
        
        return null;
    }

    /**
     * Encontra um modelo pelo ID ou lança uma exceção
     * 
     * @param mixed $id ID do modelo
     * @return static
     * @throws DatabaseException
     */
    public static function findOrFail($id): static
    {
        $model = static::find($id);
        
        if (!$model) {
            throw new DatabaseException("Modelo não encontrado com ID: {$id}");
        }
        
        return $model;
    }

    /**
     * Cria um novo modelo e salva no banco de dados
     * 
     * @param array $attributes Atributos do modelo
     * @return static
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        
        return $model;
    }

    /**
     * Salva o modelo no banco de dados
     * 
     * @return bool
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->performUpdate();
        }
        
        return $this->performInsert();
    }

    /**
     * Executa uma inserção no banco de dados
     * 
     * @return bool
     */
    protected function performInsert(): bool
    {
        $attributes = $this->getAttributes();
        
        // Adiciona timestamps se habilitado
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $attributes[static::CREATED_AT] = $now;
            $attributes[static::UPDATED_AT] = $now;
        }
        
        $query = $this->newQuery();
        $result = $query->insert($attributes);
        
        if ($result) {
            $this->exists = true;
            
            // Obtém o ID inserido
            $id = $query->getLastInsertId();
            if ($id) {
                $this->setAttribute($this->getKeyName(), $id);
            }
            
            $this->syncOriginal();
            
            return true;
        }
        
        return false;
    }

    /**
     * Executa uma atualização no banco de dados
     * 
     * @return bool
     */
    protected function performUpdate(): bool
    {
        if (!$this->isDirty()) {
            return true;
        }
        
        $dirty = $this->getDirty();
        
        // Adiciona timestamp de atualização se habilitado
        if ($this->timestamps && !isset($dirty[static::UPDATED_AT])) {
            $dirty[static::UPDATED_AT] = date('Y-m-d H:i:s');
        }
        
        $result = $this->newQuery()
            ->where($this->getKeyName(), $this->getKey())
            ->update($dirty);
        
        if ($result) {
            $this->syncOriginal();
            return true;
        }
        
        return false;
    }

    /**
     * Atualiza os atributos do modelo
     * 
     * @param array $attributes Atributos para atualizar
     * @return bool
     */
    public function update(array $attributes): bool
    {
        $this->fill($attributes);
        return $this->save();
    }

    /**
     * Exclui o modelo do banco de dados
     * 
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $result = $this->newQuery()
            ->where($this->getKeyName(), $this->getKey())
            ->delete();
        
        if ($result) {
            $this->exists = false;
            return true;
        }
        
        return false;
    }

    /**
     * Atualiza os atributos originais com os atuais
     * 
     * @return void
     */
    public function syncOriginal(): void
    {
        $this->original = $this->attributes;
        $this->changes = [];
    }

    /**
     * Cria uma nova instância do modelo a partir de dados do banco
     * 
     * @param array $attributes Atributos do banco
     * @return static
     */
    public function newFromBuilder(array $attributes): static
    {
        $model = new static($attributes);
        $model->exists = true;
        $model->syncOriginal();
        
        return $model;
    }

    /**
     * Cria uma nova coleção de modelos
     * 
     * @param array $models Array de modelos ou dados
     * @return ModelCollection
     */
    public function newCollection(array $models = []): ModelCollection
    {
        return new ModelCollection($models);
    }

    /**
     * Implementação de ArrayAccess: offsetExists
     * 
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Implementação de ArrayAccess: offsetGet
     * 
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Implementação de ArrayAccess: offsetSet
     * 
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Implementação de ArrayAccess: offsetUnset
     * 
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Implementação de JsonSerializable: jsonSerialize
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Converte o modelo para array
     * 
     * @return array
     */
    public function toArray(): array
    {
        $attributes = $this->attributes;
        
        // Remove atributos ocultos
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }
        
        return $attributes;
    }

    /**
     * Método mágico: __get
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Método mágico: __set
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Método mágico: __isset
     * 
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Método mágico: __unset
     * 
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * Método mágico: __toString
     * 
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}