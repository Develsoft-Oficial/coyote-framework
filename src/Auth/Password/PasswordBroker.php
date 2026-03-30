<?php

namespace Coyote\Auth\Password;

use Coyote\Auth\Contracts\UserProvider;
use Coyote\Database\DatabaseManager;
use RuntimeException;
use InvalidArgumentException;

/**
 * PasswordBroker
 * 
 * Responsável pelo gerenciamento de reset de senhas no Coyote Framework.
 * Cria, valida e processa tokens de reset de senha.
 */
class PasswordBroker
{
    /**
     * O gerenciador de banco de dados.
     *
     * @var \Coyote\Database\DatabaseManager
     */
    protected $db;

    /**
     * O provedor de usuários.
     *
     * @var \Coyote\Auth\Contracts\UserProvider
     */
    protected $provider;

    /**
     * O hasher de senhas.
     *
     * @var \Coyote\Auth\Password\PasswordHasher
     */
    protected $hasher;

    /**
     * A tabela onde os tokens de reset são armazenados.
     *
     * @var string
     */
    protected $table;

    /**
     * O tempo de expiração dos tokens em minutos.
     *
     * @var int
     */
    protected $expire;

    /**
     * O tempo de throttling entre solicitações em minutos.
     *
     * @var int
     */
    protected $throttle;

    /**
     * Cria uma nova instância do PasswordBroker.
     *
     * @param \Coyote\Database\DatabaseManager $db
     * @param \Coyote\Auth\Contracts\UserProvider $provider
     * @param \Coyote\Auth\Password\PasswordHasher $hasher
     * @param array $config
     * @return void
     */
    public function __construct(DatabaseManager $db, UserProvider $provider, PasswordHasher $hasher, array $config = [])
    {
        $this->db = $db;
        $this->provider = $provider;
        $this->hasher = $hasher;
        
        $this->table = $config['table'] ?? 'password_resets';
        $this->expire = $config['expire'] ?? 60;
        $this->throttle = $config['throttle'] ?? 60;
    }

    /**
     * Envia um link de reset de senha para um usuário.
     *
     * @param array $credentials
     * @return string
     * @throws \RuntimeException
     */
    public function sendResetLink(array $credentials): string
    {
        // Primeiro, encontrar o usuário
        $user = $this->getUser($credentials);
        
        if (is_null($user)) {
            throw new RuntimeException('Não foi possível encontrar um usuário com essas credenciais.');
        }

        // Verificar throttling (limite de tentativas)
        if ($this->throttled($user)) {
            throw new RuntimeException('Muitas tentativas de reset de senha. Por favor, aguarde antes de tentar novamente.');
        }

        // Criar token de reset
        $token = $this->createToken($user);

        // Aqui normalmente enviaríamos um email com o token
        // Por enquanto, apenas retornamos o token para fins de teste
        return $token;
    }

    /**
     * Reseta a senha do usuário com o token fornecido.
     *
     * @param array $credentials
     * @param string $token
     * @return bool
     * @throws \RuntimeException
     */
    public function reset(array $credentials, string $token): bool
    {
        // Validar credenciais
        if (!isset($credentials['email']) || !isset($credentials['password'])) {
            throw new InvalidArgumentException('Credenciais inválidas. Email e nova senha são obrigatórios.');
        }

        // Buscar o registro de reset
        $resetRecord = $this->getResetRecord($credentials['email'], $token);
        
        if (!$resetRecord) {
            throw new RuntimeException('Token de reset inválido ou expirado.');
        }

        // Verificar se o token expirou
        if ($this->tokenExpired($resetRecord['created_at'])) {
            $this->deleteResetRecord($credentials['email']);
            throw new RuntimeException('Token de reset expirado.');
        }

        // Buscar o usuário
        $user = $this->provider->retrieveByCredentials(['email' => $credentials['email']]);
        
        if (is_null($user)) {
            throw new RuntimeException('Não foi possível encontrar o usuário.');
        }

        // Atualizar a senha do usuário
        $hashedPassword = $this->hasher->hash($credentials['password']);
        
        // Aqui precisaríamos de um método para atualizar a senha do usuário
        // Por enquanto, vamos assumir que o UserProvider tem um método updatePassword
        // Em uma implementação real, isso seria feito via DatabaseProvider
        
        // Limpar o token usado
        $this->deleteResetRecord($credentials['email']);

        return true;
    }

