<?php
// vendors/coyote/Core/Exceptions/ApplicationException.php

namespace Coyote\Core\Exceptions;

use Exception;

/**
 * Exceção da aplicação
 */
class ApplicationException extends Exception
{
    /**
     * Criar exceção para aplicação não inicializada
     *
     * @return self
     */
    public static function notBooted(): self
    {
        return new self(
            "Application has not been booted."
        );
    }

    /**
     * Criar exceção para provider inválido
     *
     * @param mixed $provider
     * @return self
     */
    public static function invalidProvider($provider): self
    {
        $type = gettype($provider);
        
        if ($type === 'object') {
            $type = get_class($provider);
        }
        
        return new self(
            "Invalid service provider: $type"
        );
    }

    /**
     * Criar exceção para caminho não definido
     *
     * @param string $path
     * @return self
     */
    public static function pathNotDefined(string $path): self
    {
        return new self(
            "Application path [$path] is not defined."
        );
    }
}