<?php

namespace Coyote\Auth\Password;

use RuntimeException;
use InvalidArgumentException;

/**
 * BcryptHasher
 * 
 * Implementação de PasswordHasher usando o algoritmo bcrypt.
 * Bcrypt é um algoritmo de hash de senha seguro e amplamente utilizado.
 */
class BcryptHasher implements PasswordHasher
{
    /**
     * O custo padrão do algoritmo bcrypt.
     *
     * @var int
     */
    protected $cost = 12;

    /**
     * Cria uma nova instância do BcryptHasher.
     *
     * @param array $config Configuração do hasher
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->cost = $config['cost'] ?? $this->cost;
        
        // Validar o custo
        if ($this->cost < 4 || $this->cost > 31) {
            throw new InvalidArgumentException("Custo bcrypt inválido: {$this->cost}. Deve estar entre 4 e 31.");
        }
    }

    /**
     * Cria um hash de senha usando bcrypt.
     *
     * @param string $password A senha em texto plano
     * @param array $options Opções para o algoritmo de hash
     * @return string O hash da senha
     * @throws \RuntimeException Se o hash falhar
     */
    public function hash(string $password, array $options = []): string
    {
        $cost = $options['cost'] ?? $this->cost;
        
        // Validar o custo
        if ($cost < 4 || $cost > 31) {
            throw new InvalidArgumentException("Custo bcrypt inválido: {$cost}. Deve estar entre 4 e 31.");
        }

        // Validar o comprimento da senha
        if (strlen($password) > 72) {
            throw new RuntimeException('A senha é muito longa para bcrypt (máximo 72 bytes).');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
        
        if ($hash === false) {
            throw new RuntimeException('Falha ao criar hash da senha.');
        }

        return $hash;
    }

    /**
     * Verifica se uma senha corresponde a um hash bcrypt.
     *
     * @param string $password A senha em texto plano
     * @param string $hashedPassword O hash da senha
     * @return bool True se a senha corresponder, false caso contrário
     */
    public function verify(string $password, string $hashedPassword): bool
    {
        if (empty($hashedPassword)) {
            return false;
        }

        // Verificar se o hash parece ser um hash bcrypt válido
        if (strlen($hashedPassword) !== 60 || !str_starts_with($hashedPassword, '$2y$')) {
            // Pode ser um hash antigo ou de outro algoritmo
            // Tentar verificar de qualquer maneira
        }

        return password_verify($password, $hashedPassword);
    }

    /**
     * Verifica se um hash bcrypt precisa ser re-hashed.
     *
     * @param string $hashedPassword O hash da senha
     * @param array $options Opções para verificação
     * @return bool True se o hash precisar ser atualizado, false caso contrário
     */
    public function needsRehash(string $hashedPassword, array $options = []): bool
    {
        $cost = $options['cost'] ?? $this->cost;
        
        return password_needs_rehash($hashedPassword, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Obtém informações sobre um hash bcrypt.
     *
     * @param string $hashedPassword O hash da senha
     * @return array Informações sobre o hash
     */
    public function getInfo(string $hashedPassword): array
    {
        $info = password_get_info($hashedPassword);
        
        // Adicionar informações específicas do bcrypt
        if ($info['algo'] === PASSWORD_BCRYPT) {
            // Extrair custo do hash bcrypt
            if (preg_match('/^\$2y\$(\d{2})\$/', $hashedPassword, $matches)) {
                $info['options']['cost'] = (int) $matches[1];
            }
        }
        
        return $info;
    }

    /**
     * Obtém o custo atual do algoritmo.
     *
     * @return int
     */
    public function getCost(): int
    {
        return $this->cost;
    }

    /**
     * Define o custo do algoritmo.
     *
     * @param int $cost
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setCost(int $cost): void
    {
        if ($cost < 4 || $cost > 31) {
            throw new InvalidArgumentException("Custo bcrypt inválido: {$cost}. Deve estar entre 4 e 31.");
        }
        
        $this->cost = $cost;
    }

    /**
     * Verifica se uma string parece ser um hash bcrypt válido.
     *
     * @param string $hash
     * @return bool
     */
    public static function isValidHash(string $hash): bool
    {
        // Hash bcrypt deve ter 60 caracteres e começar com $2y$
        return strlen($hash) === 60 && str_starts_with($hash, '$2y$');
    }

    /**
     * Gera uma senha aleatória.
     *
     * @param int $length Comprimento da senha (padrão: 16)
     * @return string Senha aleatória
     */
    public static function generateRandomPassword(int $length = 16): string
    {
        if ($length < 8) {
            throw new InvalidArgumentException('O comprimento da senha deve ser pelo menos 8 caracteres.');
        }

        // Caracteres permitidos (excluindo caracteres ambíguos)
        $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%^&*';
        $charsLength = strlen($chars);
        
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $charsLength - 1)];
        }
        
        return $password;
    }
}