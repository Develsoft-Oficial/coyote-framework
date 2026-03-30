<?php

namespace Coyote\Validation\Rules;

class DateRule implements Rule
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
        if (!is_string($value)) {
            return false;
        }

        if (empty($parameters)) {
            // Default date validation
            return strtotime($value) !== false;
        }

        // Validate with specific format
        $format = $parameters[0];
        $date = \DateTime::createFromFormat($format, $value);
        
        return $date && $date->format($format) === $value;
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
        if (empty($parameters)) {
            return "The {$attribute} must be a valid date.";
        }

        $format = $parameters[0];
        return "The {$attribute} must be a valid date in the format {$format}.";
    }
}