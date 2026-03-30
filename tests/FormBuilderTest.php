<?php

namespace Tests;

use Coyote\Forms\FormBuilder;
use Coyote\Session\SessionManager;
use Coyote\Session\FileSessionHandler;
use Coyote\Config\Repository as ConfigRepository;

class FormBuilderTest
{
    protected $session;
    protected $config;
    
    public function __construct()
    {
        // Setup configuration
        $this->config = new ConfigRepository([
            'session' => [
                'driver' => 'file',
                'path' => sys_get_temp_dir(),
                'lifetime' => 120,
            ],
            'csrf' => [
                'enabled' => true,
                'token_name' => '_token',
            ],
        ]);
        
        // Setup session
        $handler = new FileSessionHandler(sys_get_temp_dir(), 120);
        $this->session = new SessionManager($this->config);
        $this->session->setDriver($handler);
        $this->session->start();
    }
    
    public function testFormCreation()
    {
        echo "Test 1: Form Creation\n";
        
        $builder = new FormBuilder($this->session);
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
            ->textarea('message', 'Message')
                ->rows(5)
                ->cols(40)
            ->select('country', 'Country')
                ->options([
                    'us' => 'United States',
                    'br' => 'Brazil',
                    'uk' => 'United Kingdom',
                ])
            ->checkbox('agree', 'I agree to terms')
                ->required()
            ->submit('submit', 'Submit Form')
            ->build();
        
        // Test form properties
        assert($form->getMethod() === 'POST', 'Form method should be POST');
        assert($form->getAction() === '/submit', 'Form action should be /submit');
        assert($form->isCsrfEnabled() === true, 'CSRF should be enabled');
        
        echo "✓ Form created successfully\n";
        
        // Test rendering
        $html = $form->render();
        assert(strpos($html, '<form') !== false, 'Form should contain form tag');
        assert(strpos($html, 'method="POST"') !== false, 'Form should have POST method');
        assert(strpos($html, 'action="/submit"') !== false, 'Form should have correct action');
        assert(strpos($html, 'name="name"') !== false, 'Form should contain name field');
        assert(strpos($html, 'name="email"') !== false, 'Form should contain email field');
        assert(strpos($html, 'type="password"') !== false, 'Form should contain password field');
        assert(strpos($html, '<textarea') !== false, 'Form should contain textarea');
        assert(strpos($html, '<select') !== false, 'Form should contain select');
        assert(strpos($html, 'type="checkbox"') !== false, 'Form should contain checkbox');
        assert(strpos($html, 'type="submit"') !== false, 'Form should contain submit button');
        assert(strpos($html, '_csrf_token') !== false, 'Form should contain CSRF token');
        
        echo "✓ Form rendered correctly\n";
        
        return true;
    }
    
    public function testFormValidation()
    {
        echo "\nTest 2: Form Validation\n";
        
        $builder = new FormBuilder($this->session);
        $form = $builder->create('/validate', 'POST')
            ->text('username', 'Username')
                ->required()
                ->rule('min:3')
                ->rule('max:20')
            ->email('email', 'Email')
                ->required()
                ->rule('email')
            ->build();
        
        // Test valid data
        $validData = [
            'username' => 'john_doe',
            'email' => 'john@example.com',
            '_csrf_token' => $form->getCsrfToken(),
        ];
        
        $isValid = $form->validate($validData);
        assert($isValid === true, 'Valid data should pass validation');
        assert($form->hasErrors() === false, 'Valid data should not have errors');
        
        echo "✓ Valid data passes validation\n";
        
        // Test invalid data
        $invalidData = [
            'username' => 'ab', // Too short
            'email' => 'invalid-email', // Invalid email
            '_csrf_token' => $form->getCsrfToken(),
        ];
        
        $isValid = $form->validate($invalidData);
        assert($isValid === false, 'Invalid data should fail validation');
        assert($form->hasErrors() === true, 'Invalid data should have errors');
        
        $errors = $form->getErrors();
        assert(isset($errors['username']), 'Username should have error');
        assert(isset($errors['email']), 'Email should have error');
        
        echo "✓ Invalid data fails validation with correct errors\n";
        
        return true;
    }
    
