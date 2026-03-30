<?php
// vendors/coyote/Core/Exceptions/NotFoundException.php

namespace Coyote\Core\Exceptions;

use Exception;

/**
 * Exceção para classe não encontrada
 */
class NotFoundException extends Exception
{
    /**
     * Criar exceção para classe não encontrada
     *
     * @param string $class
     * @return self
     */
    public static function classNotFound(string $class): self
    {
        return new self(
            "Target class [$class] does not exist."
        );
    }

    /**
     * Criar exceção para binding não encontrado
     *
     * @param string $abstract
     * @return self
     */
    public static function bindingNotFound(string $abstract): self
    {
        return new self(
            "No binding found for [$abstract]."
        );
    }
}