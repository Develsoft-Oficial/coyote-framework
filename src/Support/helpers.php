<?php

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string|null $abstract
     * @param array $parameters
     * @return mixed|\Coyote\Core\Application
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return \Coyote\Core\Application::getInstance();
        }
        
        return \Coyote\Core\Application::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('auth')) {
    /**
     * Get the auth manager instance.
     *
     * @param string|null $guard
     * @return \Coyote\Auth\AuthManager|\Coyote\Auth\Contracts\Guard
     */
    function auth($guard = null)
    {
        if (is_null($guard)) {
            return app('auth');
        }
        
        return app('auth')->guard($guard);
    }
}

if (!function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * @param string|array|null $key
     * @param mixed $default
     * @return mixed|\Coyote\Session\SessionManager
     */
    function session($key = null, $default = null)
    {
        $session = app('session');
        
        if (is_null($key)) {
            return $session;
        }
        
        if (is_array($key)) {
            return $session->put($key);
        }
        
        return $session->get($key, $default);
    }
}

if (!function_exists('validator')) {
    /**
     * Create a new Validator instance.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return \Coyote\Validation\Validator
     */
    function validator(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        return app('validator')->make($data, $rules, $messages, $customAttributes);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    function csrf_token(): string
    {
        return app('csrf')->token();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     *
     * @return string
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('csrf_meta')) {
    /**
     * Generate CSRF meta tag for JavaScript frameworks.
     *
     * @return string
     */
    function csrf_meta(): string
    {
        return '<meta name="csrf-token" content="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve an old input item.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function old($key = null, $default = null)
    {
        $session = app('session');
        $oldInput = $session->get('_old_input', []);
        
        if (is_null($key)) {
            return $oldInput;
        }
        
        return $oldInput[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * @param string|array|null $key
     * @param mixed $default
     * @return mixed|\Coyote\Config\Repository
     */
    function config($key = null, $default = null)
    {
        $config = app('config');
        
        if (is_null($key)) {
            return $config;
        }
        
        if (is_array($key)) {
            return $config->set($key);
        }
        
        return $config->get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        
        if ($value === false) {
            return value($default);
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        if (strlen($value) > 1 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return substr($value, 1, -1);
        }
        
        return $value;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    function value($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

if (!function_exists('abort')) {
    /**
     * Throw an HttpException with the given data.
     *
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return void
     *
     * @throws \Coyote\Http\Exceptions\HttpException
     */
    function abort($code, $message = '', array $headers = [])
    {
        throw new \Coyote\Http\Exceptions\HttpException($code, $message, $headers);
    }
}

if (!function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return \Coyote\Http\Response
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        return new \Coyote\Http\Response($content, $status, $headers);
    }
}

if (!function_exists('redirect')) {
    /**
     * Get a redirect response to the given path.
     *
     * @param string $path
     * @param int $status
     * @param array $headers
     * @param bool|null $secure
     * @return \Coyote\Http\RedirectResponse
     */
    function redirect($path = null, $status = 302, $headers = [], $secure = null)
    {
        $redirect = new \Coyote\Http\RedirectResponse('');
        
        if (!is_null($path)) {
            $redirect->to($path, $status, $headers, $secure);
        }
        
        return $redirect;
    }
}

if (!function_exists('route')) {
    /**
     * Generate a URL to a named route.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function route($name, $parameters = [], $absolute = true)
    {
        return app('router')->route($name, $parameters, $absolute);
    }
}

if (!function_exists('url')) {
    /**
     * Generate a url for the application.
     *
     * @param string $path
     * @param array $parameters
     * @param bool|null $secure
     * @return string
     */
    function url($path = null, $parameters = [], $secure = null)
    {
        return app('url')->to($path, $parameters, $secure);
    }
}