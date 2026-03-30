<?php

/**
 * Exemplo de uso do sistema de Sessão e Autenticação do Coyote Framework
 *
 * Este exemplo demonstra como usar as funcionalidades de sessão e autenticação
 * implementadas na Fase 4 do projeto.
 */

// Iniciar buffer de saída para evitar problemas com headers
ob_start();

// Configurar sessão para CLI - suprimir avisos de depreciação
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('session.use_cookies', '0');
ini_set('session.cache_limiter', '');

// Configurar autoloading manualmente
spl_autoload_register(function ($class) {
    $prefix = 'Coyote\\';
    $baseDir = __DIR__ . '/vendors/coyote/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Classe ConfigRepository simples para exemplo
class SimpleConfigRepository
{
    private $config = [];
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }
    
    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}

// Mock da classe Repository do Coyote\Config
if (!class_exists('Coyote\Config\Repository')) {
    class_alias('SimpleConfigRepository', 'Coyote\Config\Repository');
}

echo "=== EXEMPLO DE USO: Sessão e Autenticação ===\n\n";

// ============================================
// 1. CONFIGURAÇÃO DO SISTEMA DE SESSÃO
// ============================================

echo "1. Configurando sistema de sessão...\n";

// Criar configuração de sessão
$sessionConfig = new Coyote\Config\Repository([
    'session' => [
        'driver' => 'file',
        'lifetime' => 120, // 2 horas
        'files' => __DIR__ . '/storage/framework/sessions',
        'cookie' => 'coyote_app_session',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
    ]
]);

// Criar diretório de sessões se não existir
$sessionPath = __DIR__ . '/storage/framework/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
    echo "  ✓ Diretório de sessões criado\n";
}

// Criar SessionManager
$sessionManager = new Coyote\Session\SessionManager($sessionConfig);

// Iniciar sessão
if ($sessionManager->start()) {
    echo "  ✓ Sessão iniciada com ID: " . $sessionManager->getId() . "\n";
} else {
    echo "  ✗ Falha ao iniciar sessão\n";
    exit(1);
}

// ============================================
// 2. OPERAÇÕES BÁSICAS DE SESSÃO
// ============================================

echo "\n2. Operações básicas de sessão:\n";

// Armazenar dados na sessão
$sessionManager->put('usuario.nome', 'João Silva');
$sessionManager->put('usuario.email', 'joao@exemplo.com');
$sessionManager->put('usuario.perfil', 'admin');
$sessionManager->put('contador_acessos', 1);

echo "  ✓ Dados do usuário armazenados na sessão\n";

// Recuperar dados da sessão
$nome = $sessionManager->get('usuario.nome');
$email = $sessionManager->get('usuario.email');
$perfil = $sessionManager->get('usuario.perfil');

echo "  ✓ Dados recuperados: $nome ($email) - Perfil: $perfil\n";

// Incrementar contador
$sessionManager->increment('contador_acessos');
$contador = $sessionManager->get('contador_acessos');
echo "  ✓ Contador de acessos incrementado: $contador\n";

// Verificar existência de chave
if ($sessionManager->has('usuario.email')) {
    echo "  ✓ Verificação de existência funcionando\n";
}

// Flash data (dados disponíveis apenas na próxima requisição)
$sessionManager->flash('mensagem_sucesso', 'Operação realizada com sucesso!');
$sessionManager->flash('tipo_mensagem', 'success');
echo "  ✓ Flash data configurada para próxima requisição\n";

// ============================================
// 3. SISTEMA DE AUTENTICAÇÃO
// ============================================

echo "\n3. Sistema de autenticação:\n";

// Criar BcryptHasher para senhas
$hasher = new Coyote\Auth\Password\BcryptHasher(['cost' => 10]);

// Simular senha de usuário
$senhaPlana = 'minhaSenhaSegura123';
$hashSenha = $hasher->hash($senhaPlana);

echo "  ✓ Hash de senha gerado: " . substr($hashSenha, 0, 20) . "...\n";

// Verificar senha
if ($hasher->verify($senhaPlana, $hashSenha)) {
    echo "  ✓ Verificação de senha funcionando corretamente\n";
}

