<?php

namespace Coyote\Validation\Rules;

class IpRule implements Rule
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

        $flags = 0;
        
        // Check for IPv4 or IPv6 flag
        if (!empty($parameters)) {
            foreach ($parameters as $param) {
                if ($param === 'v4') {
                    $flags |= FILTER_FLAG_IPV4;
                } elseif ($param === 'v6') {
                    $flags |= FILTER_FLAG_IPV6;
                }
            }
        }

        return filter_var($value, FILTER_VALIDATE_IP, $flags) !== false;
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
        if (in_array('v4', $parameters) && !in_array('v6', $parameters)) {
            return "The {$attribute} must be a valid IPv4 address.";
        } elseif (in_array('v6', $parameters) && !in_array('v4', $parameters)) {
            return "The {$attribute} must be a valid IPv6 address.";
        }
        
        return "The {$attribute} must be a valid IP address.";
    }
}