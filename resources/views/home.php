<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title) ?> - Coyote Framework</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: #2c3e50; color: white; padding: 2rem 0; text-align: center; margin-bottom: 2rem; }
        header h1 { font-size: 2.5rem; margin-bottom: 0.5rem; }
        header p { font-size: 1.2rem; opacity: 0.9; }
        .content { background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .message { background: #e8f4fd; border-left: 4px solid #3498db; padding: 1rem; margin-bottom: 2rem; border-radius: 4px; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 2rem; }
        .feature-card { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 1.5rem; transition: transform 0.2s; }
        .feature-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .feature-card h3 { color: #2c3e50; margin-bottom: 0.5rem; }
        .feature-card p { color: #666; }
        .footer { margin-top: 3rem; text-align: center; color: #777; font-size: 0.9rem; border-top: 1px solid #eee; padding-top: 1rem; }
        .badge { display: inline-block; background: #3498db; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; margin-left: 0.5rem; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>🐺 Coyote Framework</h1>
            <p>Um micro-framework PHP leve e poderoso</p>
        </div>
    </header>
    
    <div class="container">
        <div class="content">
            <div class="message">
                <h2><?= $this->e($title) ?></h2>
                <p><?= $this->e($message) ?></p>
            </div>
            
            <h2>Funcionalidades Implementadas <span class="badge"><?= count($features) ?></span></h2>
            
            <div class="features">
                <?php foreach ($features as $feature): ?>
                <div class="feature-card">
                    <h3>✓ <?= $this->e($feature) ?></h3>
                    <p>Implementado e funcionando no framework.</p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="footer">
                <p>Coyote Framework v<?= $app->version() ?> | Ambiente: <?= $app->environment() ?> | Debug: <?= $app->isDebug() ? 'Ativo' : 'Inativo' ?></p>
                <p>Request URI: <?= $request->getUri() ?> | Method: <?= $request->getMethod() ?></p>
            </div>
        </div>
    </div>
</body>
</html>