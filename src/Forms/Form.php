<?php

namespace Coyote\Forms;

use Coyote\Session\SessionInterface;
use Coyote\Validation\Validator;

class Form
{
    protected $fields = [];
    protected $method = 'POST';
    protected $action = '';
    protected $attributes = [];
    protected $csrfEnabled = true;
    protected $csrfToken;
    protected $session;
    protected $errors = [];
    protected $validated = false;
    protected $oldInput = [];
    
    public function __construct(SessionInterface $session, array $options = [])
    {
        $this->session = $session;
        $this->method = strtoupper($options['method'] ?? 'POST');
        $this->action = $options['action'] ?? '';
        $this->attributes = $options['attributes'] ?? [];
        $this->csrfEnabled = $options['csrf'] ?? true;
        
        // Load old input from session
        $this->loadOldInput();
        
        // Generate CSRF token if enabled
        if ($this->csrfEnabled && $this->method !== 'GET') {
            $this->generateCsrfToken();
        }
    }
    
    public function addField(Field $field): self
    {
        $this->fields[$field->getName()] = $field;
        
        // Populate field value from old input if available
        if (isset($this->oldInput[$field->getName()])) {
            $field->value($this->oldInput[$field->getName()]);
        }
        
        return $this;
    }
    
    public function validate(array $data): bool
    {
        $this->errors = [];
        $allValid = true;
        
        // Validate CSRF token if enabled
        if ($this->csrfEnabled && $this->method !== 'GET' && !$this->validateCsrfToken($data)) {
            $this->errors['_csrf'] = ['Invalid CSRF token'];
            return false;
        }
        
        // Validate each field
        foreach ($this->fields as $name => $field) {
            $value = $data[$name] ?? null;
            
            if (!$field->validate($value)) {
                $this->errors[$name] = $field->getErrors();
                $allValid = false;
            }
        }
        
        $this->validated = $allValid;
        
        // Store validated data as old input for next request
        if ($allValid) {
            $this->storeOldInput($data);
        }
        
        return $allValid;
    }
    
    public function render(): string
    {
        $attributes = $this->buildFormAttributes();
        $fieldsHtml = '';
        
        foreach ($this->fields as $field) {
            $fieldsHtml .= $field->render();
        }
        
        // Add CSRF token field if enabled
        if ($this->csrfEnabled && $this->method !== 'GET') {
            $fieldsHtml .= $this->renderCsrfField();
        }
        
        return sprintf(
            '<form %s>%s</form>',
            $attributes,
            $fieldsHtml
        );
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    public function error(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
    
    public function getValidatedData(): array
    {
        if (!$this->validated) {
            throw new \RuntimeException('Form has not been validated yet. Call validate() first.');
        }
        
        $data = [];
        foreach ($this->fields as $name => $field) {
            $data[$name] = $field->getValue();
        }
        return $data;
    }
    
    public function getField(string $name): ?Field
    {
        return $this->fields[$name] ?? null;
    }
    
    public function getFields(): array
    {
        return $this->fields;
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }
    
    public function getAction(): string
    {
        return $this->action;
    }
    
    public function isCsrfEnabled(): bool
    {
        return $this->csrfEnabled;
    }
    
    public function getCsrfToken(): ?string
    {
        return $this->csrfToken;
    }
    
    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }
    
    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);
        
        // Regenerate CSRF token if method changes to/from GET
        if ($this->csrfEnabled && $this->method !== 'GET') {
            $this->generateCsrfToken();
        }
        
        return $this;
    }
    
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }
    
    public function setCsrfEnabled(bool $enabled): self
    {
        $this->csrfEnabled = $enabled;
        
        if ($enabled && $this->method !== 'GET') {
            $this->generateCsrfToken();
        }
        
        return $this;
    }
    
    protected function buildFormAttributes(): string
    {
        $attrs = array_merge([
            'method' => $this->method,
            'action' => $this->action,
        ], $this->attributes);
        
        // Add enctype for file uploads if method is not GET
        if ($this->method !== 'GET') {
            $attrs['enctype'] = 'multipart/form-data';
        }
        
        $attributes = '';
        foreach ($attrs as $key => $value) {
            if ($value !== null && $value !== '') {
                $attributes .= sprintf(' %s="%s"', $key, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        }
        
        return trim($attributes);
    }
    
    protected function generateCsrfToken(): void
    {
        $this->csrfToken = bin2hex(random_bytes(32));
        $this->session->put('_csrf_tokens.' . $this->csrfToken, time());
    }
    
    protected function validateCsrfToken(array $data): bool
    {
        $token = $data['_csrf_token'] ?? null;
        
        if (!$token || !$this->session->has('_csrf_tokens.' . $token)) {
            return false;
        }
        
        // Clean up old tokens
        $this->cleanupOldCsrfTokens();
        
        return true;
    }
    
    protected function cleanupOldCsrfTokens(): void
    {
        $tokens = $this->session->get('_csrf_tokens', []);
        $now = time();
        $expired = 3600; // 1 hour
        
        foreach ($tokens as $token => $timestamp) {
            if ($now - $timestamp > $expired) {
                $this->session->forget('_csrf_tokens.' . $token);
            }
        }
    }
    
    protected function renderCsrfField(): string
    {
        return sprintf(
            '<input type="hidden" name="_csrf_token" value="%s">',
            htmlspecialchars($this->csrfToken, ENT_QUOTES, 'UTF-8')
        );
    }
    
    protected function loadOldInput(): void
    {
        $this->oldInput = $this->session->get('_old_input', []);
    }
    
    protected function storeOldInput(array $data): void
    {
        $this->session->flash('_old_input', $data);
    }
    
    public function renderErrorSummary(): string
    {
        if (empty($this->errors)) {
            return '';
        }
        
        $html = '<div class="alert alert-danger">';
        $html .= '<ul>';
        
        foreach ($this->errors as $field => $errors) {
            foreach ($errors as $error) {
                $html .= sprintf('<li>%s</li>', htmlspecialchars($error, ENT_QUOTES, 'UTF-8'));
            }
        }
        
        $html .= '</ul></div>';
        return $html;
    }
    
    public function renderFieldErrors(string $fieldName): string
    {
        $errors = $this->errors[$fieldName] ?? [];
        
        if (empty($errors)) {
            return '';
        }
        
        $html = '<div class="invalid-feedback">';
        foreach ($errors as $error) {
            $html .= sprintf('<div>%s</div>', htmlspecialchars($error, ENT_QUOTES, 'UTF-8'));
        }
        $html .= '</div>';
        
        return $html;
    }
    
    public function old(string $field, $default = null)
    {
        return $this->oldInput[$field] ?? $default;
    }
}