# Implementação Detalhada - Fase 4D (Form Builder & Integração Final)

## 📋 Status Atual

### ✅ **Já Implementado:**
1. **Field Base Class** (`vendors/coyote/Forms/Field.php`) - Completo
2. **Field Types Implementados:**
   - `TextField.php` - Campo de texto básico
   - `EmailField.php` - Campo de email com validação
   - `PasswordField.php` - Campo de senha com segurança
   - `TextareaField.php` - Campo de texto multilinha

### 🔄 **Próximos Passos (Ordem de Implementação):**

## 1. COMPLETAR TIPOS DE CAMPOS RESTANTES

### 1.1 SelectField.php
```php
<?php

namespace Coyote\Forms\Fields;

use Coyote\Forms\Field;

class SelectField extends Field
{
    protected $options = [];
    protected $multiple = false;
    protected $size = null;
    
    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = 'select';
    }
    
    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }
    
    public function multiple(bool $multiple = true): self
    {
        $this->multiple = $multiple;
        if ($multiple) {
            $this->attribute('multiple', 'multiple');
        } else {
            $this->removeAttribute('multiple');
        }
        return $this;
    }
    
    public function size(int $size): self
    {
        $this->size = $size;
        $this->attribute('size', $size);
        return $this;
    }
    
    public function render(): string
    {
        $attributes = $this->buildAttributes();
        $html = sprintf('<select %s>', $attributes);
        
        foreach ($this->options as $value => $label) {
            $selected = $this->isSelected($value) ? ' selected' : '';
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
                $selected,
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            );
        }
        
        $html .= '</select>';
        return $html;
    }
    
    protected function isSelected($value): bool
    {
        if ($this->multiple && is_array($this->value)) {
            return in_array($value, $this->value);
        }
        
        return (string)$value === (string)$this->value;
    }
}
```

### 1.2 CheckboxField.php
```php
<?php

namespace Coyote\Forms\Fields;

use Coyote\Forms\Field;

class CheckboxField extends Field
{
    protected $checkedValue = '1';
    protected $uncheckedValue = '0';
    
    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = 'checkbox';
    }
    
    public function checkedValue($value): self
    {
        $this->checkedValue = $value;
        return $this;
    }
    
    public function uncheckedValue($value): self
    {
        $this->uncheckedValue = $value;
        return $this;
    }
    
    public function render(): string
    {
        $attributes = $this->buildAttributes();
        
        // Add hidden input for unchecked value
        $html = sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($this->uncheckedValue, ENT_QUOTES, 'UTF-8')
        );
        
        // Checkbox input
        $html .= sprintf('<input type="checkbox" %s>', $attributes);
        
        return $html;
    }
    
    protected function buildAttributes(): string
    {
        $attributes = parent::buildAttributes();
        
        // Add checked attribute if value matches checked value
        if ($this->value == $this->checkedValue) {
            $attributes .= ' checked';
        }
        
        // Override value attribute
        $attributes = preg_replace('/value="[^"]*"/', '', $attributes);
        $attributes .= sprintf(' value="%s"', htmlspecialchars($this->checkedValue, ENT_QUOTES, 'UTF-8'));
        
        return $attributes;
    }
}
```

### 1.3 RadioField.php
```php
<?php

namespace Coyote\Forms\Fields;

use Coyote\Forms\Field;

class RadioField extends Field
{
    protected $options = [];
    
    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = 'radio';
    }
    
    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }
    
    public function render(): string
    {
        $html = '';
        $counter = 0;
        
        foreach ($this->options as $value => $label) {
            $id = $this->name . '_' . $counter++;
            $checked = ((string)$value === (string)$this->value) ? ' checked' : '';
            
            $html .= sprintf(
                '<div class="radio-option">' .
                '<input type="radio" id="%s" name="%s" value="%s"%s>' .
                '<label for="%s">%s</label>' .
                '</div>',
                htmlspecialchars($id, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
                $checked,
                htmlspecialchars($id, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            );
        }
        
        return $html;
    }
}
```

### 1.4 FileField.php
```php
<?php

namespace Coyote\Forms\Fields;

use Coyote\Forms\Field;

class FileField extends Field
{
    protected $accept = null;
    protected $multiple = false;
    
    public function __construct(string $name, ?string $label = null)
    {
        parent::__construct($name, $label);
        $this->type = 'file';
    }
    
    public function accept(string $accept): self
    {
        $this->accept = $accept;
        $this->attribute('accept', $accept);
        return $this;
    }
    
    public function multiple(bool $multiple = true): self
    {
        $this->multiple = $multiple;
        if ($multiple) {
            $this->attribute('multiple', 'multiple');
        } else {
            $this->removeAttribute('multiple');
        }
        return $this;
    }
    
    public function render(): string
    {
        $attributes = $this->buildAttributes();
        return sprintf('<input type="file" %s>', $attributes);
    }
}
```

