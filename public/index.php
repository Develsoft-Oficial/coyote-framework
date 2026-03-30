<?php
// public/index.php

/**
 * Coyote Framework - Ponto de Entrada
 * 
 * Este arquivo é o front controller da aplicação.
 * Todas as requisições são direcionadas para este arquivo.
 */

// Definir constantes
define('COYOTE_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VENDOR_PATH', BASE_PATH . '/vendors');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('PUBLIC_PATH', __DIR__);

// Verificar requisitos do PHP
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    die('Coyote Framework requires PHP 8.1.0 or higher.');
}

// Verificar extensões necessárias
$requiredExtensions = ['pdo', 'json', 'mbstring'];
foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        die("Coyote Framework requires the {$extension} extension.");
    }
}

// Carregar autoloader
require VENDOR_PATH . '/autoload.php';

// Criar instância da aplicação
try {
    $app = new Coyote\Core\Application(BASE_PATH);
    
    // Configurar ambiente
    $app->setEnvironment($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'production');
    $app->setDebug(filter_var($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
    
    // Executar aplicação
    $response = $app->run();
    
    // Enviar resposta
    if ($response instanceof \Coyote\Http\Response) {
        $response->send();
    } elseif (is_string($response)) {
        echo $response;
    } elseif (is_array($response) || is_object($response)) {
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
} catch (\Throwable $e) {
    // Tratamento de erros
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    
    if ($app->isDebug() ?? false) {
        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
        echo '<title>Error - Coyote Framework</title>';
        echo '<style>';
        echo 'body { font-family: monospace; margin: 40px; background: #f5f5f5; }';
        echo '.error { background: #fff; border: 1px solid #ddd; border-radius: 5px; padding: 20px; }';
        echo '.error h1 { color: #c00; margin-top: 0; }';
        echo '.error pre { background: #f9f9f9; padding: 10px; border-radius: 3px; overflow: auto; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="error">';
        echo '<h1>' . htmlspecialchars(get_class($e)) . '</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<h3>Stack Trace:</h3>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    } else {
        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
        echo '<title>Error - Coyote Framework</title>';
        echo '<style>';
        echo 'body { font-family: sans-serif; margin: 40px; text-align: center; }';
        echo '.error { background: #fff; border: 1px solid #ddd; border-radius: 5px; padding: 40px; display: inline-block; }';
        echo '.error h1 { color: #666; margin-top: 0; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="error">';
        echo '<h1>500 Internal Server Error</h1>';
        echo '<p>An error occurred while processing your request.</p>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    // Log do erro
    if (isset($app) && $app->bound('log')) {
        $app['log']->error('Unhandled exception in front controller', [
            'exception' => $e,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}