<?php
// vendors/coyote/Forms/CustomValidationHelper.php

namespace Coyote\Forms;

use Coyote\Validation\Validator;

/**
 * CustomValidationHelper - Provides custom validation callbacks for Form Builder
 */
class CustomValidationHelper
{
    /**
     * @var array Registered custom validation rules
     */
    protected static $customRules = [];
    
    /**
     * @var array Custom validation messages
     */
    protected static $customMessages = [];
    
    /**
     * Register a custom validation rule
     *
     * @param string $ruleName
     * @param callable $callback
     * @param string|null $message
     */
    public static function registerRule(string $ruleName, callable $callback, ?string $message = null): void
    {
        // Register with Coyote's Validator
        Validator::extend($ruleName, $callback);
        
        // Store locally for reference
        self::$customRules[$ruleName] = $callback;
        
        if ($message) {
            self::$customMessages[$ruleName] = $message;
        }
    }
    
    /**
     * Register multiple custom validation rules
     *
     * @param array $rules Array of [ruleName => callback] or [ruleName => [callback, message]]
     */
    public static function registerRules(array $rules): void
    {
        foreach ($rules as $ruleName => $ruleConfig) {
            if (is_array($ruleConfig)) {
                $callback = $ruleConfig[0] ?? null;
                $message = $ruleConfig[1] ?? null;
                
                if (is_callable($callback)) {
                    self::registerRule($ruleName, $callback, $message);
                }
            } elseif (is_callable($ruleConfig)) {
                self::registerRule($ruleName, $ruleConfig);
            }
        }
    }
    
    /**
     * Get custom validation message for a rule
     *
     * @param string $ruleName
     * @return string|null
     */
    public static function getMessage(string $ruleName): ?string
    {
        return self::$customMessages[$ruleName] ?? null;
    }
    
    /**
     * Check if a custom rule is registered
     *
     * @param string $ruleName
     * @return bool
     */
    public static function hasRule(string $ruleName): bool
    {
        return isset(self::$customRules[$ruleName]);
    }
    
    /**
     * Get all registered custom rules
     *
     * @return array
     */
    public static function getRules(): array
    {
        return self::$customRules;
    }
    
