<?php

namespace Coyote\Validation\Rules;

class MaxRule implements Rule
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

        $max = (int) $parameters[0];

        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }

        if (is_array($value)) {
            return count($value) <= $max;
        }

        if (is_numeric($value)) {
            return $value <= $max;
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
        $max = $parameters[0] ?? '?';
        return "The {$attribute} may not be greater than {$max} characters.";
    }
}