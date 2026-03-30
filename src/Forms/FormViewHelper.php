<?php
// vendors/coyote/Forms/FormViewHelper.php

namespace Coyote\Forms;

use Coyote\Forms\Form;
use Coyote\Forms\Field;

/**
 * FormViewHelper - Helper para integração do Form Builder com sistema de templates
 */
class FormViewHelper
{
    protected $form;
    protected $options = [];
    protected $defaultClasses = [
        'form' => 'coyote-form',
        'form_group' => 'form-group',
        'label' => 'form-label',
        'input' => 'form-control',
        'textarea' => 'form-control',
        'select' => 'form-control',
        'checkbox' => 'form-check-input',
        'radio' => 'form-check-input',
        'file' => 'form-control-file',
        'submit' => 'btn btn-primary',
        'error' => 'error-message',
        'has_error' => 'has-error',
    ];
    
    public function __construct(Form $form, array $options = [])
    {
        $this->form = $form;
        $this->options = array_merge([
            'use_default_classes' => true,
            'wrap_fields' => true,
            'show_labels' => true,
            'show_errors' => true,
            'field_wrapper_tag' => 'div',
            'label_tag' => 'label',
            'error_tag' => 'span',
        ], $options);
    }
    
    public function renderForm(array $attributes = []): string
    {
        $formAttributes = $this->buildFormAttributes($attributes);
        $fieldsHtml = $this->renderFields();
        
        return sprintf('<form %s>%s</form>', $formAttributes, $fieldsHtml);
    }
    
    public function renderFields(): string
    {
        $html = '';
        
        foreach ($this->form->getFields() as $field) {
            $html .= $this->renderField($field->getName());
        }
        
        if ($this->form->isCsrfEnabled() && $this->form->getMethod() !== 'GET') {
            $html .= $this->renderCsrfField();
        }
        
        return $html;
    }
    
    public function renderField(string $fieldName, array $options = []): string
    {
        $field = $this->form->getField($fieldName);
        
        if (!$field) {
            return '';
        }
        
        $options = array_merge($this->options, $options);
        $html = '';
        
        if ($options['wrap_fields']) {
            $wrapperClass = $this->getClass('form_group');
            if ($this->form->hasError($fieldName)) {
                $wrapperClass .= ' ' . $this->getClass('has_error');
            }
            
            $html .= sprintf('<%s class="%s">',
                $options['field_wrapper_tag'],
                $wrapperClass
            );
        }
        
        if ($options['show_labels'] && $field->getType() !== 'hidden' && $field->getType() !== 'submit') {
            $html .= $this->renderLabel($field, $options);
        }
        
        $html .= $this->renderFieldInput($field, $options);
        
        if ($options['show_errors'] && $this->form->hasError($fieldName)) {
            $html .= $this->renderError($field, $options);
        }
        
        if ($options['wrap_fields']) {
            $html .= sprintf('</%s>', $options['field_wrapper_tag']);
        }
        
        return $html;
    }
    
    protected function renderLabel(Field $field, array $options): string
    {
        $labelClass = $this->getClass('label');
        $required = $field->isRequired() ? ' <span class="required">*</span>' : '';
        
        return sprintf('<%s for="%s" class="%s">%s%s</%s>',
            $options['label_tag'],
            $this->getFieldId($field),
            $labelClass,
            htmlspecialchars($field->getLabel()),
            $required,
            $options['label_tag']
        );
    }
    
    protected function renderFieldInput(Field $field, array $options): string
    {
        $type = $field->getType();
        
        switch ($type) {
            case 'textarea':
                return $this->renderTextarea($field);
            case 'select':
                return $this->renderSelect($field);
            case 'checkbox':
                return $this->renderCheckbox($field);
            case 'radio':
                return $this->renderRadio($field);
            case 'file':
                return $this->renderFile($field);
            case 'submit':
                return $this->renderSubmit($field);
            default:
                return $this->renderInput($field);
        }
    }
    
    protected function renderInput(Field $field): string
    {
        $attributes = $this->buildFieldAttributes($field);
        $class = $this->getClass('input');
        
        if ($class) {
            $attributes['class'] = isset($attributes['class']) 
                ? $attributes['class'] . ' ' . $class
                : $class;
        }
        
        return sprintf('<input %s>', $this->buildAttributesString($attributes));
    }
    
    protected function renderTextarea(Field $field): string
    {
        $attributes = $this->buildFieldAttributes($field);
        $class = $this->getClass('textarea');
        
        if ($class) {
            $attributes['class'] = isset($attributes['class']) 
                ? $attributes['class'] . ' ' . $class
                : $class;
        }
        
        $value = htmlspecialchars($attributes['value'] ?? '');
        unset($attributes['value']);
        
        return sprintf('<textarea %s>%s</textarea>', 
            $this->buildAttributesString($attributes), 
            $value
        );
    }
    
    protected function renderSelect(Field $field): string
    {
        $attributes = $this->buildFieldAttributes($field);
        $class = $this->getClass('select');
        
        if ($class) {
            $attributes['class'] = isset($attributes['class']) 
                ? $attributes['class'] . ' ' . $class
                : $class;
        }
        
        $optionsHtml = '';
        $fieldValue = $attributes['value'] ?? null;
        unset($attributes['value']);
        
        if (method_exists($field, 'getOptions')) {
            foreach ($field->getOptions() as $value => $label) {
                $selected = ($value == $fieldValue) ? ' selected' : '';
                $optionsHtml .= sprintf('<option value="%s"%s>%s</option>',
                    htmlspecialchars($value),
                    $selected,
                    htmlspecialchars($label)
                );
            }
        }
        
        return sprintf('<select %s>%s</select>', 
            $this->buildAttributesString($attributes), 
            $optionsHtml
        );
    }
    
