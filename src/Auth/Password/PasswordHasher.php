<?php

namespace Coyote\Auth\Password;

/**
 * Interface PasswordHasher
 * 
 * Interface para hashing e verificação de senhas no Coyote Framework.
 * Implementações devem fornecer métodos seguros para hash e verificação de senhas.
 */
interface PasswordHasher
{
    /**
     * Cria um hash de senha.
     *
     * @param string $password A senha em texto plano
     * @param array $options Opções para o algoritmo de hash
     * @return string O hash da senha
     * @throws \RuntimeException Se o hash falhar
     */
    public function hash(string $password, array $options = []): string;

    /**
     * Verifica se uma senha corresponde a um hash.
     *
     * @param string $password A senha em texto plano
     * @param string $hashedPassword O hash da senha
     * @return bool True se a senha corresponder, false caso contrário
     */
    public function verify(string $password, string $hashedPassword): bool;

    /**
     * Verifica se um hash precisa ser re-hashed.
     *
     * @param string $hashedPassword O hash da senha
     * @param array $options Opções para verificação
     * @return bool True se o hash precisar ser atualizado, false caso contrário
     */
    public function needsRehash(string $hashedPassword, array $options = []): bool;

    /**
     * Obtém informações sobre um hash.
     *
     * @param string $hashedPassword O hash da senha
     * @return array Informações sobre o hash (algoritmo, opções, etc.)
     */
    public function getInfo(string $hashedPassword): array;
}