<?php

namespace Coyote\Database;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Model Collection
 * 
 * Coleção de modelos para operações em lote
 */
class ModelCollection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Itens da coleção
     * 
     * @var array
     */
    protected $items = [];

    /**
     * Construtor
     * 
     * @param array $items Itens iniciais
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Cria uma nova coleção
     * 
     * @param array $items Itens da coleção
     * @return static
     */
    public static function make(array $items = []): static
    {
        return new static($items);
    }

    /**
     * Adiciona um item à coleção
     * 
     * @param mixed $item Item a ser adicionado
     * @return self
     */
    public function add($item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Obtém todos os itens da coleção
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Obtém o primeiro item da coleção
     * 
     * @return mixed
     */
    public function first()
    {
        return $this->items[0] ?? null;
    }

    /**
     * Obtém o último item da coleção
     * 
     * @return mixed
     */
    public function last()
    {
        $count = count($this->items);
        
        if ($count === 0) {
            return null;
        }
        
        return $this->items[$count - 1];
    }

    /**
     * Filtra a coleção usando um callback
     * 
     * @param callable $callback Callback de filtro
     * @return static
     */
    public function filter(callable $callback): static
    {
        $filtered = array_filter($this->items, $callback);
        return new static($filtered);
    }

    /**
     * Mapeia a coleção usando um callback
     * 
     * @param callable $callback Callback de mapeamento
     * @return static
     */
    public function map(callable $callback): static
    {
        $mapped = array_map($callback, $this->items);
        return new static($mapped);
    }

    /**
     * Reduz a coleção a um único valor
     * 
     * @param callable $callback Callback de redução
     * @param mixed $initial Valor inicial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Ordena a coleção usando um callback
     * 
     * @param callable $callback Callback de ordenação
     * @return static
     */
    public function sort(callable $callback): static
    {
        $items = $this->items;
        usort($items, $callback);
        
        return new static($items);
    }

    /**
     * Ordena a coleção por uma chave
     * 
     * @param string $key Chave para ordenação
     * @param int $direction Direção (SORT_ASC ou SORT_DESC)
     * @return static
     */
    public function sortBy(string $key, int $direction = SORT_ASC): static
    {
        $items = $this->items;
        
        usort($items, function ($a, $b) use ($key, $direction) {
            $valueA = $this->getItemValue($a, $key);
            $valueB = $this->getItemValue($b, $key);
            
            if ($valueA == $valueB) {
                return 0;
            }
            
            $result = $valueA < $valueB ? -1 : 1;
            
            return $direction === SORT_ASC ? $result : -$result;
        });
        
        return new static($items);
    }

    /**
     * Obtém o valor de um item por chave
     * 
     * @param mixed $item Item
     * @param string $key Chave
     * @return mixed
     */
    protected function getItemValue($item, string $key)
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }
        
        if (is_object($item)) {
            return $item->$key ?? null;
        }
        
        return null;
    }

    /**
     * Agrupa a coleção por uma chave
     * 
     * @param string $key Chave para agrupamento
     * @return static
     */
    public function groupBy(string $key): static
    {
        $groups = [];
        
        foreach ($this->items as $item) {
            $groupKey = $this->getItemValue($item, $key);
            
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [];
            }
            
            $groups[$groupKey][] = $item;
        }
        
        return new static($groups);
    }

    /**
     * Obtém uma coluna específica da coleção
     * 
     * @param string $key Chave da coluna
     * @return array
     */
    public function pluck(string $key): array
    {
        $values = [];
        
        foreach ($this->items as $item) {
            $values[] = $this->getItemValue($item, $key);
        }
        
        return $values;
    }

    /**
     * Obtém valores únicos da coleção
     * 
     * @return static
     */
    public function unique(): static
    {
        $unique = [];
        $seen = [];
        
        foreach ($this->items as $item) {
            $serialized = serialize($item);
            
            if (!in_array($serialized, $seen, true)) {
                $unique[] = $item;
                $seen[] = $serialized;
            }
        }
        
        return new static($unique);
    }

    /**
     * Mescla outra coleção com esta
     * 
     * @param ModelCollection $collection Coleção para mesclar
     * @return static
     */
    public function merge(ModelCollection $collection): static
    {
        $merged = array_merge($this->items, $collection->all());
        return new static($merged);
    }

    /**
     * Divide a coleção em pedaços
     * 
     * @param int $size Tamanho de cada pedaço
     * @return static
     */
    public function chunk(int $size): static
    {
        $chunks = array_chunk($this->items, $size);
        return new static($chunks);
    }

    /**
     * Obtém um slice da coleção
     * 
     * @param int $offset Offset inicial
     * @param int|null $length Comprimento do slice
     * @return static
     */
    public function slice(int $offset, ?int $length = null): static
    {
        $slice = array_slice($this->items, $offset, $length);
        return new static($slice);
    }

    /**
     * Verifica se a coleção está vazia
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Verifica se a coleção não está vazia
     * 
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Conta o número de itens na coleção
     * 
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Converte a coleção para array
     * 
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        
        foreach ($this->items as $item) {
            if ($item instanceof Model) {
                $array[] = $item->toArray();
            } elseif (is_array($item)) {
                $array[] = $item;
            } elseif (is_object($item) && method_exists($item, 'toArray')) {
                $array[] = $item->toArray();
            } else {
                $array[] = $item;
            }
        }
        
        return $array;
    }

    /**
     * Converte a coleção para JSON
     * 
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * Implementação de JsonSerializable
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Implementação de IteratorAggregate
     * 
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Implementação de ArrayAccess: offsetExists
     * 
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Implementação de ArrayAccess: offsetGet
     * 
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->items[$offset] ?? null;
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
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Implementação de ArrayAccess: offsetUnset
     * 
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * Método mágico: __toString
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}