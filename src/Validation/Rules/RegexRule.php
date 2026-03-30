<?php

namespace Coyote\Validation\Rules;

class RegexRule implements Rule
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

        if (!is_string($value)) {
            return false;
        }

        $pattern = $parameters[0];

        // Add delimiters if not present
        if (!preg_match('/^\/.*\/[a-zA-Z]*$/', $pattern)) {
            $pattern = '/' . $pattern . '/';
        }

        return preg_match($pattern, $value) === 1;
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
        return "The {$attribute} format is invalid.";
    }
}