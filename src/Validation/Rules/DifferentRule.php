<?php

namespace Coyote\Validation\Rules;

class DifferentRule implements Rule
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

        $otherField = $parameters[0];
        $otherValue = $data[$otherField] ?? null;

        return $value !== $otherValue;
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
        $otherField = $parameters[0] ?? 'other field';
        return "The {$attribute} and {$otherField} must be different.";
    }
}