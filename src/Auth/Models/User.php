<?php

namespace Coyote\Auth\Models;

use Coyote\Auth\Contracts\Authenticatable;
use Coyote\Database\Model;

class User extends Model implements Authenticatable
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getAttribute($this->getAuthIdentifierName());
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return $this->getKeyName();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->getAttribute('password');
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken(): string
    {
        return $this->getAttribute($this->getRememberTokenName());
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value): void
    {
        $this->setAttribute($this->getRememberTokenName(), $value);
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    /**
     * Set the password attribute.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * Verify the user's password.
     *
     * @param  string  $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->getAuthPassword());
    }

    /**
     * Get the user's email address.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getAttribute('email');
    }

    /**
     * Get the user's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getAttribute('name');
    }

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->getAttribute('email_verified_at'));
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification(): string
    {
        return $this->getEmail();
    }

    /**
     * Get the avatar URL for the user.
     *
     * @return string|null
     */
    public function getAvatarUrl(): ?string
    {
        if ($avatar = $this->getAttribute('avatar')) {
            return $avatar;
        }

        // Generate a default avatar based on email
        $hash = md5(strtolower(trim($this->getEmail())));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon";
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param  \Coyote\Database\QueryBuilder  $query
     * @param  string  $type
     * @return \Coyote\Database\QueryBuilder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include active users.
     *
     * @param  \Coyote\Database\QueryBuilder  $query
     * @return \Coyote\Database\QueryBuilder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include users with verified email.
     *
     * @param  \Coyote\Database\QueryBuilder  $query
     * @return \Coyote\Database\QueryBuilder
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Find a user by their email address.
     *
     * @param  string  $email
     * @return static|null
     */
    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)->first();
    }

    /**
     * Create a new user instance.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function create(array $attributes): static
    {
        $user = new static($attributes);
        $user->save();
        return $user;
    }
}