<?php
// app/Controllers/UserController.php

namespace App\Controllers;

use Coyote\Http\Controllers\Controller;
use Coyote\Forms\FormBuilder;
use Coyote\Session\SessionInterface;
use Coyote\Support\Facades\Auth;
use Coyote\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Exibe formulário de registro de usuário
     */
    public function register(SessionInterface $session)
    {
        // Criar formulário de registro usando FormBuilder
        $builder = new FormBuilder($session);
        
        $form = $builder->create('/users/store', 'POST')
            ->text('name', 'Nome Completo')
                ->required()
                ->placeholder('Digite seu nome completo')
                ->autofocus()
            ->email('email', 'Endereço de Email')
                ->required()
                ->rule('email')
                ->placeholder('seu@email.com')
            ->password('password', 'Senha')
                ->required()
                ->rule('min:8')
                ->attribute('autocomplete', 'new-password')
            ->password('password_confirmation', 'Confirmar Senha')
                ->required()
                ->rule('confirmed')
            ->select('country', 'País')
                ->options([
                    '' => 'Selecione um país',
                    'br' => 'Brasil',
                    'us' => 'Estados Unidos',
                    'uk' => 'Reino Unido',
                    'es' => 'Espanha',
                    'pt' => 'Portugal',
                ])
                ->required()
            ->checkbox('terms', 'Aceito os termos e condições')
                ->required()
                ->checkedValue('accepted')
                ->uncheckedValue('declined')
            ->textarea('bio', 'Biografia (opcional)')
                ->rows(4)
                ->cols(50)
                ->placeholder('Conte um pouco sobre você...')
            ->submit('submit', 'Criar Conta')
            ->build();
        
        return $this->view('users.register', [
            'title' => 'Registrar Nova Conta',
            'form' => $form,
        ]);
    }
    
    /**
     * Processa registro de usuário
     */
    public function store(SessionInterface $session)
    {
        // Criar o mesmo formulário para validação
        $builder = new FormBuilder($session);
        
        $form = $builder->create('/users/store', 'POST')
            ->text('name')
                ->required()
                ->rule('min:3')
            ->email('email')
                ->required()
                ->rule('email')
            ->password('password')
                ->required()
                ->rule('min:8')
            ->password('password_confirmation')
                ->required()
                ->rule('confirmed')
            ->select('country')
                ->required()
            ->checkbox('terms')
                ->required()
            ->textarea('bio')
            ->submit('submit')
            ->build();
        
        // Validar dados do formulário
        $data = $this->request()->all();
        
        if ($form->validate($data)) {
            // Dados válidos - processar registro
            $validatedData = $form->getValidatedData();
            
            // Aqui você normalmente salvaria no banco de dados
            // User::create($validatedData);
            
            // Redirecionar com mensagem de sucesso
            $session->flash('success', 'Conta criada com sucesso!');
            return $this->redirect('/users/login');
        }
        
        // Se houver erros, redirecionar de volta com erros
        $session->flash('errors', $form->getErrors());
        $session->flash('old', $data);
        
        return $this->redirect('/users/register');
    }
    
    /**
     * Exibe formulário de login
     */
    public function login(SessionInterface $session)
    {
        $builder = new FormBuilder($session);
        
        $form = $builder->create('/users/authenticate', 'POST')
            ->email('email', 'Email')
                ->required()
                ->rule('email')
                ->placeholder('seu@email.com')
                ->autofocus()
            ->password('password', 'Senha')
                ->required()
                ->placeholder('Digite sua senha')
            ->checkbox('remember', 'Lembrar-me')
                ->checkedValue('yes')
                ->uncheckedValue('no')
            ->submit('submit', 'Entrar')
            ->build();
        
        return $this->view('users.login', [
            'title' => 'Login',
            'form' => $form,
        ]);
    }
    
    /**
     * Processa autenticação
     */
    public function authenticate(SessionInterface $session)
    {
        $builder = new FormBuilder($session);
        
        $form = $builder->create('/users/authenticate', 'POST')
            ->email('email')
                ->required()
                ->rule('email')
            ->password('password')
                ->required()
            ->checkbox('remember')
            ->submit('submit')
            ->build();
        
        $data = $this->request()->all();
        
        if ($form->validate($data)) {
            $credentials = $form->getValidatedData();
            
            // Tentar autenticar (exemplo simplificado)
            // if (Auth::attempt($credentials)) {
            //     $session->flash('success', 'Login realizado com sucesso!');
            //     return $this->redirect('/dashboard');
            // }
            
            // Para exemplo, vamos simular sucesso
            $session->flash('success', 'Login realizado com sucesso!');
            return $this->redirect('/dashboard');
        }
        
        $session->flash('errors', $form->getErrors());
        $session->flash('old', $data);
        
        return $this->redirect('/users/login');
    }
    
    /**
     * Exibe formulário de edição de perfil
     */
    public function edit(SessionInterface $session)
    {
        // Simular dados do usuário atual (normalmente do banco de dados)
        $currentUser = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'bio' => 'Desenvolvedor PHP com 5 anos de experiência.',
            'country' => 'br',
        ];
        
        $builder = new FormBuilder($session);
        
        $form = $builder->create('/users/update', 'POST')
            ->text('name', 'Nome')
                ->required()
                ->value($currentUser['name'])
            ->email('email', 'Email')
                ->required()
                ->rule('email')
                ->value($currentUser['email'])
                ->disabled() // Email não pode ser alterado
            ->select('country', 'País')
                ->options([
                    'br' => 'Brasil',
                    'us' => 'Estados Unidos',
                    'uk' => 'Reino Unido',
                    'es' => 'Espanha',
                    'pt' => 'Portugal',
                ])
                ->required()
                ->value($currentUser['country'])
            ->textarea('bio', 'Biografia')
                ->rows(4)
                ->cols(50)
                ->value($currentUser['bio'])
            ->file('avatar', 'Foto de Perfil')
                ->accept('image/*')
                ->attribute('title', 'Selecione uma imagem para seu perfil')
            ->submit('submit', 'Salvar Alterações')
            ->build();
        
        return $this->view('users.edit', [
            'title' => 'Editar Perfil',
            'form' => $form,
            'user' => $currentUser,
        ]);
    }
    
    /**
     * Processa atualização de perfil
     */
    public function update(SessionInterface $session)
    {
        $builder = new FormBuilder($session);
        
        $form = $builder->create('/users/update', 'POST')
            ->text('name')
                ->required()
                ->rule('min:3')
            ->email('email')
                ->required()
                ->rule('email')
            ->select('country')
                ->required()
            ->textarea('bio')
            ->file('avatar')
                ->accept('image/*')
            ->submit('submit')
            ->build();
        
        $data = $this->request()->all();
        
        if ($form->validate($data)) {
            $validatedData = $form->getValidatedData();
            
            // Processar upload de arquivo se existir
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                $fileName = uniqid() . '_' . $file['name'];
                $uploadPath = storage_path('uploads/avatars/' . $fileName);
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $validatedData['avatar'] = $fileName;
                }
            }
            
            // Atualizar usuário no banco de dados
            // User::where('id', auth()->id())->update($validatedData);
            
            $session->flash('success', 'Perfil atualizado com sucesso!');
            return $this->redirect('/users/profile');
        }
        
        $session->flash('errors', $form->getErrors());
        $session->flash('old', $data);
        
        return $this->redirect('/users/edit');
    }
    
    /**
     * Exibe formulário de contato
     */
    public function contact(SessionInterface $session)
    {
        $builder = new FormBuilder($session);
        
        $form = $builder->create('/contact/send', 'POST')
            ->text('name', 'Seu Nome')
                ->required()
                ->placeholder('Digite seu nome')
            ->email('email', 'Seu Email')
                ->required()
                ->rule('email')
                ->placeholder('seu@email.com')
            ->select('subject', 'Assunto')
                ->options([
                    '' => 'Selecione um assunto',
                    'support' => 'Suporte Técnico',
                    'sales' => 'Vendas',
                    'partnership' => 'Parceria',
                    'other' => 'Outro',
                ])
                ->required()
            ->textarea('message', 'Mensagem')
                ->required()
                ->rows(6)
                ->cols(60)
                ->placeholder('Digite sua mensagem aqui...')
            ->submit('submit', 'Enviar Mensagem')
            ->build();
        
        return $this->view('contact.form', [
            'title' => 'Fale Conosco',
            'form' => $form,
        ]);
    }
    
    /**
     * Processa envio de contato
     */
    public function sendContact(SessionInterface $session)
    {
        $builder = new FormBuilder($session);
        
        $form = $builder->create('/contact/send', 'POST')
            ->text('name')
                ->required()
                ->rule('min:2')
            ->email('email')
                ->required()
                ->rule('email')
            ->select('subject')
                ->required()
            ->textarea('message')
                ->required()
                ->rule('min:10')
            ->submit('submit')
            ->build();
        
        $data = $this->request()->all();
        
        if ($form->validate($data)) {
            $validatedData = $form->getValidatedData();
            
            // Processar envio de email (exemplo simplificado)
            // Mail::send('emails.contact', $validatedData, function($message) use ($validatedData) {
            //     $message->to('contato@exemplo.com')
            //             ->subject('Novo contato: ' . $validatedData['subject']);
            // });
            
            $session->flash('success', 'Mensagem enviada com sucesso! Entraremos em contato em breve.');
            return $this->redirect('/contact');
        }
        
        $session->flash('errors', $form->getErrors());
        $session->flash('old', $data);
        
        return $this->redirect('/contact');
    }
    
    /**
     * Exemplo de formulário com validação customizada
     */
    public function customValidationExample(SessionInterface $session)
    {
        // Registrar regra customizada (normalmente em Service Provider)
        Validator::extend('even_number', function($attribute, $value, $parameters) {
            return is_numeric($value) && $value % 2 === 0;
        });
        
        Validator::extend('strong_password', function($attribute, $value, $parameters) {
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $value);
        });
        
        $builder = new FormBuilder($session);
        
        $form = $builder->create('/custom/validate', 'POST')
            ->text('even_number', 'Número Par')
                ->required()
                ->rule('even_number')
                ->placeholder('Digite um número par')
            ->password('password', 'Senha Forte')
                ->required()
                ->rule('strong_password')
                ->placeholder('Mínimo 8 caracteres com maiúscula, minúscula, número e símbolo')
            ->submit('submit', 'Validar')
            ->build();
        
        return $this->view('custom.validation', [
            'title' => 'Validação Customizada',
            'form' => $form,
        ]);
    }
}