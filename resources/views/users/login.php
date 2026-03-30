<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login') ?> - Coyote Framework</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            max-width: 400px;
            width: 100%;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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
            border-color: #4f46e5;
            background: white;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .form-control::placeholder {
            color: #a0aec0;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
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
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        
        .btn-secondary {
            background: #6b7280;
            margin-top: 12px;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
            box-shadow: 0 10px 20px rgba(107, 114, 128, 0.3);
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
        
        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            font-size: 14px;
        }
        
        .links a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
        }
        
        .links a:hover {
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
            
            .links {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Bem-vindo de Volta</h1>
                <p>Faça login para acessar sua conta</p>
            </div>
            
            <div class="card-body">
                <?php if (session()->has('success')): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars(session()->get('success')) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-error">
                        <strong>Erro!</strong> Email ou senha inválidos.
                    </div>
                <?php endif; ?>
                
                <!-- Renderizar formulário completo automaticamente -->
                <?= $form->render() ?>
                
                <div class="links">
                    <a href="/users/register">Criar nova conta</a>
                    <a href="/password/reset">Esqueci minha senha</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Foco automático no campo de email
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.querySelector('input[type="email"]');
            if (emailInput) {
                emailInput.focus();
            }
            
            // Adicionar validação básica
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(event) {
                    const email = document.querySelector('input[type="email"]');
                    const password = document.querySelector('input[type="password"]');
                    
                    if (email && !email.value) {
                        event.preventDefault();
                        alert('Por favor, digite seu email.');
                        email.focus();
                        return;
                    }
                    
                    if (password && !password.value) {
                        event.preventDefault();
                        alert('Por favor, digite sua senha.');
                        password.focus();
                        return;
                    }
                });
            }
        });
    </script>
</body>
</html>