<?php

namespace Coyote\Http\Csrf\Exceptions;

use Exception;

class TokenMismatchException extends Exception
{
    protected $code = 419;
    protected $message = 'CSRF token mismatch.';
    
    public function __construct(string $message = '', int $code = 419, Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'CSRF token validation failed.';
        }
        
        parent::__construct($message, $code, $previous);
    }
    
    public function getStatusCode(): int
    {
        return $this->code;
    }
}