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
        $this->currentField = $field;
        return $this;
    }
    
    public function submit(string $name = 'submit', ?string $label = 'Submit'): self
    {
        $field = new SubmitField($name, $label);
        $this->form->addField($field);
        $this->currentField = $field;
        return $this;
    }
    
    // Field configuration methods (fluent API)
    public function required(bool $required = true): self
    {
        if ($this->currentField) {
            $this->currentField->required($required);
        }
        return $this;
    }
    
    public function rule(string $rule): self
    {
        if ($this->currentField) {
            $this->currentField->rule($rule);
        }
        return $this;
    }
    
    public function value($value): self
    {
        if ($this->currentField) {
            $this->currentField->value($value);
        }
        return $this;
    }
    
    public function attribute(string $key, $value): self
    {
        if ($this->currentField) {
            $this->currentField->attribute($key, $value);
        }
        return $this;
    }
    
    public function placeholder(string $placeholder): self
    {
        if ($this->currentField) {
            $this->currentField->placeholder($placeholder);
        }
        return $this;
    }
    
    public function disabled(bool $disabled = true): self
    {
        if ($this->currentField) {
            $this->currentField->disabled($disabled);
        }
        return $this;
    }
    
    public function readonly(bool $readonly = true): self
    {
        if ($this->currentField) {
            $this->currentField->readonly($readonly);
        }
        return $this;
    }
    
    public function autofocus(bool $autofocus = true): self
    {
        if ($this->currentField) {
            $this->currentField->autofocus($autofocus);
        }
        return $this;
    }
    
    // Special field-specific methods
    public function options(array $options): self
    {
        if ($this->currentField instanceof SelectField || $this->currentField instanceof RadioField) {
            $this->currentField->options($options);
        }
        return $this;
    }
    
    public function multiple(bool $multiple = true): self
    {
        if ($this->currentField instanceof SelectField || $this->currentField instanceof FileField) {
            $this->currentField->multiple($multiple);
        }
        return $this;
    }
    
    public function accept(string $accept): self
    {
        if ($this->currentField instanceof FileField) {
            $this->currentField->accept($accept);
        }
        return $this;
    }
    
    // Enhanced file validation methods
    public function maxSize(int $bytes): self
    {
        if ($this->currentField instanceof FileField) {
            $this->currentField->maxSize($bytes);
        }
        return $this;
    }
    
    public function maxSizeMb(float $megabytes): self
    {
        if ($this->currentField instanceof FileField) {
            $this->currentField->maxSizeMb($megabytes);
        }
        return $this;
    }
    
    public function minSize(int $bytes): self
    {
        if ($this->currentField instanceof FileField) {
            $this->currentField->minSize($bytes);
        }
        return $this;
    }
    
    public function allowedMimeTypes(array $mimeTypes): self
    {
        if ($this->currentField instanceof FileField) {
            $this->currentField->allowedMimeTypes($mimeTypes);
        }
        return $this;
    }
    
    public function imageOnly(): self
    {
        if ($this->currentField instanceof FileField) {
            $this->currentField->imageOnly();
        }
        return $this;
    }
    
    public function pdfOnly(): self
    {
        if ($this->currentField instanceof FileField) {
            $this->currentField->pdfOnly();
        }
        return $this;
    }
    
    public function documentOnly(): self
    {
        if ($this->currentField instanceof FileField) {
            $this->currentField->documentOnly();
        }
        return $this;
    }
    
    public function rows(int $rows): self
    {
        if ($this->currentField instanceof TextareaField) {
            $this->currentField->rows($rows);
        }
        return $this;
    }
    
    public function cols(int $cols): self
    {
        if ($this->currentField instanceof TextareaField) {
            $this->currentField->cols($cols);
        }
        return $this;
    }
    
    public function checkedValue($value): self
    {
        if ($this->currentField instanceof CheckboxField) {
            $this->currentField->checkedValue($value);
        }
        return $this;
    }
    
    public function uncheckedValue($value): self
    {
        if ($this->currentField instanceof CheckboxField) {
            $this->currentField->uncheckedValue($value);
        }
        return $this;
    }
    
    // Form-level configuration
    public function action(string $action): self
    {
        if ($this->form) {
            $this->form->setAction($action);
        }
        return $this;
    }
    
    public function method(string $method): self
    {
        if ($this->form) {
            $this->form->setMethod($method);
        }
        return $this;
    }
    
    public function attributeForm(string $key, $value): self
    {
        if ($this->form) {
            $this->form->setAttribute($key, $value);
        }
        return $this;
    }
    
    public function csrf(bool $enabled = true): self
    {
        if ($this->form) {
            $this->form->setCsrfEnabled($enabled);
        }
        return $this;
    }
    
    public function build(): Form
    {
        if (!$this->form) {
            throw new \RuntimeException('No form created. Call create() first.');
        }
        
        $form = $this->form;
        
        // Reset builder state
        $this->form = null;
        $this->currentField = null;
        
        return $form;
    }
    
    // Helper method for quick form creation
    public static function make(SessionInterface $session, string $action = '', string $method = 'POST'): self
    {
        return (new self($session))->create($action, $method);
    }
}