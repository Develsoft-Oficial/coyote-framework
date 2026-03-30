<?php
// vendors/coyote/Core/Exceptions/ConfigException.php

namespace Coyote\Core\Exceptions;

use Exception;

/**
 * Exceção de configuração
 */
class ConfigException extends Exception
{
    /**
     * Criar exceção para arquivo de configuração não encontrado
     *
     * @param string $file
     * @return self
     */
    public static function fileNotFound(string $file): self
    {
        return new self(
            "Configuration file [$file] not found."
        );
    }

    /**
     * Criar exceção para caminho de configuração inválido
     *
     * @param string $path
     * @return self
     */
    public static function invalidPath(string $path): self
    {
        return new self(
            "Configuration path [$path] is invalid or does not exist."
        );
    }

    /**
     * Criar exceção para chave de configuração não encontrada
     *
     * @param string $key
     * @return self
     */
    public static function keyNotFound(string $key): self
    {
        return new self(
            "Configuration key [$key] not found."
        );
    }

    /**
     * Criar exceção para falha ao salvar configuração
     *
     * @param string $file
     * @return self
     */
    public static function saveFailed(string $file): self
    {
        return new self(
            "Failed to save configuration to [$file]."
        );
    }
}