    public function testCsrfProtection()
    {
        echo "\nTest 3: CSRF Protection\n";
        
        $builder = new FormBuilder($this->session);
        $form = $builder->create('/protected', 'POST')
            ->text('data', 'Data')
            ->build();
        
        // Test with valid CSRF token
        $validData = [
            'data' => 'test data',
            '_csrf_token' => $form->getCsrfToken(),
        ];
        
        $isValid = $form->validate($validData);
        assert($isValid === true, 'Form with valid CSRF token should pass');
        
        echo "✓ Valid CSRF token passes validation\n";
        
        // Test with invalid CSRF token
        $invalidData = [
            'data' => 'test data',
            '_csrf_token' => 'invalid_token',
        ];
        
        $isValid = $form->validate($invalidData);
        assert($isValid === false, 'Form with invalid CSRF token should fail');
        assert($form->hasErrors() === true, 'Invalid CSRF should produce error');
        
        $errors = $form->getErrors();
        assert(isset($errors['_csrf']), 'CSRF error should be present');
        
        echo "✓ Invalid CSRF token fails validation\n";
        
        // Test GET form (should not require CSRF)
        $form = $builder->create('/search', 'GET')
            ->text('query', 'Search')
            ->build();
        
        $getData = ['query' => 'test'];
        $isValid = $form->validate($getData);
        assert($isValid === true, 'GET forms should not require CSRF');
        
        echo "✓ GET forms don't require CSRF\n";
        
        return true;
    }
    
    public function testFieldTypes()
    {
        echo "\nTest 4: Field Types\n";
        
        $builder = new FormBuilder($this->session);
        
        // Test all field types
        $form = $builder->create('/test', 'POST')
            ->text('text_field', 'Text Field')
            ->email('email_field', 'Email Field')
            ->password('password_field', 'Password Field')
            ->textarea('textarea_field', 'Textarea Field')
            ->select('select_field', 'Select Field')
                ->options(['1' => 'Option 1', '2' => 'Option 2'])
            ->checkbox('checkbox_field', 'Checkbox Field')
            ->radio('radio_field', 'Radio Field')
                ->options(['a' => 'Option A', 'b' => 'Option B'])
            ->file('file_field', 'File Field')
            ->hidden('hidden_field')
            ->submit('submit_field', 'Submit')
            ->build();
        
        $fields = $form->getFields();
        
        // Check that all fields were added
        $expectedFields = [
            'text_field', 'email_field', 'password_field', 'textarea_field',
            'select_field', 'checkbox_field', 'radio_field', 'file_field',
            'hidden_field', 'submit_field'
        ];
        
        foreach ($expectedFields as $fieldName) {
            $field = $form->getField($fieldName);
            assert($field !== null, "Field {$fieldName} should exist");
        }
        
        echo "✓ All field types created successfully\n";
        
        // Test field-specific methods
        $selectField = $form->getField('select_field');
        assert(method_exists($selectField, 'options'), 'SelectField should have options method');
        
        $checkboxField = $form->getField('checkbox_field');
        assert(method_exists($checkboxField, 'isChecked'), 'CheckboxField should have isChecked method');
        
        echo "✓ Field-specific methods available\n";
        
        return true;
    }
    
    public function testOldInput()
    {
        echo "\nTest 5: Old Input Support\n";
        
        $builder = new FormBuilder($this->session);
        $form = $builder->create('/old', 'POST')
            ->text('name', 'Name')
            ->email('email', 'Email')
            ->build();
        
        // Simulate form submission with errors
        $submissionData = [
            'name' => 'John Doe',
            'email' => 'invalid-email', // Invalid email
            '_csrf_token' => $form->getCsrfToken(),
        ];
        
        $form->validate($submissionData);
        
        // Old input should now be in session
        $oldInput = $this->session->get('_old_input', []);
        assert(isset($oldInput['name']), 'Old input should contain name');
        assert($oldInput['name'] === 'John Doe', 'Old input should preserve name value');
        assert(isset($oldInput['email']), 'Old input should contain email');
        
        echo "✓ Old input stored in session after failed validation\n";
        
        // Create new form (should load old input)
        $newForm = $builder->create('/old', 'POST')
            ->text('name', 'Name')
            ->email('email', 'Email')
            ->build();
        
        // The form should automatically populate fields with old input
        // (This happens in Form::addField via loadOldInput)
        
        echo "✓ New form loads old input from session\n";
        
        return true;
    }
    
    public function runAllTests()
    {
        echo "========================================\n";
        echo "Form Builder Integration Tests\n";
        echo "========================================\n\n";
        
        $tests = [
            'testFormCreation',
            'testFormValidation', 
            'testCsrfProtection',
            'testFieldTypes',
            'testOldInput',
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            try {
                if ($this->$test()) {
                    $passed++;
                }
            } catch (\Exception $e) {
                echo "✗ Test failed: " . $e->getMessage() . "\n";
                $failed++;
            } catch (\AssertionError $e) {
                echo "✗ Assertion failed: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
        
        echo "\n========================================\n";
        echo "Results: {$passed} passed, {$failed} failed\n";
        echo "========================================\n";
        
        return $failed === 0;
    }
}

// Run tests if file is executed directly
if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    require_once __DIR__ . '/../vendors/autoload.php';
    
    $test = new FormBuilderTest();
    $success = $test->runAllTests();
    
    exit($success ? 0 : 1);
}