### 1.5 HiddenField.php
```php
<?php

namespace Coyote\Forms\Fields;

use Coyote\Forms\Field;

class HiddenField extends Field
{
    public function __construct(string $name)
    {
        parent::__construct($name, null);
        $this->type = 'hidden';
    }
    
    public function render(): string
    {
        $attributes = $this->buildAttributes();
        return sprintf('<input type="hidden" %s>', $attributes);
    }
}
```

### 1.6 SubmitField.php
```php
<?php

namespace Coyote\Forms\Fields;

use Coyote\Forms\Field;

class SubmitField extends Field
{
    public function __construct(string $name = 'submit', ?string $label = 'Submit')
    {
        parent::__construct($name, $label);
        $this->type = 'submit';
    }
    
    public function render(): string
    {
        $attributes = $this->buildAttributes();
        return sprintf('<button type="submit" %s>%s</button>', $attributes, htmlspecialchars($this->label, ENT_QUOTES, 'UTF-8'));
    }
}
```

## 2. IMPLEMENTAR FORM CLASS

### 2.1 File: `vendors/coyote/Forms/Form.php`
```php
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
        $data = [];
        foreach ($this->fields as $name => $field) {
            $data[$name] = $field->getValue();
        }
        return $data;
    }
    
    protected function buildFormAttributes(): string
    {
        $attrs = array_merge([
            'method' => $this->method,
            'action' => $this->action,
        ], $this->attributes);
        
        if ($this->method !== 'GET') {
            $attrs['enctype'] = 'multipart/form-data';
        }
        
        $attributes = '';
        foreach ($attrs as $key => $value) {
            $attributes .= sprintf(' %s="%s"', $key, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
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
}
```

## 3. IMPLEMENTAR FORMBUILDER CLASS

### 3.1 File: `vendors/coyote/Forms/FormBuilder.php`
```php
<?php

namespace Coyote\Forms;

use Coyote\Forms\Fields\TextField;
use Coyote\Forms\Fields\EmailField;
use Coyote\Forms\Fields\PasswordField;
use Coyote\Forms\Fields\TextareaField;
use Coyote\Forms\Fields\SelectField;
use Coyote\Forms\Fields\CheckboxField;
use Coyote\Forms\Fields\RadioField;
use Coyote\Forms\Fields\FileField;
use Coyote\Forms\Fields\HiddenField;
use Coyote\Forms\Fields\SubmitField;
use Coyote\Session\SessionInterface;

class FormBuilder
{
    protected $session;
    protected $form;
    protected $currentField;
    
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    
    public function create(string $action = '', string $method = 'POST'): self
    {
        $this->form = new Form($this->session, [
            'action' => $action,
            'method' => $method,
        ]);
        
        return $this;
    }
    
    // Fluent field creation methods
    public function text(string $name, ?string $label = null): self
    {
        $field = new TextField($name, $label);
        $this->form->addField($field);
        $this->currentField = $field;
        return $this;
    }
    
    public function email(string $name, ?string $label = null): self
    {
        $field = new EmailField($name, $label);
        $this->form->addField($field);
        $this->currentField = $field;
        return $this;
    }
    
    public function password(string $name, ?string $label = null): self
    {
        $field = new PasswordField($name, $label);
        $this->form->addField($field);
        $this->currentField = $field;
        return $this;
    }
    
    public function textarea(string $name, ?string $label = null): self
    {
        $field = new TextareaField($name, $label);
        $this->form->addField($field);
        $this->currentField = $field;
        return $this;
    }
    
    public function select(string $name, ?string $label = null): self
    {
        $field = new SelectField($name, $label);
        $this->form->addField($field);
        $this->currentField = $field;
        return $this;
    }
    
    public function checkbox(string $name, ?string $label = null): self
    {
        $field = new CheckboxField($name, $label);
        $this->form->addField($field);
        $this->currentField = $field;
        return $this;
    }
    
    public function radio(string $name, ?string $label = null): self
    {
        $field = new RadioField($name, $label);
        $this->form->addField($field);
        $this->currentField = $field;
        return $this;
    }
    
    public function file(string $name, ?string $label = null): self
    {
        $field = new FileField($name, $label);
        $this->form->addField($field);
        $this->currentField = $field;
        return $this;
    }
    
    public function hidden(string $name): self
    {
        $field = new HiddenField($name);
        $this->form->addField($field);
        $this->currentField