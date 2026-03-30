<?php

// Simple test for validation system without autoload
echo "=== Teste Simples do Sistema de Validação ===\n\n";

// Manually include required files
require_once __DIR__ . '/vendors/coyote/Validation/MessageBag.php';
require_once __DIR__ . '/vendors/coyote/Validation/Validator.php';
require_once __DIR__ . '/vendors/coyote/Validation/Exceptions/ValidationException.php';

// Include rule interface
require_once __DIR__ . '/vendors/coyote/Validation/Rules/Rule.php';

// Test 1: Basic MessageBag functionality
echo "Teste 1: MessageBag básico\n";
$messageBag = new Coyote\Validation\MessageBag();
$messageBag->add('name', 'Name is required');
$messageBag->add('email', 'Email is invalid');
$messageBag->add('email', 'Email must be valid format');

echo "  Tem erros? " . ($messageBag->isNotEmpty() ? 'Sim' : 'Não') . "\n";
echo "  Primeiro erro: " . $messageBag->first() . "\n";
echo "  Erros no email: " . json_encode($messageBag->get('email')) . "\n";
echo "  Total de erros: " . $messageBag->count() . "\n";

// Test 2: Validator with required rule
echo "\nTeste 2: Validator com regra 'required'\n";
$data = ['name' => '', 'email' => 'test@example.com'];
$rules = ['name' => 'required', 'email' => 'required'];

$validator = new Coyote\Validation\Validator($data, $rules);

if ($validator->fails()) {
    echo "  ❌ Falhou (esperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (inesperado)\n";
}

// Test 3: Valid data
echo "\nTeste 3: Dados válidos\n";
$data = ['name' => 'John Doe', 'email' => 'john@example.com'];
$rules = ['name' => 'required', 'email' => 'required'];

$validator = new Coyote\Validation\Validator($data, $rules);

if ($validator->fails()) {
    echo "  ❌ Falhou (inesperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (esperado)\n";
}

// Test 4: Custom messages
echo "\nTeste 4: Mensagens personalizadas\n";
$data = ['username' => ''];
$rules = ['username' => 'required'];
$messages = ['username.required' => 'O nome de usuário é obrigatório!'];

$validator = new Coyote\Validation\Validator($data, $rules, $messages);

if ($validator->fails()) {
    echo "  ❌ Falhou (esperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (inesperado)\n";
}

// Test 5: ValidationException
echo "\nTeste 5: ValidationException\n";
try {
    $data = ['email' => ''];
    $rules = ['email' => 'required'];
    
    $validator = new Coyote\Validation\Validator($data, $rules);
    
    if ($validator->fails()) {
        throw new Coyote\Validation\Exceptions\ValidationException($validator->errors(), 'Validation failed');
    }
    
    echo "  ❌ Não lançou exceção (inesperado)\n";
} catch (Coyote\Validation\Exceptions\ValidationException $e) {
    echo "  ✅ Lançou exceção (esperado)\n";
    echo "  Mensagem: " . $e->getMessage() . "\n";
    echo "  Erros: " . json_encode($e->all()) . "\n";
}

echo "\n=== Fim dos testes ===\n";