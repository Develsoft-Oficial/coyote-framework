<?php

namespace Coyote\Validation\Exceptions;

use Exception;
use Coyote\Validation\MessageBag;

class ValidationException extends Exception
{
    /**
     * The message bag instance.
     *
     * @var MessageBag
     */
    protected $errors;

    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $status = 422;

    /**
     * Create a new validation exception instance.
     *
     * @param MessageBag $errors
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(MessageBag $errors, string $message = 'Validation failed', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    /**
     * Get the validation errors.
     *
     * @return MessageBag
     */
    public function errors(): MessageBag
    {
        return $this->errors;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Set the HTTP status code.
     *
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the first error message.
     *
     * @param string|null $key
     * @return string|null
     */
    public function first(?string $key = null): ?string
    {
        return $this->errors->first($key);
    }

    /**
     * Get all error messages.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->errors->all();
    }

    /**
     * Check if a specific field has errors.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->errors->has($key);
    }

    /**
     * Get errors for a specific field.
     *
     * @param string $key
     * @return array
     */
    public function get(string $key): array
    {
        return $this->errors->get($key);
    }

    /**
     * Convert the exception to its string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getMessage() . ': ' . json_encode($this->errors->toArray());
    }

    /**
     * Create a new validation exception from a message bag.
     *
     * @param MessageBag $errors
     * @param string $message
     * @return static
     */
    public static function fromMessageBag(MessageBag $errors, string $message = 'Validation failed'): self
    {
        return new static($errors, $message);
    }

    /**
     * Create a new validation exception from an array of errors.
     *
     * @param array $errors
     * @param string $message
     * @return static
     */
    public static function fromArray(array $errors, string $message = 'Validation failed'): self
    {
        $messageBag = new MessageBag($errors);
        return new static($messageBag, $message);
    }
}