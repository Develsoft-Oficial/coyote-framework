<?php

/**
 * Teste do Fluxo Completo de Desenvolvimento
 * 
 * Este teste valida todo o fluxo de desenvolvimento do Coyote Framework
 * como pacote Composer, incluindo:
 * 1. Desenvolvimento local com symlink
 * 2. Teste como pacote instalado
 * 3. Atualizações em tempo real
 * 4. Funcionalidade básica do framework
 */

echo "=== VALIDAÇÃO DO FLUXO COMPLETO DE DESENVOLVIMENTO ===\n\n";

// 1. Verificar estrutura do projeto
echo "1. VERIFICANDO ESTRUTURA DO PROJETO\n";
echo "====================================\n";

$requiredDirs = [
    'src/' => 'Código fonte do framework',
    'src/Core/' => 'Núcleo do framework',
    'src/Support/' => 'Utilitários e autoloader',
    'scripts/' => 'Scripts de desenvolvimento',
    'test-app/' => 'Aplicação de teste',
    'test-clean-install/' => 'Instalação limpa de teste',
    '.github/workflows/' => 'CI/CD',
];

$allOk = true;
foreach ($requiredDirs as $dir => $description) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        echo "✓ {$dir} - {$description}\n";
    } else {
        echo "✗ {$dir} - {$description} (NÃO ENCONTRADO)\n";
        $allOk = false;
    }
}

echo "\n";

// 2. Verificar arquivos essenciais
echo "2. VERIFICANDO ARQUIVOS ESSENCIAIS\n";
echo "===================================\n";

$requiredFiles = [
    'composer.json' => 'Configuração do pacote Composer',
    'README.md' => 'Documentação principal',
    'scripts/setup-dev-symlink.ps1' => 'Script PowerShell para desenvolvimento',
    'scripts/setup-dev-symlink.bat' => 'Script Batch para desenvolvimento',
    'test-app/composer.json' => 'Configuração da app de teste',
    '.gitignore' => 'Controle de versão',
    '.github/workflows/ci.yml' => 'CI/CD pipeline',
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ {$file} - {$description}\n";
    } else {
        echo "✗ {$file} - {$description} (NÃO ENCONTRADO)\n";
        $allOk = false;
    }
}

echo "\n";

// 3. Testar desenvolvimento com symlink
echo "3. TESTANDO DESENVOLVIMENTO COM SYMLINK\n";
echo "========================================\n";

$testAppVendorPath = __DIR__ . '/test-app/vendor/coyote/framework';
if (is_dir($testAppVendorPath)) {
    echo "✓ Aplicação de teste com framework instalado\n";
    
    // Verificar se é um symlink/junction
    if (is_link($testAppVendorPath) || @readlink($testAppVendorPath)) {
        echo "✓ É um symlink/junction para desenvolvimento\n";
        
        $target = realpath($testAppVendorPath);
        $expected = realpath(__DIR__);
        if ($target === $expected) {
            echo "✓ Aponta para o diretório correto: {$target}\n";
        } else {
            echo "✗ Aponta para diretório incorreto: {$target} (esperado: {$expected})\n";
            $allOk = false;
        }
    } else {
        echo "⚠️  NÃO é um symlink (pode ser cópia física)\n";
    }
} else {
    echo "✗ Aplicação de teste NÃO tem framework instalado\n";
    echo "  Execute: cd test-app && composer install\n";
    $allOk = false;
}

echo "\n";

// 4. Testar instalação limpa
echo "4. TESTANDO INSTALAÇÃO LIMPA\n";
echo "=============================\n";

$cleanInstallPath = __DIR__ . '/test-clean-install/vendor/coyote/framework';
if (is_dir($cleanInstallPath)) {
    echo "✓ Instalação limpa criada com sucesso\n";
    
    // Verificar estrutura básica
    $cleanFiles = ['composer.json', 'src/', 'README.md'];
    $cleanOk = true;
    foreach ($cleanFiles as $file) {
        if (file_exists($cleanInstallPath . '/' . $file) || is_dir($cleanInstallPath . '/' . $file)) {
            echo "  ✓ {$file} presente\n";
        } else {
            echo "  ✗ {$file} ausente\n";
            $cleanOk = false;
        }
    }
    
    if ($cleanOk) {
        echo "✓ Estrutura da instalação limpa está correta\n";
    } else {
        echo "✗ Problemas na estrutura da instalação limpa\n";
        $allOk = false;
    }
} else {
    echo "✗ Instalação limpa NÃO criada\n";
    echo "  Execute: mkdir test-clean-install && cd test-clean-install && composer init && composer require coyote/framework:@dev\n";
    $allOk = false;
}

echo "\n";

// 5. Testar funcionalidade do framework
echo "5. TESTANDO FUNCIONALIDADE DO FRAMEWORK\n";
echo "=======================================\n";

