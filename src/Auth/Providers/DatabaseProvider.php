<?php

namespace Coyote\Auth\Providers;

use Coyote\Auth\Contracts\Authenticatable;
use Coyote\Auth\Contracts\UserProvider;
use Coyote\Auth\Password\PasswordHasher;
use Coyote\Database\DatabaseManager;
use Coyote\Database\QueryBuilder;

class DatabaseProvider implements UserProvider
{
    /**
     * The database manager instance.
     *
     * @var \Coyote\Database\DatabaseManager
     */
    protected $db;

    /**
     * The database connection name.
     *
     * @var string|null
     */
    protected $connection;

    /**
     * The table containing the users.
     *
     * @var string
     */
    protected $table;

    /**
     * The column name for the identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * The hasher instance.
     *
     * @var \Coyote\Auth\Password\PasswordHasher|null
     */
    protected $hasher;

    /**
     * Create a new database user provider.
     *
     * @param  array  $config
     * @param  string|null  $connection
     * @param  string  $table
     * @param  string  $identifier
     * @return void
     */
    public function __construct(array $config, ?string $connection = null, string $table = 'users', string $identifier = 'id')
    {
        $this->db = new DatabaseManager($config);
        $this->connection = $connection;
        $this->table = $table;
        $this->identifier = $identifier;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Coyote\Auth\Contracts\Authenticatable|null
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $user = $this->newQuery()
            ->where($this->identifier, '=', $identifier)
            ->first();

        if ($user) {
            return $this->createAuthenticatable($user);
        }

        return null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Coyote\Auth\Contracts\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $user = $this->newQuery()
            ->where($this->identifier, '=', $identifier)
            ->where('remember_token', '=', $token)
            ->first();

        if ($user) {
            return $this->createAuthenticatable($user);
        }

        return null;
    }

    /**
     * Update the "remember me" token for the given user.
     *
     * @param  \Coyote\Auth\Contracts\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $this->newQuery()
            ->where($this->identifier, '=', $user->getAuthIdentifier())
            ->update(['remember_token' => $token]);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Coyote\Auth\Contracts\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials)) {
            return null;
        }

        // First we will remove any password credential from the array
        $query = $this->newQuery();
        
        foreach ($credentials as $key => $value) {
            if (!str_contains($key, 'password')) {
                $query->where($key, '=', $value);
            }
        }

        $user = $query->first();

        if ($user) {
            return $this->createAuthenticatable($user);
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Coyote\Auth\Contracts\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (!isset($credentials['password'])) {
            return false;
        }

        $plain = $credentials['password'];
        $hashed = $user->getAuthPassword();

        // If no hasher is set, we'll do a simple comparison
        if (!$this->hasher) {
            return hash_equals($hashed, hash('sha256', $plain));
        }

        return $this->hasher->verify($plain, $hashed);
    }

    /**
     * Create a new query builder instance.
     *
     * @return \Coyote\Database\QueryBuilder
     */
    protected function newQuery(): QueryBuilder
    {
        $connection = $this->db->connection($this->connection);
        $pdo = $connection->getPdo();
        $queryBuilder = new QueryBuilder($connection, $this->table);
        return $queryBuilder;
    }

    /**
     * Create an authenticatable instance from a database record.
     *
     * @param  object|array  $record
     * @return \Coyote\Auth\Contracts\Authenticatable
     */
    protected function createAuthenticatable($record): Authenticatable
    {
        // Convert to array if it's an object
        if (is_object($record)) {
            $record = (array) $record;
        }

        return new class($record, $this->identifier) implements Authenticatable {
            private $attributes;
            private $identifier;

            public function __construct(array $attributes, string $identifier)
            {
                $this->attributes = $attributes;
                $this->identifier = $identifier;
            }

            public function getAuthIdentifier()
            {
                return $this->attributes[$this->identifier] ?? null;
            }

            public function getAuthPassword()
            {
                return $this->attributes['password'] ?? '';
            }

            public function getRememberToken()
            {
                return $this->attributes['remember_token'] ?? '';
            }

            public function setRememberToken($value): void
            {
                $this->attributes['remember_token'] = $value;
            }

            public function getRememberTokenName()
            {
                return 'remember_token';
            }

            public function __get($name)
            {
                return $this->attributes[$name] ?? null;
            }

            public function __set($name, $value)
            {
                $this->attributes[$name] = $value;
            }

            public function __isset($name)
            {
                return isset($this->attributes[$name]);
            }
        };
    }

    /**
     * Set the hasher instance.
     *
     * @param  \Coyote\Auth\Password\PasswordHasher  $hasher
     * @return $this
     */
    public function setHasher($hasher): self
    {
        $this->hasher = $hasher;

        return $this;
    }

    /**
     * Get the database manager instance.
     *
     * @return \Coyote\Database\DatabaseManager
     */
    public function getDatabaseManager(): DatabaseManager
    {
        return $this->db;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get the identifier column name.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}