<?php

namespace Coyote\Validation\Rules;

class ConfirmedRule implements Rule
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
        $confirmationField = $attribute . '_confirmation';
        
        return isset($data[$confirmationField]) && $value === $data[$confirmationField];
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
        return "The {$attribute} confirmation does not match.";
    }
}