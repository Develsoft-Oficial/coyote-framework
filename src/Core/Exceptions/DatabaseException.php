<?php

namespace Coyote\Core\Exceptions;

use Exception;

/**
 * Database Exception
 * 
 * Exceção para erros de banco de dados
 */
class DatabaseException extends Exception
{
    /**
     * SQL que causou o erro
     * 
     * @var string|null
     */
    protected $sql;

    /**
     * Bindings que causaram o erro
     * 
     * @var array|null
     */
    protected $bindings;

    /**
     * Construtor
     * 
     * @param string $message Mensagem de erro
     * @param int $code Código de erro
     * @param Exception|null $previous Exceção anterior
     * @param string|null $sql SQL que causou o erro
     * @param array|null $bindings Bindings que causaram o erro
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Exception $previous = null,
        ?string $sql = null,
        ?array $bindings = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->sql = $sql;
        $this->bindings = $bindings;
    }

    /**
     * Obtém o SQL que causou o erro
     * 
     * @return string|null
     */
    public function getSql(): ?string
    {
        return $this->sql;
    }

    /**
     * Obtém os bindings que causaram o erro
     * 
     * @return array|null
     */
    public function getBindings(): ?array
    {
        return $this->bindings;
    }

    /**
     * Define o SQL que causou o erro
     * 
     * @param string|null $sql
     * @return self
     */
    public function setSql(?string $sql): self
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * Define os bindings que causaram o erro
     * 
     * @param array|null $bindings
     * @return self
     */
    public function setBindings(?array $bindings): self
    {
        $this->bindings = $bindings;
        return $this;
    }
}