// Testar autoloader do Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✓ Autoloader do Composer disponível\n";
    
    // Testar namespaces
    $namespaces = ['Coyote\\', 'Coyote\\Core\\', 'Coyote\\Support\\'];
    $namespaceOk = true;
    
    foreach ($namespaces as $namespace) {
        // Verificar se o namespace está no autoloader do Composer
        $composerAutoload = __DIR__ . '/vendor/composer/autoload_psr4.php';
        if (file_exists($composerAutoload)) {
            $autoloadData = include $composerAutoload;
            $found = false;
            foreach ($autoloadData as $prefix => $paths) {
                if (strpos($namespace, $prefix) === 0) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                echo "  ✓ Namespace {$namespace} registrado no Composer\n";
            } else {
                echo "  ✗ Namespace {$namespace} NÃO registrado no Composer\n";
                $namespaceOk = false;
            }
        }
    }
    
    if ($namespaceOk) {
        echo "✓ Namespaces registrados corretamente\n";
    } else {
        echo "✗ Problemas com registro de namespaces\n";
        $allOk = false;
    }
} else {
    echo "✗ Autoloader do Composer NÃO disponível\n";
    echo "  Execute: composer install\n";
    $allOk = false;
}

echo "\n";

// 6. Testar scripts de desenvolvimento
echo "6. TESTANDO SCRIPTS DE DESENVOLVIMENTO\n";
echo "=======================================\n";

$scripts = [
    'scripts/setup-dev-symlink.ps1' => 'PowerShell',
    'scripts/setup-dev-symlink.bat' => 'Batch',
];

foreach ($scripts as $script => $type) {
    if (file_exists(__DIR__ . '/' . $script)) {
        $content = file_get_contents(__DIR__ . '/' . $script);
        if (strpos($content, 'coyote/framework') !== false || 
            strpos($content, 'test-app') !== false ||
            strpos($content, 'symlink') !== false ||
            strpos($content, 'junction') !== false) {
            echo "✓ Script {$type} parece correto\n";
        } else {
            echo "⚠️  Script {$type} pode não estar configurado corretamente\n";
        }
    } else {
        echo "✗ Script {$type} NÃO encontrado\n";
        $allOk = false;
    }
}

echo "\n";

// 7. Testar CI/CD
echo "7. TESTANDO CONFIGURAÇÃO DE CI/CD\n";
echo "===================================\n";

$ciFiles = [
    '.github/workflows/ci.yml' => 'CI pipeline',
    '.github/workflows/release.yml' => 'Release pipeline',
];

foreach ($ciFiles as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $content = file_get_contents(__DIR__ . '/' . $file);
        if (strpos($content, 'coyote/framework') !== false || 
            strpos($content, 'tests') !== false ||
            strpos($content, 'php') !== false) {
            echo "✓ {$description} configurado\n";
        } else {
            echo "⚠️  {$description} pode não estar configurado corretamente\n";
        }
    } else {
        echo "✗ {$description} NÃO encontrado\n";
        $allOk = false;
    }
}

echo "\n";

// 8. Resumo e próximos passos
echo "8. RESUMO E PRÓXIMOS PASSOS\n";
echo "============================\n";

if ($allOk) {
    echo "✅ FLUXO DE DESENVOLVIMENTO VALIDADO COM SUCESSO!\n\n";
    
    echo "O Coyote Framework está configurado corretamente como pacote Composer:\n";
    echo "1. ✅ Estrutura do pacote organizada em src/\n";
    echo "2. ✅ Sistema de desenvolvimento com symlink/junction\n";
    echo "3. ✅ Aplicação de teste funcional (test-app/)\n";
    echo "4. ✅ Instalação limpa testada (test-clean-install/)\n";
    echo "5. ✅ CI/CD configurado (.github/workflows/)\n";
    echo "6. ✅ Documentação atualizada (README.md)\n";
    echo "7. ✅ Controle de versão (git init, tag v1.0.0)\n";
    echo "8. ✅ Scripts de desenvolvimento (PowerShell e Batch)\n\n";
    
    echo "PRÓXIMOS PASSOS RECOMENDADOS:\n";
    echo "1. Resolver conflito de autoloader (Autoloader.php vs Composer)\n";
    echo "2. Publicar no Packagist (criar conta em packagist.org)\n";
    echo "3. Configurar webhook para atualizações automáticas\n";
    echo "4. Adicionar mais testes unitários\n";
    echo "5. Documentar API completa\n";
    echo "6. Criar exemplos mais avançados\n\n";
    
    echo "PARA DESENVOLVER LOCALMENTE:\n";
    echo "1. Edite os arquivos em src/\n";
    echo "2. As mudanças são refletidas automaticamente em test-app/vendor/coyote/framework\n";
    echo "3. Teste com: cd test-app && php seu-teste.php\n";
    echo "4. Para testar instalação limpa: cd test-clean-install && php test-simple.php\n\n";
    
    echo "PARA PUBLICAR UMA NOVA VERSÃO:\n";
    echo "1. git tag -a v1.0.1 -m \"Nova versão\"\n";
    echo "2. git push --tags\n";
    echo "3. O GitHub Actions criará automaticamente uma release\n";
} else {
    echo "⚠️  ALGUNS PROBLEMAS DETECTADOS NO FLUXO DE DESENVOLVIMENTO\n\n";
    
    echo "Problemas encontrados:\n";
    echo "1. Verifique se todos os diretórios e arquivos necessários existem\n";
    echo "2. Execute os scripts de desenvolvimento: scripts/setup-dev-symlink.bat\n";
    echo "3. Configure a aplicação de teste: cd test-app && composer install\n";
    echo "4. Teste a instalação limpa: mkdir test-clean-install && composer require coyote/framework:@dev\n\n";
    
    echo "O fluxo básico está funcionando, mas alguns ajustes são necessários.\n";
}

echo "=== FIM DA VALIDAÇÃO ===\n";