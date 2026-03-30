# Form Builder API Documentation

## Overview

The Coyote Framework Form Builder provides a fluent, object-oriented interface for creating HTML forms with built-in validation, CSRF protection, and session-based old input handling.

## Table of Contents

1. [Quick Start](#quick-start)
2. [Form Creation](#form-creation)
3. [Field Types](#field-types)
4. [Field Configuration](#field-configuration)
5. [Form Configuration](#form-configuration)
6. [Validation](#validation)
7. [Rendering](#rendering)
8. [File Uploads](#file-uploads)
9. [Custom Validation](#custom-validation)
10. [Integration with Controllers](#integration-with-controllers)
11. [Integration with Views](#integration-with-views)
12. [API Reference](#api-reference)

## Quick Start

```php
use Coyote\Forms\FormBuilder;
use Coyote\Session\SessionManager;

// Initialize session
$session = new SessionManager($config);
$session->start();

// Create form builder
$builder = new FormBuilder($session);

// Build a form
$form = $builder->create('/submit', 'POST')
    ->text('name', 'Full Name')
        ->required()
        ->placeholder('Enter your name')
    ->email('email', 'Email Address')
        ->required()
        ->rule('email')
    ->password('password', 'Password')
        ->required()
        ->rule('min:8')
    ->submit('submit', 'Submit')
    ->build();

// Render the form
echo $form->render();
```

## Form Creation

### Basic Form Creation

```php
// Method 1: Using constructor
$builder = new FormBuilder($session);
$form = $builder->create('/action', 'POST')->build();

// Method 2: Using static make() method
$form = FormBuilder::make($session, '/action', 'POST')->build();
```

### Form Configuration Methods

- `action(string $action)`: Set form action URL
- `method(string $method)`: Set form method (GET, POST, PUT, DELETE)
- `csrf(bool $enabled = true)`: Enable/disable CSRF protection
- `attributeForm(string $key, $value)`: Add custom HTML attribute to form tag

## Field Types

### Available Field Types

| Method | Field Class | HTML Output |
|--------|-------------|-------------|
| `text(string $name, ?string $label)` | `TextField` | `<input type="text">` |
| `email(string $name, ?string $label)` | `EmailField` | `<input type="email">` |
| `password(string $name, ?string $label)` | `PasswordField` | `<input type="password">` |
| `textarea(string $name, ?string $label)` | `TextareaField` | `<textarea></textarea>` |
| `select(string $name, ?string $label)` | `SelectField` | `<select></select>` |
| `checkbox(string $name, ?string $label)` | `CheckboxField` | `<input type="checkbox">` |
| `radio(string $name, ?string $label)` | `RadioField` | `<input type="radio">` |
| `file(string $name, ?string $label)` | `FileField` | `<input type="file">` |
| `hidden(string $name)` | `HiddenField` | `<input type="hidden">` |
| `submit(string $name, ?string $label)` | `SubmitField` | `<input type="submit">` |

### Field Creation Examples

```php
$form = $builder->create('/submit', 'POST')
    // Text field
    ->text('username', 'Username')
    
    // Email field with validation
    ->email('email', 'Email Address')
        ->rule('email')
    
    // Password field with confirmation
    ->password('password', 'Password')
    ->password('password_confirmation', 'Confirm Password')
        ->rule('confirmed')
    
    // Textarea with dimensions
    ->textarea('message', 'Message')
        ->rows(5)
        ->cols(40)
    
    // Select with options
    ->select('country', 'Country')
        ->options([
            'us' => 'United States',
            'br' => 'Brazil',
            'uk' => 'United Kingdom',
        ])
    
    // Checkbox with custom values
    ->checkbox('terms', 'Accept Terms')
        ->checkedValue('accepted')
        ->uncheckedValue('declined')
    
    // File upload
    ->file('avatar', 'Profile Picture')
        ->accept('image/*')
    
    // Submit button
    ->submit('submit', 'Save')
    ->build();
```

## Field Configuration

### Common Configuration Methods

These methods can be chained after any field creation:

- `required(bool $required = true)`: Mark field as required
- `value($value)`: Set field value
- `placeholder(string $placeholder)`: Set placeholder text
- `disabled(bool $disabled = true)`: Disable the field
- `readonly(bool $readonly = true)`: Make field read-only
- `autofocus(bool $autofocus = true)`: Set autofocus attribute
- `attribute(string $key, $value)`: Add custom HTML attribute
- `rule(string $rule)`: Add validation rule

### Field-Specific Configuration

#### Select and Radio Fields
- `options(array $options)`: Set available options

#### Textarea Fields
- `rows(int $rows)`: Set number of rows
- `cols(int $cols)`: Set number of columns

#### Checkbox Fields
- `checkedValue($value)`: Set value when checked
- `uncheckedValue($value)`: Set value when unchecked

#### File Fields
- `accept(string $accept)`: Set accepted file types (e.g., "image/*", ".pdf,.doc")
- `multiple(bool $multiple = true)`: Allow multiple file selection

## Form Configuration

### CSRF Protection

CSRF protection is enabled by default for non-GET forms:

```php
$form = $builder->create('/submit', 'POST')
    ->csrf() // Enabled by default
    ->text('name', 'Name')
    ->build();
```

To disable CSRF:
```php
$form = $builder->create('/submit', 'POST')
    ->csrf(false)
    ->text('name', 'Name')
    ->build();
```

### Form Attributes

Add custom attributes to the form tag:

```php
$form = $builder->create('/submit', 'POST')
    ->attributeForm('class', 'form-horizontal')
    ->attributeForm('id', 'user-form')
    ->attributeForm('enctype', 'multipart/form-data')
    ->text('name', 'Name')
    ->build();
```

## Validation

### Built-in Validation Rules

The Form Builder integrates with Coyote's validation system. Available rules include:

- `required`: Field must be present
- `email`: Valid email format
- `min:X`: Minimum length/value
- `max:X`: Maximum length/value
- `numeric`: Must be numeric
- `string`: Must be a string
- `array`: Must be an array
- `confirmed`: Field must match confirmation field
- `different:field`: Must be different from another field
- `same:field`: Must be same as another field
- `in:value1,value2`: Must be in list of values
- `not_in:value1,value2`: Must not be in list of values
- `regex:pattern`: Must match regex pattern
- `date`: Valid date
- `boolean`: Must be boolean
- `ip`: Valid IP address
- `url`: Valid URL

### Validation Example

```php
$form = $builder->create('/register', 'POST')
    ->text('username', 'Username')
        ->required()
        ->rule('min:3')
        ->rule('max:20')
    ->email('email', 'Email')
        ->required()
        ->rule('email')
    ->password('password', 'Password')
        ->required()
        ->rule('min:8')
    ->password('password_confirmation', 'Confirm Password')
        ->required()
        ->rule('confirmed')
    ->build();

// Validate submitted data
if ($form->validate($_POST)) {
    // Data is valid
    $validData = $form->getValidatedData();
} else {
    // Get validation errors
    $errors = $form->getErrors();
}
```

### Accessing Validation Results

- `validate(array $data)`: Validate form data, returns boolean
- `isValid()`: Check if form passed validation
- `getErrors()`: Get validation errors
- `getError(string $field)`: Get error for specific field
- `hasErrors()`: Check if form has errors
- `getValidatedData()`: Get validated data

## Rendering

### Basic Rendering

```php
// Render complete form
echo $form->render();

// Render form without CSRF token (for AJAX forms)
echo $form->renderWithoutCsrf();

// Render individual fields
foreach ($form->getFields() as $field) {
    echo $field->render();
}
```

### Custom Rendering

You can extend the Form and Field classes to customize rendering:

```php
class CustomForm extends \Coyote\Forms\Form
{
    public function render(): string
    {
        $html = '<div class="custom-form">';
        $html .= parent::render();
        $html .= '</div>';
        return $html;
    }
}
```

## File Uploads

### Basic File Upload

```php
$form = $builder->create('/upload', 'POST')
    ->file('document', 'Upload Document')
        ->required()
        ->accept('.pdf,.doc,.docx')
    ->submit('upload', 'Upload File')
    ->build();
```

### Multiple File Upload

```php
$form = $builder->create('/upload', 'POST')
    ->file('photos', 'Select Photos')
        ->multiple()
        ->accept('image/*')
    ->submit('upload', 'Upload Photos')
    ->build();
```

### Server-side File Handling

```php
if ($form->validate($_POST)) {
    $file = $_FILES['document'] ?? null;
    
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        // Move uploaded file
        move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    }
}
```

## Custom Validation

### Custom Rule Classes

Create custom validation rules by extending the `Rule` class:

```php
use Coyote\Validation\Rules\Rule;

class CustomRule extends Rule
{
    public function validate($value): bool
    {
        // Custom validation logic
        return $value === 'custom';
    }
    
    public function message(): string
    {
        return 'The field must be "custom".';
    }
}
```

### Using Custom Rules

```php
// Register custom rule
\Coyote\Validation\Validator::extend('custom', CustomRule::class);

// Use in form
$form = $builder->create('/submit', 'POST')
    ->text('field', 'Custom Field')
        ->rule('custom')
    ->build();
```

### Closure-based Validation

```php
use Coyote\Validation\Validator;

// Add custom validation using closure
Validator::extend('even', function($attribute, $value, $parameters) {
    return is_numeric($value) && $value % 2 === 0;
});

// Use in form
$form = $builder->create('/submit', 'POST')
    ->text('number', 'Even Number')
        ->rule('even')
    ->build();
```

## Integration with Controllers

### Basic Controller Integration

```php
namespace App\Controllers;

use Coyote\Http\Controllers\Controller;
use Coyote\Forms\FormBuilder;

class UserController extends Controller
{
    public function create(FormBuilder $builder)
    {
        $form = $builder->create('/users/store', 'POST')
            ->text('name', 'Name')
                ->required()
            ->email('email', 'Email')
                ->required()
                ->rule('email')
            ->submit('submit', 'Create User')
            ->build();
        
        return view('users.create', ['form' => $form]);
    }
    
    public function store(FormBuilder $builder)
    {
        $form = $builder->create('/users/store', 'POST')
            ->text('name', 'Name')
                ->required()
            ->email('email', 'Email')
                ->required()
                ->rule('email')
            ->submit('submit', 'Create User')
            ->build();
        
        if ($form->validate(request()->all())) {
            // Create user
            $data = $form->getValidatedData();
            User::create($data);
            
            return redirect('/users')->with('success', 'User created!');
        }
        
        // Return with errors
        return back()->withErrors($form->getErrors());
    }
}
```

### Form Request Validation

```php
use Coyote\Validation\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ];
    }
    
    public function authorize(): bool
    {
        return auth()->check();
    }
}

// In controller
public function store(CreateUserRequest $request)
{
    // Data is already validated
    $validated = $request->validated();
    User::create($validated);
    
    return redirect('/users');
}
```

## Integration with Views

### Basic View Integration

```php
// In controller
public function create()
{
    $form = FormBuilder::make(session(), '/users/store', 'POST')
        ->text('name', 'Name')
        ->email('email', 'Email')
        ->submit('submit', 'Save')
        ->build();
    
    return view('users.form', compact('form'));
}
```

```html
<!-- resources/views/users/form.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
</head>
<body>
    <h1>Create User</h1>
    
    <?= $form->render() ?>
    
    <!-- Or render manually -->
    <form action="/users/store" method="POST">
        <?= csrf_field() ?>
        
        <div>
            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="<?= old('name') ?>">
            <?php if ($form->hasError('name')): ?>
                <span class="error"><?= $form->getError('name') ?></span>
            <?php endif; ?>
        </div>
        
        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= old('email') ?>">
            <?php if ($form->hasError('email')): ?>
                <span class="error"><?= $form->getError('email') ?></span>
            <?php endif; ?>
        </div>
        
        <button type="submit">Save</button>
    </form>
</body>
</html>
```

### Blade Template Integration

```blade
{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Create User</h1>
    
    {!! $form->render() !!}
    
    {{-- Or using form components --}}
    <x-form action="/users/store" method="POST">
        <x-form-input name="name" label="Name" required />
        <x-form-email name="email" label="Email" required />
        <x-form-submit>Save</x-form-submit>
    </x-form>
@endsection
```

## API Reference

### FormBuilder Class

#### Constructor
```php
public function __construct(SessionInterface $session)
```

#### Form Creation
- `create(string $action = '', string $method = 'POST'): self`
- `static make(SessionInterface $session, string $action = '', string $method = 'POST'): self`

#### Field Creation Methods
- `text(string $name, ?string $label = null): self`
- `email(string $name, ?string $label = null): self`
- `password(string $name, ?string $label = null): self`
- `textarea(string $name, ?string $label = null): self`
- `select(string $name, ?string $label = null): self`
- `checkbox(string $name, ?string $label = null): self`
- `radio(string $name, ?string $label = null): self`
- `file(string $name, ?string $label = null): self`
- `hidden(string $name): self`
- `submit(string $name = 'submit', ?string $label = 'Submit'): self`

#### Field Configuration Methods
- `required(bool $required = true): self`
- `value($value): self`
- `rule(string $rule): self`
- `attribute(string $key, $value): self`
- `placeholder(string $placeholder): self`
- `disabled(bool $disabled = true): self`
- `readonly(bool $readonly = true): self`
- `autofocus(bool $autofocus = true): self`
- `options(array $options): self`
- `multiple(bool $multiple = true): self`
- `accept(string $accept): self`
- `rows(int $rows): self`
- `cols(int $cols): self`
- `checkedValue($value): self`
- `uncheckedValue($value): self`

#### Form Configuration Methods
- `action(string $action): self`
- `method(string $method): self`
- `attributeForm(string $key, $value): self`
- `csrf(bool $enabled = true): self`

#### Finalization
- `build(): Form`

### Form Class

#### Validation
- `validate(array $data): bool`
- `isValid(): bool`
- `getErrors(): array`
- `getError(string $field): ?string`
- `hasErrors(): bool`
- `hasError(string $field): bool`
- `getValidatedData(): array`

#### Rendering
- `render(): string`
- `renderWithoutCsrf(): string`
- `getFields(): array`
- `getField(string $name): ?Field`