    protected function renderCheckbox(Field $field): string
    {
        $attributes = $this->buildFieldAttributes($field);
        $class = $this->getClass('checkbox');
        
        if ($class) {
            $attributes['class'] = isset($attributes['class']) 
                ? $attributes['class'] . ' ' . $class
                : $class;
        }
        
        $checked = '';
        if (isset($attributes['value'])) {
            $checkedValue = method_exists($field, 'getCheckedValue') 
                ? $field->getCheckedValue() 
                : '1';
            
            if ($attributes['value'] == $checkedValue) {
                $checked = ' checked';
            }
        }
        
        return sprintf('<input type="checkbox" %s%s>', 
            $this->buildAttributesString($attributes), 
            $checked
        );
    }
    
    protected function renderRadio(Field $field): string
    {
        $attributes = $this->buildFieldAttributes($field);
        $class = $this->getClass('radio');
        
        if ($class) {
            $attributes['class'] = isset($attributes['class']) 
                ? $attributes['class'] . ' ' . $class
                : $class;
        }
        
        return sprintf('<input type="radio" %s>', 
            $this->buildAttributesString($attributes)
        );
    }
    
    protected function renderFile(Field $field): string
    {
        $attributes = $this->buildFieldAttributes($field);
        $class = $this->getClass('file');
        
        if ($class) {
            $attributes['class'] = isset($attributes['class']) 
                ? $attributes['class'] . ' ' . $class
                : $class;
        }
        
        return sprintf('<input type="file" %s>', 
            $this->buildAttributesString($attributes)
        );
    }
    
    protected function renderSubmit(Field $field): string
    {
        $attributes = $this->buildFieldAttributes($field);
        $class = $this->getClass('submit');
        
        if ($class) {
            $attributes['class'] = isset($attributes['class']) 
                ? $attributes['class'] . ' ' . $class
                : $class;
        }
        
        if (!isset($attributes['value']) && $field->getLabel()) {
            $attributes['value'] = $field->getLabel();
        }
        
        return sprintf('<input type="submit" %s>', 
            $this->buildAttributesString($attributes)
        );
    }
    
    protected function renderError(Field $field, array $options): string
    {
        $errorClass = $this->getClass('error');
        $error = $this->form->getError($field->getName());
        
        if (is_array($error)) {
            $error = implode(', ', $error);
        }
        
        return sprintf('<%s class="%s">%s</%s>',
            $options['error_tag'],
            $errorClass,
            htmlspecialchars($error),
            $options['error_tag']
        );
    }
    
    protected function renderCsrfField(): string
    {
        $token = $this->form->getCsrfToken();
        
        if (!$token) {
            return '';
        }
        
        return sprintf('<input type="hidden" name="_token" value="%s">',
            htmlspecialchars($token)
        );
    }
    
    protected function buildFormAttributes(array $additionalAttributes = []): string
    {
        $attributes = [
            'method' => $this->form->getMethod(),
            'action' => $this->form->getAction(),
        ];
        
        $hasFileField = false;
        foreach ($this->form->getFields() as $field) {
            if ($field->getType() === 'file') {
                $hasFileField = true;
                break;
            }
        }
        
        if ($hasFileField) {
            $attributes['enctype'] = 'multipart/form-data';
        }
        
        $formClass = $this->getClass('form');
        if ($formClass) {
            $attributes['class'] = isset($attributes['class']) 
                ? $attributes['class'] . ' ' . $formClass
                : $formClass;
        }
        
        $attributes = array_merge($attributes, $additionalAttributes);
        
        return $this->buildAttributesString($attributes);
    }
    
    protected function buildFieldAttributes(Field $field): array
    {
        $attributes = [
            'type' => $field->getType(),
            'name' => $field->getName(),
            'id' => $this->getFieldId($field),
        ];
        
        $value = $field->getValue();
        if ($value !== null && $field->getType() !== 'textarea' && $field->getType() !== 'select') {
            $attributes['value'] = $value;
        }
        
        if (method_exists($field, 'getAttributes')) {
            $fieldAttributes = $field->getAttributes();
            if (is_array($fieldAttributes)) {
                $attributes = array_merge($attributes, $fieldAttributes);
            }
        }
        
        if ($field->isRequired()) {
            $attributes['required'] = 'required';
        }
        
        return $attributes;
    }
    
    protected function getFieldId(Field $field): string
    {
        return 'field_' . $field->getName();
    }
    
    protected function getClass(string $type): string
    {
        if (!$this->options['use_default_classes']) {
            return '';
        }
        
        return $this->defaultClasses[$type] ?? '';
    }
    
    protected function buildAttributesString(array $attributes): string
    {
        $parts = [];
        
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            
            if ($value === true) {
                $parts[] = $key;
            } else {
                $parts[] = sprintf('%s="%s"', $key, htmlspecialchars($value));
            }
        }
        
        return implode(' ', $parts);
    }
}
