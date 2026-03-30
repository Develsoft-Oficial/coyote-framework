<?php

require_once __DIR__ . '/vendor/autoload.php';

use Coyote\Validation\Validator;
use Coyote\Validation\Exceptions\ValidationException;

echo "=== Teste do Sistema de Validação ===\n\n";

// Test 1: Basic validation with required rule
echo "Teste 1: Validação básica com regra 'required'\n";
$data = ['name' => '', 'email' => 'test@example.com'];
$rules = ['name' => 'required', 'email' => 'required|email'];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    echo "  ❌ Falhou (esperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (inesperado)\n";
}

// Test 2: Valid data
echo "\nTeste 2: Dados válidos\n";
$data = ['name' => 'John Doe', 'email' => 'john@example.com', 'age' => 25];
$rules = [
    'name' => 'required|string|min:3|max:50',
    'email' => 'required|email',
    'age' => 'required|numeric|min:18|max:100'
];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    echo "  ❌ Falhou (inesperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (esperado)\n";
    echo "  Dados validados: " . json_encode($validator->validated()) . "\n";
}

// Test 3: Email validation
echo "\nTeste 3: Validação de email\n";
$data = ['email' => 'invalid-email'];
$rules = ['email' => 'required|email'];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    echo "  ❌ Falhou (esperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (inesperado)\n";
}

// Test 4: Min/Max validation
echo "\nTeste 4: Validação de tamanho mínimo/máximo\n";
$data = ['password' => '123', 'description' => 'Too long description that exceeds limit'];
$rules = [
    'password' => 'required|min:6',
    'description' => 'required|max:20'
];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    echo "  ❌ Falhou (esperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (inesperado)\n";
}

// Test 5: Confirmed rule
echo "\nTeste 5: Regra 'confirmed'\n";
$data = ['password' => 'secret123', 'password_confirmation' => 'secret123'];
$rules = ['password' => 'required|confirmed'];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    echo "  ❌ Falhou (inesperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (esperado)\n";
}

// Test 6: Same rule
echo "\nTeste 6: Regra 'same'\n";
$data = ['password' => 'secret123', 'confirm_password' => 'secret123'];
$rules = ['password' => 'required|same:confirm_password'];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    echo "  ❌ Falhou (inesperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (esperado)\n";
}

// Test 7: In/NotIn rules
echo "\nTeste 7: Regras 'in' e 'not_in'\n";
$data = ['status' => 'active', 'role' => 'admin'];
$rules = [
    'status' => 'required|in:active,inactive,pending',
    'role' => 'required|not_in:guest,anonymous'
];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    echo "  ❌ Falhou (inesperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (esperado)\n";
}

// Test 8: ValidationException
echo "\nTeste 8: ValidationException\n";
try {
    $data = ['email' => 'invalid'];
    $rules = ['email' => 'required|email'];
    
    $validator = Validator::make($data, $rules);
    $validator->validated(); // This should throw exception
    
    echo "  ❌ Não lançou exceção (inesperado)\n";
} catch (ValidationException $e) {
    echo "  ✅ Lançou exceção (esperado)\n";
    echo "  Status: " . $e->status() . "\n";
    echo "  Erros: " . json_encode($e->all()) . "\n";
}

// Test 9: Custom messages
echo "\nTeste 9: Mensagens personalizadas\n";
$data = ['username' => ''];
$rules = ['username' => 'required'];
$messages = [
    'username.required' => 'O nome de usuário é obrigatório!',
    'username' => 'Campo :attribute é necessário'
];

$validator = Validator::make($data, $rules, $messages);

if ($validator->fails()) {
    echo "  ❌ Falhou (esperado)\n";
    echo "  Erros: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "  ✅ Passou (inesperado)\n";
}

// Test 10: MessageBag functionality
echo "\nTeste 10: Funcionalidades do MessageBag\n";
$data = ['field1' => '', 'field2' => 'invalid-email'];
$rules = [
    'field1' => 'required',
    'field2' => 'required|email'
];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    $errors = $validator->errors();
    echo "  ❌ Falhou (esperado)\n";
    echo "  Tem erros? " . ($errors->isNotEmpty() ? 'Sim' : 'Não') . "\n";
    echo "  Primeiro erro: " . $errors->first() . "\n";
    echo "  Erros no field1: " . json_encode($errors->get('field1')) . "\n";
    echo "  Field2 tem erro? " . ($errors->has('field2') ? 'Sim' : 'Não') . "\n";
    echo "  Total de erros: " . $errors->count() . "\n";
} else {
    echo "  ✅ Passou (inesperado)\n";
}

echo "\n=== Fim dos testes ===\n";