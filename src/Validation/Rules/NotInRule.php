<?php

namespace Coyote\Validation\Rules;

class NotInRule implements Rule
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

        return !in_array($value, $parameters, true);
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
        $values = implode(', ', $parameters);
        return "The selected {$attribute} is invalid. The value cannot be: {$values}.";
    }
}