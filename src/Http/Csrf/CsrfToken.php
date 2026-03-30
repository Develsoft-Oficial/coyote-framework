<?php

namespace Coyote\Http\Csrf;

class CsrfToken
{
    protected $value;
    
    public function __construct(string $value)
    {
        $this->value = $value;
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    public function __toString(): string
    {
        return $this->value;
    }
    
    public function equals(CsrfToken $other): bool
    {
        return hash_equals($this->value, $other->getValue());
    }
    
    public function isEmpty(): bool
    {
        return empty($this->value);
    }
    
    public static function fromString(string $value): self
    {
        return new self($value);
    }
    
    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(32)));
    }
}