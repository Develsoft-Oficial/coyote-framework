<?php

namespace Coyote\Validation;

use Coyote\Validation\Rules\Rule;
use Coyote\Validation\Exceptions\ValidationException;

class Validator
{
    /**
     * The data being validated.
     *
     * @var array
     */
    protected $data = [];

    /**
     * The validation rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Custom error messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The validation errors.
     *
     * @var MessageBag
     */
    protected $errors;

    /**
     * Custom rule resolvers.
     *
     * @var array
     */
    protected static $customRules = [];

    /**
     * Create a new Validator instance.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     */
    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $this->parseRules($rules);
        $this->messages = $messages;
        $this->errors = new MessageBag();
    }

    /**
     * Parse the rules into a structured format.
     *
     * @param array $rules
     * @return array
     */
    protected function parseRules(array $rules): array
    {
        $parsed = [];

        foreach ($rules as $field => $ruleString) {
            if (is_string($ruleString)) {
                $parsed[$field] = $this->parseRuleString($ruleString);
            } elseif (is_array($ruleString)) {
                $parsed[$field] = $ruleString;
            }
        }

        return $parsed;
    }

    /**
     * Parse a rule string like "required|email|min:6".
     *
     * @param string $ruleString
     * @return array
     */
    protected function parseRuleString(string $ruleString): array
    {
        $rules = [];

        foreach (explode('|', $ruleString) as $rule) {
            $rule = trim($rule);
            
            if (empty($rule)) {
                continue;
            }

            // Check if rule has parameters (e.g., "min:6")
            if (strpos($rule, ':') !== false) {
                [$ruleName, $parameters] = explode(':', $rule, 2);
                $parameters = explode(',', $parameters);
            } else {
                $ruleName = $rule;
                $parameters = [];
            }

            $rules[] = [
                'rule' => $ruleName,
                'parameters' => $parameters,
            ];
        }

        return $rules;
    }

    /**
     * Validate the data.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->errors = new MessageBag();

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->getValue($field);

            foreach ($fieldRules as $ruleData) {
                $ruleName = $ruleData['rule'];
                $parameters = $ruleData['parameters'];

                if (!$this->passesRule($field, $value, $ruleName, $parameters)) {
                    $this->addError($field, $ruleName, $parameters);
                }
            }
        }

        return $this->errors->isEmpty();
    }

    /**
     * Get the value for a field.
     *
     * @param string $field
     * @return mixed
     */
    protected function getValue(string $field)
    {
        // Support dot notation for nested arrays
        if (strpos($field, '.') !== false) {
            return $this->getNestedValue($field);
        }

        return $this->data[$field] ?? null;
    }

    /**
     * Get a nested value using dot notation.
     *
     * @param string $key
     * @return mixed
     */
    protected function getNestedValue(string $key)
    {
        $keys = explode('.', $key);
        $value = $this->data;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Check if a rule passes for a given value.
     *
     * @param string $field
     * @param mixed $value
     * @param string $ruleName
     * @param array $parameters
     * @return bool
     */
    protected function passesRule(string $field, $value, string $ruleName, array $parameters): bool
    {
        // Check for custom rule first
        if (isset(static::$customRules[$ruleName])) {
            $callback = static::$customRules[$ruleName];
            return $callback($field, $value, $parameters, $this->data);
        }

        // Check for built-in rule
        $method = 'validate' . ucfirst($ruleName);
        if (method_exists($this, $method)) {
            return $this->$method($field, $value, $parameters);
        }

        // Check for rule class
        $ruleClass = 'Coyote\\Validation\\Rules\\' . ucfirst($ruleName) . 'Rule';
        if (class_exists($ruleClass)) {
            $rule = new $ruleClass();
            return $rule->passes($field, $value, $parameters, $this->data);
        }

        // Rule not found
        throw new \InvalidArgumentException("Validation rule '{$ruleName}' does not exist.");
    }

    /**
     * Add an error for a field.
     *
     * @param string $field
     * @param string $rule
     * @param array $parameters
     */
    protected function addError(string $field, string $rule, array $parameters): void
    {
        $message = $this->getMessage($field, $rule, $parameters);
        $this->errors->add($field, $message);
    }

    /**
     * Get the error message for a rule.
     *
     * @param string $field
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function getMessage(string $field, string $rule, array $parameters): string
    {
        // Check for custom message
        $customKey = "{$field}.{$rule}";
        if (isset($this->messages[$customKey])) {
            return $this->messages[$customKey];
        }

        // Check for field-specific message without rule
        if (isset($this->messages[$field])) {
            return $this->messages[$field];
        }

        // Default messages
        $defaultMessages = $this->getDefaultMessages();
        $messageKey = strtolower($rule);

        if (isset($defaultMessages[$messageKey])) {
            $message = $defaultMessages[$messageKey];
        } else {
            $message = "The {$field} field is invalid.";
        }

        // Replace placeholders
        return $this->replacePlaceholders($message, $field, $rule, $parameters);
    }

    /**
     * Replace placeholders in error messages.
     *
     * @param string $message
     * @param string $field
     * @param string $rule
     * @param array $parameters
     * @return string
     */
    protected function replacePlaceholders(string $message, string $field, string $rule, array $parameters): string
    {
        $replacements = [
            ':attribute' => $field,
            ':rule' => $rule,
        ];

        // Add parameter placeholders
        foreach ($parameters as $index => $parameter) {
            $replacements[":param" . ($index + 1)] = $parameter;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Get default error messages.
     *
     * @return array
     */
    protected function getDefaultMessages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'email' => 'The :attribute must be a valid email address.',
            'min' => 'The :attribute must be at least :param1 characters.',
            'max' => 'The :attribute may not be greater than :param1 characters.',
            'numeric' => 'The :attribute must be a number.',
            'integer' => 'The :attribute must be an integer.',
            'string' => 'The :attribute must be a string.',
            'array' => 'The :attribute must be an array.',
            'boolean' => 'The :attribute field must be true or false.',
            'date' => 'The :attribute must be a valid date.',
            'url' => 'The :attribute must be a valid URL.',
            'ip' => 'The :attribute must be a valid IP address.',
            'regex' => 'The :attribute format is invalid.',
            'in' => 'The selected :attribute is invalid.',
            'notin' => 'The selected :attribute is invalid.',
            'same' => 'The :attribute and :param1 must match.',
            'different' => 'The :attribute and :param1 must be different.',
            'confirmed' => 'The :attribute confirmation does not match.',
            'unique' => 'The :attribute has already been taken.',
        ];
    }

    /**
     * Check if validation fails.
     *
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->validate();
    }

    /**
     * Get the validation errors.
     *
     * @return MessageBag
     */
    public function errors(): MessageBag
    {
        if ($this->errors->isEmpty()) {
            $this->validate();
        }

        return $this->errors;
    }

    /**
     * Get the validated data.
     *
     * @return array
     * @throws ValidationException
     */
    public function validated(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this->errors());
        }

        $validated = [];

        foreach (array_keys($this->rules) as $field) {
            if (array_key_exists($field, $this->data)) {
                $validated[$field] = $this->data[$field];
            }
        }

        return $validated;
    }

    /**
     * Create a new validator instance.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return static
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        return new static($data, $rules, $messages);
    }

    /**
     * Register a custom validation rule.
     *
     * @param string $rule
     * @param callable $callback
     */
    public static function extend(string $rule, callable $callback): void
    {
        static::$customRules[$rule] = $callback;
    }

    /**
     * Validate required rule.
     *
     * @param string $field
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateRequired(string $field, $value, array $parameters): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && count($value) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Validate email rule.
     *
     * @param string $field
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateEmail(string $field, $value, array $parameters): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate min rule.
     *
     * @param string $field
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateMin(string $field, $value, array $parameters): bool
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
     * Validate max rule.
     *
     * @param string $field
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateMax(string $field, $value, array $parameters): bool
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
     * Validate numeric rule.
     *
     * @param string $field
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateNumeric(string $field, $value, array $parameters): bool
    {
        return is_numeric($value);
    }

    /**
     * Validate string rule.
     *
     * @param string $field
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateString(string $field, $value, array $parameters): bool
    {
        return is_string($value);
    }

    /**
     * Validate array rule.
     *
     * @param string $field
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateArray(string $field, $value, array $parameters): bool
    {
        return is_array($value);
    }

    /**
     * Validate confirmed rule.
     *
     * @param string $field
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateConfirmed(string $field, $value, array $parameters): bool
    {
        $confirmationField = $field . '_confirmation';
        return isset($this->data[$confirmationField]) && $value === $this->data[$confirmationField];
    }

    /**
     * Validate same rule.
     *
     * @param string $field
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    protected function validateSame(string $field, $value, array $parameters): bool
    {
        if (empty($parameters)) {
            return false;
        }

        $otherField = $parameters[0];
        $otherValue = $this->getValue($otherField);

        return $value === $otherValue;
    }
}