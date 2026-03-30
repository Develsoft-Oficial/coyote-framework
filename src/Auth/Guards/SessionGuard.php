<?php

namespace Coyote\Auth\Guards;

use Coyote\Auth\Contracts\Authenticatable;
use Coyote\Auth\Contracts\UserProvider;
use Coyote\Session\SessionInterface;
use Coyote\Http\Request;

class SessionGuard implements Guard
{
    /**
     * The name of the guard.
     *
     * @var string
     */
    protected $name;

    /**
     * The user provider implementation.
     *
     * @var \Coyote\Auth\Contracts\UserProvider
     */
    protected $provider;

    /**
     * The session store implementation.
     *
     * @var \Coyote\Session\SessionInterface
     */
    protected $session;

    /**
     * The current request instance.
     *
     * @var \Coyote\Http\Request|null
     */
    protected $request;

    /**
     * The currently authenticated user.
     *
     * @var \Coyote\Auth\Contracts\Authenticatable|null
     */
    protected $user;

    /**
     * Indicates if the user was authenticated via a recaller cookie.
     *
     * @var bool
     */
    protected $viaRemember = false;

    /**
     * The session key for the user ID.
     *
     * @var string
     */
    protected $sessionKey;

    /**
     * The session key for the "remember me" token.
     *
     * @var string
     */
    protected $recallerKey = 'remember_me';

    /**
     * Indicates if the logout method has been called.
     *
     * @var bool
     */
    protected $loggedOut = false;

