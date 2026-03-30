# Form Builder

O Form Builder do Coyote Framework fornece uma API fluente e orientada a objetos para criar formulários HTML com validação integrada, proteção CSRF e manipulação de sessão.

## 📋 Visão Geral

```php
use Coyote\Forms\FormBuilder;
use Coyote\Session\SessionManager;

// Inicializar sessão
$session = new SessionManager($config);
$session->start();

// Criar form builder
$builder = new FormBuilder($session);

// Construir formulário
$form = $builder->create('/submit', 'POST')
    ->text('name', 'Nome Completo')
        ->required()
        ->placeholder('Digite seu nome')
    ->email('email', 'E-mail')
        ->required()
        ->rule('email')
    ->password('password', 'Senha')
        ->required()
        ->rule('min:8')
    ->submit('submit', 'Enviar')
    ->build();

// Renderizar
echo $form->render();
```

## 🏗️ Criação de Formulários

### Métodos de Criação

```php
// Método 1: Construtor
$builder = new FormBuilder($session);
$form = $builder->create('/action', 'POST')->build();

// Método 2: Método estático
$form = FormBuilder::make($session, '/action', 'POST')->build();

// Método 3: Com configurações
$form = $builder->create('/submit', 'POST')
    ->csrf() // Proteção CSRF (padrão)
    ->attributeForm('class', 'form-horizontal')
    ->attributeForm('id', 'user-form')
    ->build();
```

### Configuração do Formulário

```php
$form = $builder->create('/submit', 'POST')
    ->action('/novo-endpoint') // Alterar action
    ->method('PUT') // Alterar método
    ->csrf(false) // Desabilitar CSRF
    ->attributeForm('enctype', 'multipart/form-data') // Para uploads
    ->attributeForm('class', 'form-vertical')
    ->attributeForm('data-validate', 'true')
    ->build();
```

## 🎛️ Tipos de Campos

### Campos Disponíveis

| Método | Classe | HTML Output | Descrição |
|--------|--------|-------------|-----------|
| `text($name, $label)` | `TextField` | `<input type="text">` | Campo de texto |
| `email($name, $label)` | `EmailField` | `<input type="email">` | E-mail com validação |
| `password($name, $label)` | `PasswordField` | `<input type="password">` | Senha |
| `textarea($name, $label)` | `TextareaField` | `<textarea></textarea>` | Área de texto |
| `select($name, $label)` | `SelectField` | `<select></select>` | Lista suspensa |
| `checkbox($name, $label)` | `CheckboxField` | `<input type="checkbox">` | Checkbox |
| `radio($name, $label)` | `RadioField` | `<input type="radio">` | Radio button |
| `file($name, $label)` | `FileField` | `<input type="file">` | Upload de arquivo |
| `hidden($name)` | `HiddenField` | `<input type="hidden">` | Campo oculto |
| `submit($name, $label)` | `SubmitField` | `<input type="submit">` | Botão submit |
| `button($name, $label)` | `ButtonField` | `<button type="button">` | Botão genérico |
| `date($name, $label)` | `DateField` | `<input type="date">` | Data |
| `number($name, $label)` | `NumberField` | `<input type="number">` | Número |
| `range($name, $label)` | `RangeField` | `<input type="range">` | Range slider |

### Exemplos de Campos

```php
$form = $builder->create('/submit', 'POST')
    // Texto básico
    ->text('username', 'Nome de Usuário')
    
    // E-mail com validação
    ->email('email', 'Endereço de E-mail')
        ->rule('email')
    
    // Senha com confirmação
    ->password('password', 'Senha')
    ->password('password_confirmation', 'Confirmar Senha')
        ->rule('confirmed')
    
    // Textarea com dimensões
    ->textarea('bio', 'Biografia')
        ->rows(5)
        ->cols(40)
        ->placeholder('Conte-nos sobre você...')
    
    // Select com opções
    ->select('country', 'País')
        ->options([
            'br' => 'Brasil',
            'us' => 'Estados Unidos',
            'uk' => 'Reino Unido',
        ])
        ->placeholder('Selecione um país')
    
    // Checkbox com valores personalizados
    ->checkbox('terms', 'Aceitar Termos')
        ->checkedValue('accepted')
        ->uncheckedValue('declined')
    
    // Radio buttons
    ->radio('gender', 'Gênero')
        ->options([
            'male' => 'Masculino',
            'female' => 'Feminino',
            'other' => 'Outro',
        ])
    
    // Upload de arquivo
    ->file('avatar', 'Foto de Perfil')
        ->accept('image/*')
        ->multiple() // Múltiplos arquivos
    
    // Campo oculto
    ->hidden('token', csrf_token())
    
    // Botão submit
    ->submit('submit', 'Salvar')
    
    ->build();
```

## ⚙️ Configuração de Campos

