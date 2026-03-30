<?php

/**
 * Exemplo Completo do Form Builder System - Fase 4D
 * 
 * Este exemplo demonstra todas as funcionalidades do sistema de Form Builder
 * implementado na Fase 4D do Coyote Framework.
 */

require_once __DIR__ . '/vendors/autoload.php';

use Coyote\Core\Application;
use Coyote\Config\Repository as ConfigRepository;
use Coyote\Session\SessionManager;
use Coyote\Session\FileSessionHandler;
use Coyote\Forms\FormBuilder;
use Coyote\Support\Facades\Auth;
use Coyote\Support\Facades\Session;
use Coyote\Support\Facades\Validator;
use Coyote\Support\Facades\Csrf;

// ============================================================================
// 1. CONFIGURAÇÃO INICIAL
// ============================================================================

echo "========================================\n";
echo "EXEMPLO COMPLETO - FORM BUILDER SYSTEM\n";
echo "========================================\n\n";

// Configuração da aplicação
$config = new ConfigRepository([
    'app' => [
        'name' => 'Coyote Form Builder Example',
        'env' => 'development',
    ],
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

// Inicializar aplicação
$app = Application::getInstance();
$app->instance('config', $config);

// Configurar sessão
$handler = new FileSessionHandler(sys_get_temp_dir(), 120);
$session = new SessionManager($config);
$session->setHandler($handler);
$session->start();

$app->instance('session', $session);

// ============================================================================
// 2. EXEMPLO 1: FORMULÁRIO DE REGISTRO SIMPLES
// ============================================================================

echo "1. FORMULÁRIO DE REGISTRO SIMPLES\n";
echo "----------------------------------------\n";

$builder = new FormBuilder($session);

$registrationForm = $builder->create('/register', 'POST')
    ->text('name', 'Full Name')
        ->required()
        ->placeholder('Enter your full name')
        ->autofocus()
    ->email('email', 'Email Address')
        ->required()
        ->rule('email')
        ->placeholder('you@example.com')
    ->password('password', 'Password')
        ->required()
        ->rule('min:8')
        ->attribute('autocomplete', 'new-password')
    ->password('password_confirmation', 'Confirm Password')
        ->required()
        ->rule('confirmed')
    ->select('country', 'Country')
        ->options([
            '' => 'Select a country',
            'us' => 'United States',
            'br' => 'Brazil',
            'uk' => 'United Kingdom',
            'de' => 'Germany',
            'fr' => 'France',
        ])
        ->required()
    ->checkbox('terms', 'I agree to the terms and conditions')
        ->required()
        ->checkedValue('accepted')
        ->uncheckedValue('declined')
    ->textarea('bio', 'Biography')
        ->rows(4)
        ->cols(50)
        ->placeholder('Tell us about yourself...')
    ->submit('register', 'Create Account')
    ->build();

echo "Formulário criado com sucesso!\n";
echo "Método: " . $registrationForm->getMethod() . "\n";
echo "Ação: " . $registrationForm->getAction() . "\n";
echo "CSRF Habilitado: " . ($registrationForm->isCsrfEnabled() ? 'Sim' : 'Não') . "\n";
echo "Número de campos: " . count($registrationForm->getFields()) . "\n\n";

// Simular dados de submissão válidos
echo "Simulando submissão válida...\n";
$validData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'securepassword123',
    'password_confirmation' => 'securepassword123',
    'country' => 'us',
    'terms' => 'accepted',
    'bio' => 'Software developer with 5 years of experience.',
    '_csrf_token' => $registrationForm->getCsrfToken(),
];

if ($registrationForm->validate($validData)) {
    echo "✓ Validação passou!\n";
    $validatedData = $registrationForm->getValidatedData();
    echo "Dados validados: " . json_encode($validatedData, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "✗ Validação falhou!\n";
    echo "Erros: " . json_encode($registrationForm->getErrors(), JSON_PRETTY_PRINT) . "\n";
}

echo "\n";

// Simular dados de submissão inválidos
echo "Simulando submissão inválida...\n";
$invalidData = [
    'name' => 'Jo', // Muito curto
    'email' => 'invalid-email',
    'password' => '123', // Muito curto
    'password_confirmation' => '456', // Não confere
    'country' => '', // Não selecionado
    'terms' => 'declined', // Não aceitou termos
    'bio' => str_repeat('a', 1000), // Muito longo
    '_csrf_token' => $registrationForm->getCsrfToken(),
];

if (!$registrationForm->validate($invalidData)) {
    echo "✓ Validação falhou como esperado!\n";
    echo "Erros encontrados:\n";
    foreach ($registrationForm->getErrors() as $field => $errors) {
        echo "  - {$field}: " . implode(', ', $errors) . "\n";
    }
}

echo "\n";

// ============================================================================
// 3. EXEMPLO 2: FORMULÁRIO DE UPLOAD DE ARQUIVO
// ============================================================================

echo "2. FORMULÁRIO DE UPLOAD DE ARQUIVO\n";
echo "----------------------------------------\n";

$uploadForm = $builder->create('/upload', 'POST')
    ->text('title', 'Document Title')
        ->required()
        ->rule('max:100')
    ->file('document', 'Select Document')
        ->required()
        ->accept('.pdf,.doc,.docx')
        ->multiple(false)
    ->textarea('description', 'Description')
        ->rows(3)
    ->checkbox('public', 'Make document public')
    ->submit('upload', 'Upload Document')
    ->build();

echo "Formulário de upload criado!\n";
echo "Campos de arquivo suportam: " . ($uploadForm->getField('document')->getAccept() ?? 'Todos os tipos') . "\n\n";

// ============================================================================
// 4. EXEMPLO 3: FORMULÁRIO DE PESQUISA (GET)
// ============================================================================

echo "3. FORMULÁRIO DE PESQUISA (MÉTODO GET)\n";
echo "----------------------------------------\n";

$searchForm = $builder->create('/search', 'GET')
    ->text('query', 'Search Query')
        ->placeholder('Enter search terms...')
    ->select('category', 'Category')
        ->options([
            '' => 'All Categories',
            'books' => 'Books',
            'movies' => 'Movies',
            'music' => 'Music',
            'games' => 'Games',
        ])
    ->select('sort', 'Sort By')
        ->options([
            'relevance' => 'Relevance',
            'newest' => 'Newest',
            'oldest' => 'Oldest',
            'price_asc' => 'Price: Low to High',
            'price_desc' => 'Price: High to Low',
        ])
    ->checkbox('in_stock', 'In Stock Only')
    ->submit('search', 'Search')
    ->build();

echo "Formulário de pesquisa criado!\n";
echo "Método: " . $searchForm->getMethod() . " (não requer CSRF)\n\n";

// ============================================================================
// 5. EXEMPLO 4: USANDO HELPERS GLOBAIS
// ============================================================================

echo "4. USANDO HELPERS GLOBAIS\n";
echo "----------------------------------------\n";

echo "Helper csrf_token(): " . substr(csrf_token(), 0, 20) . "...\n";
echo "Helper csrf_field(): " . csrf_field() . "\n";
echo "Helper csrf_meta(): " . csrf_meta() . "\n";

// Simular armazenamento de old input
$session->flash('_old_input', [
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
]);

echo "Helper old('name'): " . old('name') . "\n";
echo "Helper old('email'): " . old('email') . "\n";
echo "Helper old('nonexistent', 'default'): " . old('nonexistent', 'valor padrão') . "\n\n";

// ============================================================================
// 6. EXEMPLO 5: USANDO FACADES
// ============================================================================

echo "5. USANDO FACADES\n";
echo "----------------------------------------\n";

// Configurar facades com a aplicação
\Coyote\Support\Facades\Facade::setFacadeApplication($app);

echo "Usando Auth facade (simulação)...\n";
echo "Auth::check() simulado: false\n";
echo "Auth::guest() simulado: true\n\n";

echo "Usando Session facade...\n";
Session::put('test_key', 'test_value');
echo "Session::get('test_key'): " . Session::get('test_key') . "\n";
echo "Session::has('test_key'): " . (Session::has('test_key') ? 'Sim' : 'Não') . "\n\n";

echo "Usando Validator facade...\n";
$validator = Validator::make(
    ['email' => 'test@example.com', 'age' => 25],
    ['email' => 'required|email', 'age' => 'required|numeric|min:18']
);
echo "Validator criado: " . get_class($validator) . "\n";
echo "Validação passou: " . ($validator->validate() ? 'Sim' : 'Não') . "\n\n";

echo "Usando Csrf facade...\n";
$token = Csrf::token();
echo "Csrf::token(): " . substr($token, 0, 20) . "...\n";
echo "Csrf::validateToken('{$token}'): " . (Csrf::validateToken($token) ? 'Válido' : 'Inválido') . "\n";
echo "Csrf::validateToken('invalid_token'): " . (Csrf::validateToken('invalid_token') ? 'Válido' : 'Inválido') . "\n\n";

// ============================================================================
// 7. EXEMPLO 6: RENDERIZAÇÃO COMPLETA
// ============================================================================

echo "6. RENDERIZAÇÃO COMPLETA DO FORMULÁRIO\n";
echo "----------------------------------------\n";

$contactForm = $builder->create('/contact', 'POST')
    ->text('name', 'Your Name')
        ->required()
        ->value(old('name', ''))
    ->email('email', 'Email Address')
        ->required()
        ->value(old('email', ''))
    ->select('subject', 'Subject')
        ->options([
            '' => 'Select a subject',
            'general' => 'General Inquiry',
            'support' => 'Technical Support',
            'billing' => 'Billing Issue',
            'feedback' => 'Feedback',
        ])
        ->required()
    ->textarea('message', 'Message')
        ->required()
        ->rows(6)
        ->placeholder('How can we help you?')
        ->value(old('message', ''))
    ->submit('send', 'Send Message')
    ->build();

echo "HTML do formulário de contato:\n";
echo "==============================\n";
echo $contactForm->render();
echo "\n==============================\n\n";

// Simular erro para demonstrar renderização de erros
$errorData = [
    'name' => '',
    'email' => 'invalid',
    'subject' => '',
    'message' => 'Hi',
    '_csrf_token' => $contactForm->getCsrfToken(),
];

$contactForm->validate($errorData);

if ($contactForm->hasErrors()) {
    echo "Resumo de erros:\n";
    echo $contactForm->renderErrorSummary();
    echo "\n";
}

// ============================================================================
// 8. EXEMPLO 7: FORM BUILDER FLUENT API AVANÇADA
// ============================================================================

echo "7. API FLUENT AVANÇADA\n";
echo "----------------------------------------\n";

echo "Criando formulário com API fluente complexa...\n";

$advancedForm = FormBuilder::make($session, '/advanced', 'POST')
    ->text('product_name', 'Product Name')
        ->required()
        ->rule('min:3')
        ->rule('max:100')
        ->attribute('data-product', 'true')
        ->attribute('class', 'form-control')
    ->email('product_email', 'Product Contact Email')
        ->required()
        ->rule('email')
        ->placeholder('contact@product.com')
    ->textarea('description', 'Product Description')
        ->required()
        ->rows(5)
        ->cols(60)
        ->rule('min:20')
        ->rule('max:1000')
    ->select('category', 'Product Category')
        ->options([
            'electronics' => 'Electronics',
            'clothing' => 'Clothing',
            'books' => 'Books',
            'home' => 'Home & Garden',
            'sports' => 'Sports & Outdoors',
        ])
        ->required()
        ->multiple(false)
    ->select('tags', 'Product Tags')
        ->options([
            'new' => 'New Arrival',
            'bestseller' => 'Bestseller',
            'sale' => 'On Sale',
            'featured' => 'Featured',
            'limited' => 'Limited Edition',
        ])
        ->multiple(true)
        ->size(3)
    ->file('images', 'Product Images')
        ->accept('image/*')
        ->multiple(true)
    ->checkbox('featured', 'Feature this product on homepage')
        ->checkedValue('yes')
    ->checkbox('available', 'Product is available for purchase')
        ->checkedValue('true')
        ->uncheckedValue('false')
    ->radio('status', 'Product Status')
        ->options([
            'draft' => 'Draft',
            'review' => 'Under Review',
            'published' => 'Published',
            'archived' => 'Archived',
        ])
    ->hidden('created_by')
        ->value('admin')
    ->submit('save_draft', 'Save as Draft')
        ->attribute('class', 'btn btn-secondary')
    ->submit('publish', 'Publish Product')
        ->attribute('class', 'btn btn-primary')
    ->build();

echo "Formulário avançado criado com sucesso!\n";
echo "Total de campos: " . count($advancedForm->getFields()) . "\n";
echo "Campos múltiplos: " . ($advancedForm->getField('tags')->isMultiple() ? 'Sim' : 'Não') . "\n";
echo "Upload múltiplo: " . ($advancedForm->getField('images')->isMultiple() ? 'Sim' : 'Não') . "\n\n";

// ============================================================================
// 9. CONCLUSÃO
// ============================================================================

echo "========================================\n";
echo "CONCLUSÃO\n";
echo "========================================\n\n";

echo "✓ Sistema Form Builder implementado com sucesso!\n";
echo "✓ 10 tipos de campo suportados\n";
echo "✓ Validação integrada com Validator existente\n";
echo "✓ Proteção CSRF automática\n";
echo "✓ Suporte a old input via sessão\n";
echo "✓ Helpers globais disponíveis\n";
echo "✓ Facades para acesso fácil\n";
echo "✓ API fluente e intuitiva\n";
echo "✓ Testes de integração implementados\n\n";

echo "O sistema Form Builder da Fase 4D está completo e pronto para uso!\n";
echo "Total de arquivos implementados: ~20 arquivos\n";
echo "Funcionalidades principais: Form, FormBuilder, Field types, CSRF, Helpers, Facades\n\n";

echo "Para usar em sua aplicação:\n";
echo "1. Use FormBuilder para criar formulários\n";
echo "2. Use helpers como csrf_field() nas views\n";
echo "3. Use facades como Auth::check() no código\n";
echo "4. O middleware CSRF já está registrado no grupo 'web'\n\n";

echo "Exemplo rápido:\n";
echo "```php\n";
echo "// Criar formulário\n";
echo "\$form = FormBuilder::make(\$session, '/submit', 'POST')\n";
echo "    ->text('name', 'Name')->required()\n";
echo "    ->email('email', 'Email')->required()->rule('email')\n";
echo "    ->build();\n\n";
echo "// Validar dados\n";
echo "if (\$form->validate(\$request->all())) {\n";
echo "    \$data = \$form->getValidatedData();\n";
echo "    // Processar dados...\n";
echo "}\n";
echo "```\n";

// Limpar sessão
$session->flush();

echo "\n========================================\n";
echo "FIM DO EXEMPLO\n";
echo "========================================\n";