    /**
     * Register common custom validation rules
     */
    public static function registerCommonRules(): void
    {
        self::registerRules([
            // Brazilian CPF validation
            'cpf' => [
                function($attribute, $value, $parameters) {
                    return self::validateCpf($value);
                },
                'O CPF informado é inválido.'
            ],
            
            // Brazilian CNPJ validation
            'cnpj' => [
                function($attribute, $value, $parameters) {
                    return self::validateCnpj($value);
                },
                'O CNPJ informado é inválido.'
            ],
            
            // Strong password validation
            'strong_password' => [
                function($attribute, $value, $parameters) {
                    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $value);
                },
                'A senha deve conter pelo menos 8 caracteres, incluindo maiúscula, minúscula, número e caractere especial.'
            ],
            
            // Phone number validation (Brazilian format)
            'phone_br' => [
                function($attribute, $value, $parameters) {
                    return preg_match('/^(?:(?:\+|00)?(55)\s?)?(?:\(?([1-9][0-9])\)?\s?)?(?:((?:9\d|[2-9])\d{3})\-?(\d{4}))$/', $value);
                },
                'O número de telefone informado é inválido.'
            ],
            
            // Date after today
            'future_date' => [
                function($attribute, $value, $parameters) {
                    $date = strtotime($value);
                    return $date !== false && $date > time();
                },
                'A data deve ser futura.'
            ],
            
            // Date before today
            'past_date' => [
                function($attribute, $value, $parameters) {
                    $date = strtotime($value);
                    return $date !== false && $date < time();
                },
                'A data deve ser passada.'
            ],
            
            // Age validation (minimum age)
            'min_age' => [
                function($attribute, $value, $parameters) {
                    $minAge = $parameters[0] ?? 18;
                    $birthDate = new \DateTime($value);
                    $today = new \DateTime();
                    $age = $today->diff($birthDate)->y;
                    return $age >= $minAge;
                },
                'Você deve ter pelo menos :param1 anos.'
            ],
            
            // Even number validation
            'even' => [
                function($attribute, $value, $parameters) {
                    return is_numeric($value) && $value % 2 === 0;
                },
                'O número deve ser par.'
            ],
            
            // Odd number validation
            'odd' => [
                function($attribute, $value, $parameters) {
                    return is_numeric($value) && $value % 2 !== 0;
                },
                'O número deve ser ímpar.'
            ],
            
            // Array count validation
            'array_count' => [
                function($attribute, $value, $parameters) {
                    if (!is_array($value)) {
                        return false;
                    }
                    
                    $min = $parameters[0] ?? null;
                    $max = $parameters[1] ?? null;
                    $count = count($value);
                    
                    if ($min !== null && $count < $min) {
                        return false;
                    }
                    
                    if ($max !== null && $count > $max) {
                        return false;
                    }
                    
                    return true;
                },
                'O array deve conter entre :param1 e :param2 elementos.'
            ],
            
            // Unique in array validation
            'unique_in_array' => [
                function($attribute, $value, $parameters, $data) {
                    if (!is_array($value)) {
                        return true;
                    }
                    
                    return count($value) === count(array_unique($value));
                },
                'Os valores no array devem ser únicos.'
            ],
            
            // Regex validation with custom pattern
            'regex_custom' => [
                function($attribute, $value, $parameters) {
                    if (empty($parameters)) {
                        return true;
                    }
                    
                    $pattern = $parameters[0];
                    return preg_match($pattern, $value);
                },
                'O valor não corresponde ao padrão requerido.'
            ],
        ]);
    }
    
    /**
     * Validate Brazilian CPF
     *
     * @param string $cpf
     * @return bool
     */
    public static function validateCpf(string $cpf): bool
    {
        // Remove non-numeric characters
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Check length
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Check for repeated digits
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Validate first digit
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate Brazilian CNPJ
     *
     * @param string $cnpj
     * @return bool
     */
    public static function validateCnpj(string $cnpj): bool
    {
        // Remove non-numeric characters
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Check length
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Validate first digit
        $sum = 0;
        $weight = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weight[$i];
        }
        
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;
        
        if ($cnpj[12] != $digit1) {
            return false;
        }
        
        // Validate second digit
        $sum = 0;
        $weight = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weight[$i];
        }
        
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;
        
        if ($cnpj[13] != $digit2) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Add custom validation callback to a field
     *
     * @param Field $field
     * @param callable $callback
     * @param string $message
     * @return void
     */
    public static function addCallbackToField(Field $field, callable $callback, string $message): void
    {
        // Generate a unique rule name for this callback
        $ruleName = 'callback_' . md5(serialize($callback));
        
        // Register the callback as a custom rule
        self::registerRule($ruleName, $callback, $message);
        
        // Add the rule to the field
        $field->rule($ruleName);
    }
    
    /**
     * Create a validation rule from a closure
     *
     * @param \Closure $closure
     * @param string $message
     * @return string The generated rule name
     */
    public static function createRuleFromClosure(\Closure $closure, string $message = 'Validation failed.'): string
    {
        $ruleName = 'closure_' . uniqid();
        self::registerRule($ruleName, $closure, $message);
        return $ruleName;
    }
    
    /**
     * Example of using custom validation with Form Builder
     *
     * @return array Example usage
     */
    public static function getExampleUsage(): array
    {
        return [
            'basic' => [
                'code' => '// Register custom rule
CustomValidationHelper::registerRule(\'even_number\', function($attribute, $value) {
    return is_numeric($value) && $value % 2 === 0;
}, \'O número deve ser par.\');

// Use in form
$form = $builder->create(\'/submit\', \'POST\')
    ->text(\'number\', \'Número Par\')
        ->rule(\'even_number\')
    ->build();',
                'description' => 'Validação básica de número par'
            ],
            
            'with_parameters' => [
                'code' => '// Register rule with parameters
CustomValidationHelper::registerRule(\'between_exclusive\', function($attribute, $value, $parameters) {
    $min = $parameters[0] ?? 0;
    $max = $parameters[1] ?? 100;
    return $value > $min && $value < $max;
}, \'O valor deve estar entre :param1 e :param2 (exclusivo).\');

// Use in form
$form = $builder->create(\'/submit\', \'POST\')
    ->text(\'percentage\', \'Porcentagem\')
        ->rule(\'between_exclusive:10,90\')
    ->build();',
                'description' => 'Validação com parâmetros'
            ],
            
            'inline_callback' => [
                'code' => '// Create rule from closure inline
$ruleName = CustomValidationHelper::createRuleFromClosure(
    function($attribute, $value) {
        return strpos($value, \'coyote\') !== false;
    },
    \'O valor deve conter "coyote".\'
);

// Use in form
$form = $builder->create(\'/submit\', \'POST\')
    ->text(\'text\', \'Texto\')
        ->rule($ruleName)
    ->build();',
                'description' => 'Callback inline'
            ],
            
            'field_callback' => [
                'code' => '// Add callback directly to field
$field = new TextField(\'username\', \'Nome de Usuário\');
CustomValidationHelper::addCallbackToField(
    $field,
    function($attribute, $value) {
        return preg_match(\'/^[a-zA-Z0-9_]{3,20}$/\', $value);
    },
    \'O nome de usuário deve conter apenas letras, números e underscore (3-20 caracteres).\'
);

// Add field to form
$form->addField($field);',
                'description' => 'Callback direto no campo'
            ],
        ];
    }
}