### Métodos Comuns a Todos os Campos

```php
// Configurações básicas
->required()                    // Campo obrigatório
->required($message)            // Com mensagem personalizada
->value($value)                 // Valor inicial
->placeholder($text)            // Placeholder
->disabled()                    // Desabilitar campo
->readonly()                    // Somente leitura
->autofocus()                   // Foco automático
->attribute($key, $value)       // Atributo HTML personalizado
->class($className)             // Classe CSS
->id($id)                       // ID do campo
->style($css)                   // Estilo inline

// Validação
->rule($rule)                   // Regra de validação
->rules([$rule1, $rule2])      // Múltiplas regras
->ruleIf($rule, $condition)    // Regra condicional

// Dados
->old()                         // Usar valor antigo da sessão
->default($value)               // Valor padrão
->fromModel($model, $attribute) // Valor do model
```

### Configurações Específicas

#### Select e Radio Fields
```php
->options([
    'value1' => 'Label 1',
    'value2' => 'Label 2',
])

->options(Product::pluck('name', 'id'))

->optionGroups([
    'Frutas' => [
        'apple' => 'Maçã',
        'banana' => 'Banana',
    ],
    'Verduras' => [
        'lettuce' => 'Alface',
        'spinach' => 'Espinafre',
    ],
])
```

#### Textarea Fields
```php
->rows(10)      // Número de linhas
->cols(50)      // Número de colunas
->wrap('hard')  // Wrap do texto
```

#### Checkbox Fields
```php
->checkedValue('yes')       // Valor quando marcado
->uncheckedValue('no')      // Valor quando desmarcado
->checked()                 // Marcar por padrão
->inline()                  // Exibir inline
```

#### File Fields
```php
->accept('image/*')         // Tipos de arquivo aceitos
->accept('.pdf,.doc,.docx') // Extensões específicas
->multiple()                // Múltiplos arquivos
->maxSize(2048)             // Tamanho máximo em KB
```

## 🎨 Renderização

### Métodos de Renderização

```php
// Renderizar formulário completo
echo $form->render();

// Renderizar apenas campos (sem form tag)
echo $form->renderFields();

// Renderizar campo específico
echo $form->field('email')->render();

// Renderizar com template personalizado
echo $form->renderWith('custom-form-template.php');

// Renderizar em partes
echo $form->open();     // <form ...>
echo $form->fields();   // Campos
echo $form->close();    // </form>
```

### Templates Personalizados

```php
// Usar template diferente
$form->setTemplate('forms/custom.php');

// Template básico
/*
<form {{ $attributes }}>
    @csrf
    @foreach($fields as $field)
        <div class="form-group">
            @if($field->hasLabel())
                <label for="{{ $field->id }}">
                    {{ $field->label }}
                </label>
            @endif
            {{ $field->render() }}
            @if($field->hasError())
                <div class="error">
                    {{ $field->error() }}
                </div>
            @endif
        </div>
    @endforeach
</form>
*/
```

### Layouts de Formulário

```php
// Formulário horizontal (Bootstrap)
$form->setLayout('horizontal');

// Formulário inline
$form->setLayout('inline');

// Formulário vertical (padrão)
$form->setLayout('vertical');

// Layout personalizado
$form->setLayout(function ($field) {
    return "
        <div class='my-custom-row'>
            <label>{$field->label}</label>
            <div class='input-wrapper'>
                {$field->render()}
                {$field->error()}
            </div>
        </div>
    ";
});
```

## ✅ Validação

### Validação Integrada

```php
// No controller
public function store(Request $request)
{
    // Validar automaticamente
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);
    
    // Se falhar, redireciona com erros
    // Os erros ficam disponíveis na sessão
}

// No formulário, os erros são exibidos automaticamente
```

### Regras de Validação

```php
// Regras básicas
->rule('required')
->rule('email')
->rule('numeric')
->rule('string')
->rule('boolean')

// Regras com parâmetros
->rule('min:8')
->rule('max:255')
->rule('between:3,50')
->rule('in:admin,user,guest')
->rule('regex:/^[a-z]+$/')

// Regras de arquivo
->rule('file')
->rule('image')
->rule('mimes:jpg,png,pdf')
->rule('max:2048') // 2MB

// Regras de data
->rule('date')
->rule('date_format:Y-m-d')
->rule('after:today')
->rule('before:2024-12-31')

// Regras personalizadas
->rule(function ($attribute, $value, $fail) {
    if ($value === 'foo') {
        $fail('O campo ' . $attribute . ' não pode ser foo.');
    }
})
```

### Mensagens de Erro Personalizadas