    /**
     * Create a new session guard.
     *
     * @param  string  $name
     * @param  \Coyote\Auth\Contracts\UserProvider  $provider
     * @param  \Coyote\Session\Store  $session
     * @param  \Coyote\Http\Request|null  $request
     * @param  array  $config
     * @return void
     */
    public function __construct(string $name, UserProvider $provider, Store $session, ?Request $request = null, array $config = [])
    {
        $this->name = $name;
        $this->provider = $provider;
        $this->session = $session;
        $this->request = $request;
        $this->sessionKey = 'auth_' . $name;
        $this->config = $config;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Coyote\Auth\Contracts\Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        if ($this->loggedOut) {
            return null;
        }

        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get($this->sessionKey);

        // First we will try to load the user using the identifier from the session.
        // If no user is found, we will attempt to load via the recaller cookie.
        if (!is_null($id)) {
            if ($user = $this->provider->retrieveById($id)) {
                $this->user = $user;
            }
        }

        // If the user is null, we may attempt to load via the recaller cookie.
        if (is_null($this->user) && !is_null($recaller = $this->recaller())) {
            $this->user = $this->userFromRecaller($recaller);
            
            if ($this->user) {
                $this->updateSession($this->user->getAuthIdentifier());
                $this->viaRemember = true;
            }
        }

        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        if ($this->loggedOut) {
            return null;
        }

        return $this->user()
            ? $this->user()->getAuthIdentifier()
            : $this->session->get($this->sessionKey);
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (!is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
            $this->setUser($user);
            return true;
        }

        return false;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array  $credentials
     * @param  bool  $remember
     * @return bool
     */
    public function attempt(array $credentials = [], bool $remember = false): bool
    {
        $this->viaRemember = false;

        if ($this->validate($credentials)) {
            $this->login($this->user, $remember);
            return true;
        }

        return false;
    }

    /**
     * Log a user into the application.
     *
     * @param  \Coyote\Auth\Contracts\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(Authenticatable $user, bool $remember = false): void
    {
        $this->updateSession($user->getAuthIdentifier());

        // If the user should be permanently "remembered" by the application we will
        // queue a permanent cookie that contains the encrypted copy of the user
        // identifier. We will then decrypt this later to retrieve the users.
        if ($remember) {
            $this->createRememberTokenIfDoesntExist($user);
            $this->queueRecallerCookie($user);
        }

        // If we have an event dispatcher instance set we will fire an event so that
        // any listeners will hook into the authentication events and run actions.
        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
    }

    /**
     * Update the session with the given ID.
     *
     * @param  mixed  $id
     * @return void
     */
    protected function updateSession($id): void
    {
        $this->session->put($this->sessionKey, $id);
        $this->session->migrate(true);
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout(): void
    {
        $user = $this->user();

        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        $this->clearUserDataFromStorage();

        if (!is_null($this->user)) {
            $this->cycleRememberToken($user);
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;
        $this->loggedOut = true;
    }

    /**
     * Remove the user data from the session and cookies.
     *
     * @return void
     */
    protected function clearUserDataFromStorage(): void
    {
        $this->session->remove($this->sessionKey);

        if (!is_null($this->recaller())) {
            $this->getCookieJar()->queue($this->getCookieJar()->forget($this->recallerKey));
        }
    }

    /**
     * Get the decrypted recaller cookie for the request.
     *
     * @return array|null
     */
    protected function recaller(): ?array
    {
        if (is_null($this->request)) {
            return null;
        }

        if ($recaller = $this->request->cookies->get($this->recallerKey)) {
            return explode('|', $recaller, 3);
        }

        return null;
    }

    /**
     * Pull a user from the repository by its "remember me" token.
     *
     * @param  array  $recaller
     * @return \Coyote\Auth\Contracts\Authenticatable|null
     */
    protected function userFromRecaller(array $recaller): ?Authenticatable
    {
        if (count($recaller) !== 3) {
            return null;
        }

        [$id, $token, $hash] = $recaller;

        // Verify the hash to prevent tampering
        if (!hash_equals(hash('sha256', $id . '|' . $token), $hash)) {
            return null;
        }

        if ($user = $this->provider->retrieveByToken($id, $token)) {
            return $user;
        }

        return null;
    }

    /**
     * Create a new "remember me" token for the user if one doesn't already exist.
     *
     * @param  \Coyote\Auth\Contracts\Authenticatable  $user
     * @return void
     */
    protected function createRememberTokenIfDoesntExist(Authenticatable $user): void
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
    }

    /**
     * Refresh the "remember me" token for the user.
     *
     * @param  \Coyote\Auth\Contracts\Authenticatable  $user
     * @return void
     */
    protected function cycleRememberToken(Authenticatable $user): void
    {
        $token = bin2hex(random_bytes(20));
        $user->setRememberToken($token);
        $this->provider->updateRememberToken($user, $token);
    }

    /**
     * Queue the recaller cookie into the cookie jar.
     *
     * @param  \Coyote\Auth\Contracts\Authenticatable  $user
     * @return void
     */
    protected function queueRecallerCookie(Authenticatable $user): void
    {
        $value = $user->getAuthIdentifier() . '|' . $user->getRememberToken();
        $hash = hash('sha256', $value);
        $value .= '|' . $hash;

        $this->getCookieJar()->queue(
            $this->createRecallerCookie($value)
        );
    }

    /**
     * Create a "remember me" cookie for a given ID.
     *
     * @param  string  $value
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function createRecallerCookie(string $value)
    {
        // This is a simplified version - in a real implementation
        // we would use the Cookie class from Symfony
        return null;
    }

    /**
     * Get the cookie jar instance.
     *
     * @return mixed
     */
    protected function getCookieJar()
    {
        // This would return the actual cookie jar
        return null;
    }

    /**
     * Fire the login event if the dispatcher is set.
     *
     * @param  \Coyote\Auth\Contracts\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    protected function fireLoginEvent(Authenticatable $user, bool $remember): void
    {
        // Event firing would go here
    }

    /**
     * Set the current user.
     *
     * @param  \Coyote\Auth\Contracts\Authenticatable  $user
     * @return void
     */
    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
        $this->loggedOut = false;
    }

    /**
     * Set the request instance.
     *
     * @param  \Coyote\Http\Request  $request
     * @return $this
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Set the session instance.
     *
     * @param  \Coyote\Session\SessionInterface  $session
     * @return $this
     */
    public function setSession(SessionInterface $session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get the name of the guard.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember(): bool
    {
        return $this->viaRemember;
    }
}