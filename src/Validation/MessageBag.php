<?php

namespace Coyote\Validation;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;
use JsonSerializable;

class MessageBag implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * All of the registered messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Default format for message output.
     *
     * @var string
     */
    protected $format = ':message';

    /**
     * Create a new message bag instance.
     *
     * @param array $messages
     */
    public function __construct(array $messages = [])
    {
        foreach ($messages as $key => $value) {
            $this->messages[$key] = (array) $value;
        }
    }

    /**
     * Add a message to the bag.
     *
     * @param string $key
     * @param string $message
     * @return $this
     */
    public function add(string $key, string $message): self
    {
        if ($this->isUnique($key, $message)) {
            $this->messages[$key][] = $message;
        }

        return $this;
    }

    /**
     * Merge a new array of messages into the bag.
     *
     * @param array $messages
     * @return $this
     */
    public function merge(array $messages): self
    {
        $this->messages = array_merge_recursive($this->messages, $messages);

        return $this;
    }

    /**
     * Determine if a key and message combination already exists.
     *
     * @param string $key
     * @param string $message
     * @return bool
     */
    protected function isUnique(string $key, string $message): bool
    {
        $messages = $this->messages[$key] ?? [];

        return !in_array($message, $messages, true);
    }

    /**
     * Check if the message bag has any messages.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->messages);
    }

    /**
     * Check if the message bag has any messages.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Get the first message from the bag for a given key.
     *
     * @param string|null $key
     * @param string|null $format
     * @return string|null
     */
    public function first(?string $key = null, ?string $format = null): ?string
    {
        $messages = $key ? $this->get($key, $format) : $this->all($format);

        return $messages[0] ?? null;
    }

    /**
     * Get all of the messages from the bag for a given key.
     *
     * @param string $key
     * @param string|null $format
     * @return array
     */
    public function get(string $key, ?string $format = null): array
    {
        if (array_key_exists($key, $this->messages)) {
            return $this->transform($this->messages[$key], $this->checkFormat($format));
        }

        return [];
    }

    /**
     * Get all of the messages for every key in the bag.
     *
     * @param string|null $format
     * @return array
     */
    public function all(?string $format = null): array
    {
        $format = $this->checkFormat($format);
        $all = [];

        foreach ($this->messages as $key => $messages) {
            $all[$key] = $this->transform($messages, $format);
        }

        return $all;
    }

    /**
     * Get all of the unique messages for every key in the bag.
     *
     * @param string|null $format
     * @return array
     */
    public function unique(?string $format = null): array
    {
        $format = $this->checkFormat($format);
        $all = [];

        foreach ($this->messages as $key => $messages) {
            $uniqueMessages = array_unique($messages);
            $all[$key] = $this->transform($uniqueMessages, $format);
        }

        return $all;
    }

    /**
     * Format an array of messages.
     *
     * @param array $messages
     * @param string $format
     * @return array
     */
    protected function transform(array $messages, string $format): array
    {
        return array_map(function ($message) use ($format) {
            return str_replace(':message', $message, $format);
        }, $messages);
    }

    /**
     * Check if a key exists in the message bag.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->messages[$key]) && count($this->messages[$key]) > 0;
    }

    /**
     * Check if a key does not exist in the message bag.
     *
     * @param string $key
     * @return bool
     */
    public function missing(string $key): bool
    {
        return !$this->has($key);
    }

    /**
     * Get the number of messages in the bag.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->messages, COUNT_RECURSIVE) - count($this->messages);
    }

    /**
     * Get the number of messages for a given key.
     *
     * @param string $key
     * @return int
     */
    public function countFor(string $key): int
    {
        return count($this->messages[$key] ?? []);
    }

    /**
     * Get the keys present in the message bag.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->messages);
    }

    /**
     * Clear all messages from the bag.
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->messages = [];

        return $this;
    }

    /**
     * Clear messages for a specific key.
     *
     * @param string $key
     * @return $this
     */
    public function clearFor(string $key): self
    {
        unset($this->messages[$key]);

        return $this;
    }

    /**
     * Set the default message format.
     *
     * @param string $format
     * @return $this
     */
    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get the default message format.
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Check and return the format to use.
     *
     * @param string|null $format
     * @return string
     */
    protected function checkFormat(?string $format): string
    {
        return $format ?: $this->format;
    }

    /**
     * Convert the message bag to its string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            throw new \InvalidArgumentException('MessageBag does not support numeric keys.');
        }

        $this->add($offset, $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->clearFor($offset);
    }

    /**
     * Get an iterator for the messages.
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->messages);
    }
}