```php
// Mensagem específica para regra
->rule('required', 'Este campo é obrigatório.')
->rule('email', 'Por favor, insira um e-mail válido.')

// Mensagens para múltiplas regras
->messages([
    'required' => 'O campo :attribute é obrigatório.',
    'email' => 'O :attribute deve ser um e-mail válido.',
    'min' => 'O :attribute deve ter pelo menos :min caracteres.',
])

// Mensagens específicas por campo
->messagesFor('password', [
    'required' => 'A senha é obrigatória.',
    'min' => 'A senha deve ter pelo menos 8 caracteres.',
])
```

## 🔄 Old Input (Valores Antigos)

### Manter Valores Após Submit

```php
// Automaticamente mantém valores após validação falhar
$form = $builder->create('/submit', 'POST')
    ->text('name', 'Nome')
        ->old() // Usa valor da sessão se existir
    ->build();

// Ou especificar manualmente
->value(old('name', $default))

// Em controllers, após validação falhar:
return redirect()->back()->withInput();
```

## 🎯 Exemplos Completos

### Exemplo 1: Formulário de Registro

```php
<?php
// app/Controllers/AuthController.php

public function showRegistrationForm()
{
    $builder = new FormBuilder(session());
    
    $form = $builder->create('/register', 'POST')
        ->text('name', 'Nome Completo')
            ->required()
            ->placeholder('Seu nome completo')
            ->autofocus()
        
        ->email('email', 'Endereço de E-mail')
            ->required()
            ->rule('email')
            ->rule('unique:users,email')
            ->placeholder('seu@email.com')
        
        ->password('password', 'Senha')
            ->required()
            ->rule('min:8')
            ->rule('regex:/[A-Z]/', 'Deve conter pelo menos uma letra maiúscula')
            ->rule('regex:/[0-9]/', 'Deve conter pelo menos um número')
        
        ->password('password_confirmation', 'Confirmar Senha')
            ->required()
            ->rule('confirmed')
        
        ->select('country', 'País')
            ->options(Country::pluck('name', 'id'))
            ->required()
            ->placeholder('Selecione seu país')
        
        ->checkbox('terms', 'Aceito os termos de uso')
            ->required('Você deve aceitar os termos')
            ->checkedValue('accepted')
        
        ->submit('register', 'Criar Conta')
            ->class('btn btn-primary btn-lg')
        
        ->build();
    
    return view('auth.register', compact('form'));
}

public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'country' => 'required|exists:countries,id',
        'terms' => 'required|accepted',
    ]);
    
    $user = User::create($validated);
    
    auth()->login($user);
    
    return redirect('/dashboard')
        ->with('success', 'Conta criada com sucesso!');
}
```

### Exemplo 2: Formulário de Edição com Model

```php
<?php
// app/Controllers/UserController.php

public function edit(User $user)
{
    $builder = new FormBuilder(session());
    
    $form = $builder->create("/users/{$user->id}", 'PUT')
        ->text('name', 'Nome')
            ->required()
            ->fromModel($user, 'name')
        
        ->email('email', 'E-mail')
            ->required()
            ->rule('email')
            ->rule("unique:users,email,{$user->id}")
            ->fromModel($user, 'email')
        
        ->textarea('bio', 'Biografia')
            ->rows(4)
            ->fromModel($user, 'bio')
            ->placeholder('Conte um pouco sobre você...')
        
        ->file('avatar', 'Foto de Perfil')
            ->accept('image/*')
            ->help('Tamanho máximo: 2MB. Formatos: JPG, PNG.')
        
        ->submit('save', 'Salvar Alterações')
            ->class('btn btn-success')
        
        ->build();
    
    return view('users.edit', compact('form', 'user'));
}

public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => "required|email|unique:users,email,{$user->id}",
        'bio' => 'nullable|string|max:1000',
        'avatar' => 'nullable|image|max:2048',
    ]);
    
    if ($request->hasFile('avatar')) {
        $path = $request->file('avatar')->store('avatars');
        $validated['avatar'] = $path;
    }
    
    $user->update($validated);
    
    return redirect("/users/{$user->id}")
        ->with('success', 'Perfil atualizado com sucesso!');
}
```

### Exemplo 3: Formulário Dinâmico com JavaScript

```php
<?php
// Formulário com campos dinâmicos

$form = $builder->create('/products', 'POST')
    ->text('name', 'Nome do Produto')
        ->required()
        ->attribute('data-validate', 'true')
    
    ->number('price', 'Preço')
        ->required()
        ->min(0)
        ->step(0.01)
        ->attribute('data-price', 'true')
    
    ->select('category_id', 'Categoria')
        ->options(Category::pluck('name', 'id'))
        ->required()
        ->attribute('data-category', 'true')
        ->attribute('onchange', 'loadSubcategories(this.value)')
    
    // Campo dinâmico (via JavaScript)
    ->select('subcategory_id', 'Subcategoria')
        ->options([]) // Vazio inicialmente
        ->attribute('id', 'subcategory-select')
        ->attribute('