<?php

namespace Coyote\Forms;

use Coyote\Validation\Validator;

abstract class Field
{
    /**
     * The field name.
     *
     * @var string
     */
    protected $name;

    /**
     * The field type.
     *
     * @var string
     */
    protected $type;

    /**
     * The field label.
     *
     * @var string|null
     */
    protected $label;

    /**
     * The field value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * HTML attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Validation errors.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Whether the field is required.
     *
     * @var bool
     */
    protected $required = false;

    /**
     * Create a new field instance.
     *
     * @param string $name
     * @param string|null $label
     */
    public function __construct(string $name, ?string $label = null)
    {
        $this->name = $name;
        $this->label = $label ?? $this->formatLabel($name);
    }

    /**
     * Format a field name into a label.
     *
     * @param string $name
     * @return string
     */
    protected function formatLabel(string $name): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $name));
    }

    /**
     * Set the field value.
     *
     * @param mixed $value
     * @return $this
     */
    public function value($value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get the field value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set an HTML attribute.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function attribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Set multiple HTML attributes.
     *
     * @param array $attributes
     * @return $this
     */
    public function attributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Set the field as required.
     *
     * @param bool $required
     * @return $this
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;
        
        if ($required && !in_array('required', $this->rules)) {
            $this->rules[] = 'required';
        } elseif (!$required && ($key = array_search('required', $this->rules)) !== false) {
            unset($this->rules[$key]);
            $this->rules = array_values($this->rules);
        }
        
        return $this;
    }

    /**
     * Add a validation rule.
     *
     * @param string $rule
     * @return $this
     */
    public function rule(string $rule): self
    {
        if (!in_array($rule, $this->rules)) {
            $this->rules[] = $rule;
        }
        
        return $this;
    }

    /**
     * Add multiple validation rules.
     *
     * @param array $rules
     * @return $this
     */
    public function rules(array $rules): self
    {
        foreach ($rules as $rule) {
            $this->rule($rule);
        }
        
        return $this;
    }

    /**
     * Get the validation rules.
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Set validation errors.
     *
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if the field has errors.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Validate the field value.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value): bool
    {
        if (empty($this->rules)) {
            return true;
        }

        $validator = Validator::make(
            [$this->name => $value],
            [$this->name => implode('|', $this->rules)]
        );

        if ($validator->fails()) {
            $this->errors = $validator->errors()->get($this->name);
            return false;
        }

        $this->errors = [];
        return true;
    }

    /**
     * Render the field.
     *
     * @return string
     */
    abstract public function render(): string;

    /**
     * Build HTML attributes string.
     *
     * @return string
     */
    protected function buildAttributes(): string
    {
        $attributes = [];
        
        foreach ($this->attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $attributes[] = $key;
                }
            } else {
                $attributes[] = sprintf('%s="%s"', $key, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        }
        
        return implode(' ', $attributes);
    }

    /**
     * Get the field name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the field type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the field label.
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set the field label.
     *
     * @param string $label
     * @return $this
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Set placeholder attribute.
     *
     * @param string $placeholder
     * @return $this
     */
    public function placeholder(string $placeholder): self
    {
        return $this->attribute('placeholder', $placeholder);
    }

    /**
     * Set disabled attribute.
     *
     * @param bool $disabled
     * @return $this
     */
    public function disabled(bool $disabled = true): self
    {
        return $this->attribute('disabled', $disabled);
    }

    /**
     * Set readonly attribute.
     *
     * @param bool $readonly
     * @return $this
     */
    public function readonly(bool $readonly = true): self
    {
        return $this->attribute('readonly', $readonly);
    }

    /**
     * Set autofocus attribute.
     *
     * @param bool $autofocus
     * @return $this
     */
    public function autofocus(bool $autofocus = true): self
    {
        return $this->attribute('autofocus', $autofocus);
    }
}