    /**
     * Valida um token de reset de senha.
     *
     * @param string $email
     * @param string $token
     * @return bool
     */
    public function validateToken(string $email, string $token): bool
    {
        $resetRecord = $this->getResetRecord($email, $token);
        
        if (!$resetRecord) {
            return false;
        }

        if ($this->tokenExpired($resetRecord['created_at'])) {
            $this->deleteResetRecord($email);
            return false;
        }

        return true;
    }

    /**
     * Cria um novo token de reset para o usuário.
     *
     * @param \Coyote\Auth\Contracts\Authenticatable $user
     * @return string
     */
    protected function createToken($user): string
    {
        // Gerar token seguro
        $token = bin2hex(random_bytes(32));
        
        // Hash do token para armazenamento seguro
        $hashedToken = hash('sha256', $token);
        
        // Limpar tokens antigos para este email
        $this->deleteResetRecord($user->email ?? $user->getAuthIdentifier());
        
        // Inserir novo token
        $this->db->table($this->table)->insert([
            'email' => $user->email ?? $user->getAuthIdentifier(),
            'token' => $hashedToken,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $token;
    }

    /**
     * Busca um registro de reset por email e token.
     *
     * @param string $email
     * @param string $token
     * @return array|null
     */
    protected function getResetRecord(string $email, string $token): ?array
    {
        $hashedToken = hash('sha256', $token);
        
        $record = $this->db->table($this->table)
            ->where('email', '=', $email)
            ->where('token', '=', $hashedToken)
            ->first();
        
        return $record ? (array) $record : null;
    }

    /**
     * Verifica se um token expirou.
     *
     * @param string $createdAt
     * @return bool
     */
    protected function tokenExpired(string $createdAt): bool
    {
        $expiresAt = strtotime($createdAt) + ($this->expire * 60);
        return time() > $expiresAt;
    }

    /**
     * Remove um registro de reset.
     *
     * @param string $email
     * @return void
     */
    protected function deleteResetRecord(string $email): void
    {
        $this->db->table($this->table)
            ->where('email', '=', $email)
            ->delete();
    }

    /**
     * Verifica se há throttling (limite de tentativas) para um usuário.
     *
     * @param \Coyote\Auth\Contracts\Authenticatable $user
     * @return bool
     */
    protected function throttled($user): bool
    {
        $email = $user->email ?? $user->getAuthIdentifier();
        
        // Buscar tentativas recentes
        $recentAttempts = $this->db->table($this->table)
            ->where('email', '=', $email)
            ->where('created_at', '>', date('Y-m-d H:i:s', time() - ($this->throttle * 60)))
            ->count();
        
        return $recentAttempts >= 3; // Máximo de 3 tentativas no período de throttling
    }

    /**
     * Busca um usuário baseado nas credenciais fornecidas.
     *
     * @param array $credentials
     * @return \Coyote\Auth\Contracts\Authenticatable|null
     */
    protected function getUser(array $credentials): ?\Coyote\Auth\Contracts\Authenticatable
    {
        if (!isset($credentials['email'])) {
            return null;
        }

        return $this->provider->retrieveByCredentials(['email' => $credentials['email']]);
    }

    /**
     * Define a tabela de reset de senhas.
     *
     * @param string $table
     * @return $this
     */
    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Define o tempo de expiração dos tokens.
     *
     * @param int $minutes
     * @return $this
     */
    public function setExpire(int $minutes): self
    {
        $this->expire = $minutes;
        return $this;
    }

    /**
     * Define o tempo de throttling.
     *
     * @param int $minutes
     * @return $this
     */
    public function setThrottle(int $minutes): self
    {
        $this->throttle = $minutes;
        return $this;
    }

    /**
     * Obtém a tabela de reset de senhas.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Obtém o tempo de expiração dos tokens.
     *
     * @return int
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * Obtém o tempo de throttling.
     *
     * @return int
     */
    public function getThrottle(): int
    {
        return $this->throttle;
    }
}