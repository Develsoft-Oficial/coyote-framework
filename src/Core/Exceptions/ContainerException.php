<?php
// vendors/coyote/Core/Exceptions/ContainerException.php

namespace Coyote\Core\Exceptions;

use Exception;

/**
 * Exceção do container
 */
class ContainerException extends Exception
{
    /**
     * Criar exceção para dependência não resolvida
     *
     * @param string $dependency
     * @param string $class
     * @return self
     */
    public static function unresolvedDependency(string $dependency, string $class): self
    {
        return new self(
            "Unresolvable dependency [$dependency] in class $class"
        );
    }

    /**
     * Criar exceção para classe não instanciável
     *
     * @param string $class
     * @return self
     */
    public static function notInstantiable(string $class): self
    {
        return new self(
            "Target [$class] is not instantiable."
        );
    }
}