<?php

namespace Coyote\Session;

/**
 * Interface Store
 * 
 * Define a interface para armazenamento de sessões no Coyote Framework.
 * Implementações devem fornecer métodos para manipulação de dados de sessão.
 */
interface Store
{
    /**
     * Inicia a sessão.
     *
     * @return bool
     */
    public function start(): bool;

    /**
     * Salva a sessão.
     *
     * @return bool
     */
    public function save(): bool;

    /**
     * Destrói a sessão.
     *
     * @return bool
     */
    public function destroy(): bool;

    /**
     * Regenera o ID da sessão.
     *
     * @param bool $deleteOldSession Se deve deletar a sessão antiga
     * @return bool
     */
    public function regenerate(bool $deleteOldSession = true): bool;

    /**
     * Obtém um valor da sessão.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Armazena um valor na sessão.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function put(string $key, $value): void;

    /**
     * Armazena múltiplos valores na sessão.
     *
     * @param array $values
     * @return void
     */
    public function putMany(array $values): void;

    /**
     * Verifica se uma chave existe na sessão.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Remove um valor da sessão.
     *
     * @param string $key
     * @return void
     */
    public function forget(string $key): void;

    /**
     * Remove múltiplos valores da sessão.
     *
     * @param array $keys
     * @return void
     */
    public function forgetMany(array $keys): void;

    /**
     * Limpa todos os dados da sessão.
     *
     * @return void
     */
    public function flush(): void;

    /**
     * Armazena um valor flash na sessão.
     * Valores flash estão disponíveis apenas na próxima requisição.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function flash(string $key, $value): void;

    /**
     * Armazena múltiplos valores flash na sessão.
     *
     * @param array $values
     * @return void
     */
    public function flashMany(array $values): void;

    /**
     * Mantém valores flash para a próxima requisição.
     *
     * @param array|string $keys
     * @return void
     */
    public function reflash($keys = null): void;

    /**
     * Obtém o ID da sessão.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Define o ID da sessão.
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id): void;

    /**
     * Obtém o nome da sessão.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Define o nome da sessão.
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void;

    /**
     * Verifica se a sessão foi iniciada.
     *
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * Obtém todos os dados da sessão.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Obtém e remove um valor da sessão.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function pull(string $key, $default = null);

    /**
     * Incrementa um valor numérico na sessão.
     *
     * @param string $key
     * @param int $amount
     * @return int
     */
    public function increment(string $key, int $amount = 1): int;

    /**
     * Decrementa um valor numérico na sessão.
     *
     * @param string $key
     * @param int $amount
     * @return int
     */
    public function decrement(string $key, int $amount = 1): int;
}