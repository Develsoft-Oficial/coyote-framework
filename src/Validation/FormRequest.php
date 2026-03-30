<?php

namespace Coyote\Validation;

use Coyote\Http\Request;
use Coyote\Validation\Exceptions\ValidationException;

abstract class FormRequest extends Request
{
    /**
     * The validator instance.
     *
     * @var Validator|null
     */
    protected $validator;

    /**
     * Determine if the request passes validation.
     *
     * @var bool
     */
    protected $validated = false;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules(): array;

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Override in child classes if needed
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData(): array
    {
        return $this->all();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     * @throws \Exception
     */
    protected function failedAuthorization(): void
    {
        throw new \Exception('Unauthorized.');
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::fromMessageBag($validator->errors());
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     * @throws ValidationException
     */
    public function validated(): array
    {
        if (!$this->validated) {
            $this->validate();
        }

        return $this->validator->validated();
    }

    /**
     * Validate the request.
     *
     * @return void
     * @throws ValidationException
     */
    public function validate(): void
    {
        if (!$this->authorize()) {
            $this->failedAuthorization();
        }

        $this->prepareForValidation();

        $data = $this->validationData();
        $rules = $this->rules();
        $messages = $this->messages();

        // Merge attribute names into messages
        $attributes = $this->attributes();
        foreach ($attributes as $key => $value) {
            $messages[$key] = str_replace(':attribute', $value, $messages[$key] ?? ':attribute');
        }

        $this->validator = Validator::make($data, $rules, $messages);

        if ($this->validator->fails()) {
            $this->failedValidation($this->validator);
        }

        $this->validated = true;
    }

    /**
     * Get the validator instance.
     *
     * @return Validator|null
     */
    public function validator(): ?Validator
    {
        return $this->validator;
    }

    /**
     * Get validation errors.
     *
     * @return MessageBag
     */
    public function errors(): MessageBag
    {
        if (!$this->validator) {
            $this->validate();
        }

        return $this->validator->errors();
    }

    /**
     * Determine if the request has validation errors.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !$this->errors()->isEmpty();
    }

    /**
     * Get the first validation error.
     *
     * @param string|null $key
     * @return string|null
     */
    public function firstError(?string $key = null): ?string
    {
        return $this->errors()->first($key);
    }

    /**
     * Get all validation errors.
     *
     * @return array
     */
    public function allErrors(): array
    {
        return $this->errors()->all();
    }

    /**
     * Check if a specific field has validation errors.
     *
     * @param string $key
     * @return bool
     */
    public function hasError(string $key): bool
    {
        return $this->errors()->has($key);
    }

    /**
     * Get validation errors for a specific field.
     *
     * @param string $key
     * @return array
     */
    public function getErrors(string $key): array
    {
        return $this->errors()->get($key);
    }

    /**
     * Create a new form request instance with validated data.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return static
     * @throws ValidationException
     */
    public static function create(array $data, array $rules, array $messages = []): self
    {
        $request = new static();
        $request->merge($data);
        
        // Create a custom validator
        $validator = Validator::make($data, $rules, $messages);
        
        if ($validator->fails()) {
            throw ValidationException::fromMessageBag($validator->errors());
        }
        
        $request->validator = $validator;
        $request->validated = true;
        
        return $request;
    }

    /**
     * Get a validated input value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function validatedInput(string $key, $default = null)
    {
        $validated = $this->validated();
        
        return $validated[$key] ?? $default;
    }

    /**
     * Get only validated data for specified keys.
     *
     * @param array $keys
     * @return array
     */
    public function onlyValidated(array $keys): array
    {
        $validated = $this->validated();
        $result = [];
        
        foreach ($keys as $key) {
            if (array_key_exists($key, $validated)) {
                $result[$key] = $validated[$key];
            }
        }
        
        return $result;
    }

    /**
     * Get validated data except for specified keys.
     *
     * @param array $keys
     * @return array
     */
    public function exceptValidated(array $keys): array
    {
        $validated = $this->validated();
        
        foreach ($keys as $key) {
            unset($validated[$key]);
        }
        
        return $validated;
    }
}