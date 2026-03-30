<?php

namespace Coyote\Validation\Rules;

class MinRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param array $data
     * @return bool
     */
    public function passes(string $attribute, $value, array $parameters, array $data): bool
    {
        if (empty($parameters)) {
            return false;
        }

        $min = (int) $parameters[0];

        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }

        if (is_array($value)) {
            return count($value) >= $min;
        }

        if (is_numeric($value)) {
            return $value >= $min;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @param string $attribute
     * @param array $parameters
     * @return string
     */
    public function message(string $attribute, array $parameters): string
    {
        $min = $parameters[0] ?? '?';
        return "The {$attribute} must be at least {$min} characters.";
    }
}