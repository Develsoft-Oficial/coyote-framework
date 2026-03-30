<?php
// vendors/coyote/Log/Logger.php

namespace Coyote\Log;

/**
 * Logger simples para o framework
 */
class Logger
{
    /**
     * @var string Caminho do arquivo de log
     */
    protected $logFile;

    /**
     * @var array Níveis de log suportados
     */
    protected $levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug'
    ];

    /**
     * Criar novo logger
     *
     * @param string|null $logFile Caminho do arquivo de log
     */
    public function __construct(?string $logFile = null)
    {
        if ($logFile === null) {
            $logFile = __DIR__ . '/../../storage/logs/coyote.log';
        }
        
        $this->logFile = $logFile;
        
        // Criar diretório se não existir
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Registrar mensagem de log
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void
    {
        if (!in_array($level, $this->levels)) {
            $level = 'info';
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context, JSON_PRETTY_PRINT) : '';
        
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $contextStr
        );

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Registrar emergência
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Registrar alerta
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Registrar crítico
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Registrar erro
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Registrar aviso
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Registrar notificação
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Registrar informação
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Registrar debug
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Obter conteúdo do arquivo de log
     *
     * @param int $lines Número de linhas a retornar
     * @return string
     */
    public function getLogContent(int $lines = 100): string
    {
        if (!file_exists($this->logFile)) {
            return '';
        }

        $content = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($content === false) {
            return '';
        }

        $content = array_slice($content, -$lines);
        return implode("\n", $content);
    }

    /**
     * Limpar arquivo de log
     *
     * @return void
     */
    public function clear(): void
    {
        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }
    }

    /**
     * Verificar se arquivo de log existe
     *
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->logFile);
    }

    /**
     * Obter tamanho do arquivo de log
     *
     * @return int
     */
    public function getSize(): int
    {
        return file_exists($this->logFile) ? filesize($this->logFile) : 0;
    }
}