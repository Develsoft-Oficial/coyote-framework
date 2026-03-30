<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Registrar') ?> - Coyote Framework</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            max-width: 500px;
            width: 100%;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .card-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .card-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .card-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-control::placeholder {
            color: #a0aec0;
        }
        
        .form-control:disabled {
            background: #edf2f7;
            cursor: not-allowed;
        }
        
        .form-text {
            display: block;
            margin-top: 6px;
            font-size: 13px;
            color: #718096;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .form-check-input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }
        
        .form-check-label {
            font-size: 14px;
            color: #4a5568;
        }
        
        .btn {
            display: inline-block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }
        
        .error-message {
            color: #e53e3e;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }
        
        .login-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #718096;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 640px) {
            .card-body {
                padding: 30px 20px;
            }
            
            .card-header {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Criar Nova Conta</h1>
                <p>Preencha os dados abaixo para se registrar</p>
            </div>
            
            <div class="card-body">
                <?php if (session()->has('success')): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars(session()->get('success')) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-error">
                        <strong>Erro!</strong> Por favor, corrija os erros abaixo.
                    </div>
                <?php endif; ?>
                
                <!-- Método 1: Renderizar formulário completo automaticamente -->
                <?= $form->render() ?>
                
                <!-- 
                Método 2: Renderizar manualmente (comente a linha acima e descomente abaixo)
                
                <form action="/users/store" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="form-group">
                        <label for="name">Nome Completo *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-control" 
                               value="<?= old('name') ?>"
                               placeholder="Digite seu nome completo"
                               required>
                        <?php if ($form->hasError('name')): ?>
                            <span class="error-message"><?= $form->getError('name') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Endereço de Email *</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               value="<?= old('email') ?>"
                               placeholder="seu@email.com"
                               required>
                        <?php if ($form->hasError('email')): ?>
                            <span class="error-message"><?= $form->getError('email') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Senha *</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Mínimo 8 caracteres"
                               required>
                        <?php if ($form->hasError('password')): ?>
                            <span class="error-message"><?= $form->getError('password') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Senha *</label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="form-control" 
                               placeholder="Digite a senha novamente"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="country">País *</label>
                        <select id="country" name="country" class="form-control" required>
                            <option value="">Selecione um país</option>
                            <option value="br" <?= old('country') == 'br' ? 'selected' : '' ?>>Brasil</option>
                            <option value="us" <?= old('country') == 'us' ? 'selected' : '' ?>>Estados Unidos</option>
                            <option value="uk" <?= old('country') == 'uk' ? 'selected' : '' ?>>Reino Unido</option>
                            <option value="es" <?= old('country') == 'es' ? 'selected' : '' ?>>Espanha</option>
                            <option value="pt" <?= old('country') == 'pt' ? 'selected' : '' ?>>Portugal</option>
                        </select>
                        <?php if ($form->hasError('country')): ?>
                            <span class="error-message"><?= $form->getError('country') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" 
                               id="terms" 
                               name="terms" 
                               value="accepted"
                               class="form-check-input"
                               <?= old('terms') == 'accepted' ? 'checked' : '' ?>
                               required>
                        <label for="terms" class="form-check-label">
                            Aceito os termos e condições
                        </label>
                        <?php if ($form->hasError('terms')): ?>
                            <span class="error-message"><?= $form->getError('terms') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">Biografia (opcional)</label>
                        <textarea id="bio" 
                                  name="bio" 
                                  class="form-control" 
                                  rows="4"
                                  placeholder="Conte um pouco sobre você..."><?= old('bio') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Criar Conta</button>
                </form>
                -->
                
                <div class="login-link">
                    Já tem uma conta? <a href="/users/login">Faça login aqui</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Validação client-side básica
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            if (form) {
                form.addEventListener('submit', function(event) {
                    const password = document.getElementById('password');
                    const confirmPassword = document.getElementById('password_confirmation');
                    
                    if (password && confirmPassword && password.value !== confirmPassword.value) {
                        event.preventDefault();
                        alert('As senhas não coincidem!');
                        confirmPassword.focus();
                    }
                });
            }
            
            // Adicionar classes de erro dinamicamente
            const errorElements = document.querySelectorAll('.error-message');
            errorElements.forEach(function(error) {
                const input = error.previousElementSibling;
                if (input && input.classList.contains('form-control')) {
                    input.classList.add('has-error');
                }
            });
        });
    </script>
</body>
</html>