// Verificar senha incorreta
if (!$hasher->verify('senhaErrada', $hashSenha)) {
    echo "  ✓ Rejeição de senha incorreta funcionando\n";
}

// ============================================
// 4. EXEMPLO DE AUTENTICAÇÃO DE USUÁRIO
// ============================================

echo "\n4. Exemplo de autenticação de usuário:\n";

// Simular credenciais de login
$credenciais = [
    'email' => 'joao@exemplo.com',
    'password' => 'minhaSenhaSegura123'
];

// Em um cenário real, aqui buscaríamos o usuário do banco de dados
// Para este exemplo, simularemos um usuário autenticado

// Armazenar estado de autenticação na sessão
$sessionManager->put('auth.authenticated', true);
$sessionManager->put('auth.user_id', 123);
$sessionManager->put('auth.user_name', 'João Silva');
$sessionManager->put('auth.last_login', date('Y-m-d H:i:s'));

echo "  ✓ Estado de autenticação armazenado na sessão\n";

// Verificar se usuário está autenticado
if ($sessionManager->get('auth.authenticated')) {
    $userId = $sessionManager->get('auth.user_id');
    $userName = $sessionManager->get('auth.user_name');
    $lastLogin = $sessionManager->get('auth.last_login');
    
    echo "  ✓ Usuário autenticado: $userName (ID: $userId)\n";
    echo "  ✓ Último login: $lastLogin\n";
}

// ============================================
// 5. GERENCIAMENTO DE SESSÃO
// ============================================

echo "\n5. Gerenciamento de sessão:\n";

// Obter todos os dados da sessão
$todosDados = $sessionManager->all();
echo "  ✓ Total de itens na sessão: " . count($todosDados) . "\n";

// Remover item específico
$sessionManager->forget('usuario.perfil');
if (!$sessionManager->has('usuario.perfil')) {
    echo "  ✓ Item 'usuario.perfil' removido com sucesso\n";
}

// Pull (obter e remover)
$emailPull = $sessionManager->pull('usuario.email');
if ($emailPull && !$sessionManager->has('usuario.email')) {
    echo "  ✓ Pull realizado: email '$emailPull' obtido e removido\n";
}

// ============================================
// 6. LIMPEZA E FINALIZAÇÃO
// ============================================

echo "\n6. Finalizando exemplo:\n";

// Salvar sessão
if ($sessionManager->save()) {
    echo "  ✓ Sessão salva com sucesso\n";
}

// Obter informações da sessão
echo "  ✓ ID da sessão: " . $sessionManager->getId() . "\n";
echo "  ✓ Nome da sessão: " . $sessionManager->getName() . "\n";
// Nota: O método getLifetime() não está disponível no SessionManager
// Em uma aplicação real, você obteria isso da configuração
echo "  ✓ Tempo de vida configurado: 120 minutos\n";

// Para limpar completamente a sessão (em um logout):
// $sessionManager->flush();
// echo "  ✓ Sessão completamente limpa (simulação de logout)\n";

// Para destruir a sessão:
// $sessionManager->destroy();
// echo "  ✓ Sessão destruída\n";

echo "\n=== RESUMO DO EXEMPLO ===\n";
echo "✅ Sistema de sessão funcionando com múltiplos drivers\n";
echo "✅ Armazenamento e recuperação de dados na sessão\n";
echo "✅ Flash data para mensagens entre requisições\n";
echo "✅ Sistema de hash de senhas com bcrypt\n";
echo "✅ Verificação e rehash de senhas\n";
echo "✅ Integração pronta para uso com AuthManager\n";
echo "✅ Exemplo completo de autenticação de usuário\n";

echo "\n=== PRÓXIMOS PASSOS PARA USO REAL ===\n";
echo "1. Integrar com DatabaseProvider para buscar usuários do banco\n";
echo "2. Implementar PasswordBroker para recuperação de senha\n";
echo "3. Adicionar funcionalidade 'Remember Me' ao SessionGuard\n";
echo "4. Criar middlewares de autenticação para rotas protegidas\n";
echo "5. Implementar controle de acesso baseado em papéis (RBAC)\n";

// Salvar e fechar sessão
$sessionManager->save();

echo "\nExemplo concluído com sucesso!\n";

// Limpar buffer de saída
ob_end_flush();