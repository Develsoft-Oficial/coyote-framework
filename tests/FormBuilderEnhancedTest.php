<?php
// tests/FormBuilderEnhancedTest.php

namespace Tests;

use Coyote\Forms\FormBuilder;
use Coyote\Forms\FormViewHelper;
use Coyote\Forms\CustomValidationHelper;
use Coyote\Session\SessionManager;
use Coyote\Session\FileSessionHandler;
use Coyote\Config\Repository as ConfigRepository;

/**
 * Enhanced Form Builder Tests
 * 
 * Tests for all new features implemented:
 * 1. FormViewHelper integration
 * 2. Enhanced file upload validation
 * 3. Custom validation callbacks
 * 4. Template system integration
 */
class FormBuilderEnhancedTest
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
    
    public function testFormViewHelper()
    {
        echo "Test 1: FormViewHelper Integration\n";
        echo "----------------------------------------\n";
        
        $builder = new FormBuilder($this->session);
        $form = $builder->create('/test', 'POST')
            ->text('name', 'Name')
                ->required()
                ->placeholder('Enter your name')
            ->email('email', 'Email')
                ->required()
                ->rule('email')
            ->submit('submit', 'Submit')
            ->build();
        
        // Create FormViewHelper
        $viewHelper = new FormViewHelper($form, [
            'use_default_classes' => true,
            'wrap_fields' => true,
            'show_labels' => true,
            'show_errors' => true,
        ]);
        
        // Test rendering form
        $formHtml = $viewHelper->renderForm(['class' => 'test-form']);
        assert(strpos($formHtml, '<form') !== false, 'FormViewHelper should render form tag');
        assert(strpos($formHtml, 'class="test-form') !== false, 'FormViewHelper should add custom classes');
        assert(strpos($formHtml, 'name="name"') !== false, 'FormViewHelper should render name field');
        assert(strpos($formHtml, 'type="email"') !== false, 'FormViewHelper should render email field');
        assert(strpos($formHtml, 'type="submit"') !== false, 'FormViewHelper should render submit button');
        
        echo "✓ FormViewHelper renders form correctly\n";
        
        // Test rendering individual field
        $fieldHtml = $viewHelper->renderField('name');
        assert(strpos($fieldHtml, 'name="name"') !== false, 'FormViewHelper should render individual field');
        assert(strpos($fieldHtml, 'form-group') !== false, 'FormViewHelper should add wrapper classes');
        
        echo "✓ FormViewHelper renders individual fields correctly\n";
        
        // Test without default classes
        $viewHelperNoClasses = new FormViewHelper($form, ['use_default_classes' => false]);
        $formHtmlNoClasses = $viewHelperNoClasses->renderForm();
        assert(strpos($formHtmlNoClasses, 'form-control') === false, 'FormViewHelper should not add default classes when disabled');
        
        echo "✓ FormViewHelper respects use_default_classes option\n";
        
        return true;
    }
    
    public function testEnhancedFileUpload()
    {
        echo "\nTest 2: Enhanced File Upload Validation\n";
        echo "----------------------------------------\n";
        
        $builder = new FormBuilder($this->session);
        $form = $builder->create('/upload', 'POST')
            ->file('avatar', 'Profile Picture')
                ->required()
                ->imageOnly()
                ->maxSizeMb(2) // 2MB
                ->multiple(true, 3) // Allow up to 3 files
            ->file('document', 'Document')
                ->pdfOnly()
                ->maxSize(1024 * 1024) // 1MB in bytes
            ->submit('upload', 'Upload Files')
            ->build();
        
        // Get file field
        $avatarField = $form->getField('avatar');
        $documentField = $form->getField('document');
        
        // Test field configuration
        assert($avatarField->isMultiple() === true, 'File field should support multiple uploads');
        assert($avatarField->getMaxFiles() === 3, 'File field should have correct max files limit');
        assert($avatarField->getMaxSize() === 2 * 1024 * 1024, 'File field should have correct max size (2MB)');
        
        // Check allowed MIME types for image field
        $avatarMimeTypes = $avatarField->getAllowedMimeTypes();
        assert(in_array('image/jpeg', $avatarMimeTypes), 'Image field should allow JPEG');
        assert(in_array('image/png', $avatarMimeTypes), 'Image field should allow PNG');
        
        // Check PDF field configuration
        assert($documentField->getMaxSize() === 1024 * 1024, 'Document field should have correct max size (1MB)');
        $documentMimeTypes = $documentField->getAllowedMimeTypes();
        assert(in_array('application/pdf', $documentMimeTypes), 'Document field should allow PDF');
        
        echo "✓ Enhanced file upload configuration works correctly\n";
        
        // Test validation with simulated file data
        $validImageData = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => sys_get_temp_dir() . '/test.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024 * 1024, // 1MB
        ];
        
        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');
        $validImageData['tmp_name'] = $tempFile;
        
        // Test single file validation
        $isValid = $avatarField->validate($validImageData);
        assert($isValid === true, 'Valid image file should pass validation');
        
        // Test file too large
        $largeFileData = $validImageData;
        $largeFileData['size'] = 3 * 1024 * 1024; // 3MB > 2MB limit
        $isValid = $avatarField->validate($largeFileData);
        assert($isValid === false, 'Oversized file should fail validation');
        
        // Test wrong file type
        $wrongTypeData = $validImageData;
        $wrongTypeData['type'] = 'application/pdf';
        $wrongTypeData['name'] = 'test.pdf';
        $isValid = $avatarField->validate($wrongTypeData);
        assert($isValid === false, 'Wrong file type should fail validation');
        
        // Clean up
        unlink($tempFile);
        
        echo "✓ File validation works correctly\n";
        
        return true;
    }
    
    public function testCustomValidationCallbacks()
    {
        echo "\nTest 3: Custom Validation Callbacks\n";
        echo "----------------------------------------\n";
        
        // Register custom validation rules
        CustomValidationHelper::registerCommonRules();
        
        $builder = new FormBuilder($this->session);
        $form = $builder->create('/validate', 'POST')
            ->text('even_number', 'Even Number')
                ->required()
                ->rule('even')
            ->text('cpf_field', 'CPF')
                ->required()
                ->rule('cpf')
            ->password('password', 'Strong Password')
                ->required()
                ->rule('strong_password')
            ->text('age', 'Age')
                ->required()
                ->rule('min_age:18')
            ->submit('validate', 'Validate')
            ->build();
        
        echo "✓ Custom validation rules registered and applied to form\n";
        
        // Test even number validation
        $evenField = $form->getField('even_number');
        
        // Valid even number
        $isValid = $evenField->validate(4);
        assert($isValid === true, 'Even number should pass validation');
        
        // Invalid odd number
        $isValid = $evenField->validate(3);
        assert($isValid === false, 'Odd number should fail validation');
        
        echo "✓ Even number validation works correctly\n";
        
        // Test strong password validation
        $passwordField = $form->getField('password');
        
        // Valid strong password
        $isValid = $passwordField->validate('StrongPass123!');
        assert($isValid === true, 'Strong password should pass validation');
        
        // Invalid weak password
        $isValid = $passwordField->validate('weak');
        assert($isValid === false, 'Weak password should fail validation');
        
        echo "✓ Strong password validation works correctly\n";
        
        // Test custom rule registration
        CustomValidationHelper::registerRule('contains_coyote', function($attribute, $value) {
            return strpos($value, 'coyote') !== false;
        }, 'O valor deve conter "coyote".');
        
        assert(CustomValidationHelper::hasRule('contains_coyote'), 'Custom rule should be registered');
        
        echo "✓ Custom rule registration works correctly\n";
        
        return true;
    }
    
    public function testTemplateIntegration()
    {
        echo "\nTest 4: Template System Integration\n";
        echo "----------------------------------------\n";
        
        $builder = new FormBuilder($this->session);
        $form = $builder->create('/template', 'POST')
            ->text('username', 'Username')
                ->required()
                ->placeholder('Enter username')
                ->attribute('data-test', 'username-field')
            ->email('email', 'Email Address')
                ->required()
                ->rule('email')
            ->select('country', 'Country')
                ->options([
                    '' => 'Select a country',
                    'us' => 'United States',
                    'br' => 'Brazil',
                    'uk' => 'United Kingdom',
                ])
                ->required()
            ->textarea('message', 'Message')
                ->rows(4)
                ->cols(50)
            ->checkbox('agree', 'I agree to terms')
                ->required()
            ->file('attachment', 'Attachment')
                ->accept('.pdf,.doc,.docx')
            ->submit('submit', 'Submit Form')
            ->build();
        
        // Test FormViewHelper with various options
        $viewHelper = new FormViewHelper($form);
        
        // Test form attributes
        $formHtml = $viewHelper->renderForm(['id' => 'test-form', 'class' => 'custom-form']);
        assert(strpos($formHtml, 'id="test-form"') !== false, 'Form should have custom ID');
        assert(strpos($formHtml, 'class="custom-form') !== false, 'Form should have custom class');
        assert(strpos($formHtml, 'enctype="multipart/form-data"') !== false, 'Form should have enctype for file upload');
        
        echo "✓ Form attributes rendered correctly\n";
        
        // Test field rendering with custom options
        $fieldOptions = [
            'wrap_fields' => false,
            'show_labels' => false,
            'show_errors' => false,
        ];
        
        $fieldHtml = $viewHelper->renderField('username', $fieldOptions);
        assert(strpos($fieldHtml, '<label') === false, 'Field should not render label when show_labels is false');
        assert(strpos($fieldHtml, '<div class="form-group') === false, 'Field should not wrap when wrap_fields is false');
        
        echo "✓ Field rendering options work correctly\n";
        
        // Test CSRF field rendering
        $csrfField = $viewHelper->renderCsrfField();
        assert(strpos($csrfField, 'name="_token"') !== false, 'CSRF field should have correct name');
        assert(strpos($csrfField, 'type="hidden"') !== false, 'CSRF field should be hidden');
        
        echo "✓ CSRF field rendered correctly\n";
        
        return true;
    }
    
    public function runAllTests()
    {
        echo "========================================\n";
        echo "Form Builder Enhanced Tests\n";
        echo "========================================\n\n";
        
        $tests = [
            'testFormViewHelper',
            'testEnhancedFileUpload',
            'testCustomValidationCallbacks',
            'testTemplateIntegration',
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            try {
                echo "\n";
                if ($this->$test()) {
                    $passed++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                echo "✗ Test {$test} failed with exception: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
        
        echo "\n========================================\n";
        echo "Test Results: {$passed} passed, {$failed} failed\n";
        echo "========================================\n";
        
        return $failed === 0;
    }
}

// Run tests if executed directly
if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    require_once __DIR__ . '/../vendors/autoload.php';
    
    $test = new FormBuilderEnhancedTest();
    $success = $test->runAllTests();
    
    exit($success ? 0 : 1);
}