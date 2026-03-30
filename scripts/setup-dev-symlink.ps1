#!/usr/bin/env pwsh
<#
.SYNOPSIS
Configura desenvolvimento symlink para o Coyote Framework

.DESCRIPTION
Este script configura um symlink do pacote local para desenvolvimento,
permitindo testar o framework como se fosse um pacote baixado do Composer.

.PARAMETER ExampleAppPath
Caminho para a aplicação de exemplo que usará o framework

.EXAMPLE
.\setup-dev-symlink.ps1 -ExampleAppPath "..\coyote-example-app"

.EXAMPLE
.\setup-dev-symlink.ps1
#>

param(
    [string]$ExampleAppPath = "..\coyote-example-app"
)

Write-Host "=== Configuração de Desenvolvimento Symlink - Coyote Framework ===" -ForegroundColor Cyan
Write-Host ""

# Verificar se estamos no diretório correto
$frameworkPath = Get-Location
$frameworkComposerJson = Join-Path $frameworkPath "composer.json"

if (-not (Test-Path $frameworkComposerJson)) {
    Write-Host "ERRO: Não encontrado composer.json no diretório atual" -ForegroundColor Red
    Write-Host "Execute este script do diretório raiz do Coyote Framework" -ForegroundColor Yellow
    exit 1
}

Write-Host "✓ Framework encontrado em: $frameworkPath" -ForegroundColor Green

# Verificar/Criar aplicação de exemplo
if (-not (Test-Path $ExampleAppPath)) {
    Write-Host "Aplicação de exemplo não encontrada em: $ExampleAppPath" -ForegroundColor Yellow
    Write-Host "Deseja criar uma aplicação de exemplo? (S/N)" -ForegroundColor Yellow
    $response = Read-Host
    
    if ($response -eq "S" -or $response -eq "s") {
        Write-Host "Criando aplicação de exemplo..." -ForegroundColor Cyan
        
        # Criar diretório
        New-Item -ItemType Directory -Path $ExampleAppPath -Force | Out-Null
        
        # Criar composer.json básico
        $exampleComposerJson = @"
{
    "name": "coyote/example-app",
    "description": "Aplicação de exemplo do Coyote Framework",
    "type": "project",
    "require": {
        "coyote/framework": "@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "$frameworkPath",
            "options": {
                "symlink": true
            }
        }
    ],
    "minimum-stability": "dev",
    "prefer-stable": true
}
"@
        
        Set-Content -Path (Join-Path $ExampleAppPath "composer.json") -Value $exampleComposerJson
        
        # Criar estrutura básica
        New-Item -ItemType Directory -Path (Join-Path $ExampleAppPath "app") -Force | Out-Null
        New-Item -ItemType Directory -Path (Join-Path $ExampleAppPath "public") -Force | Out-Null
        
        Write-Host "✓ Aplicação de exemplo criada em: $ExampleAppPath" -ForegroundColor Green
    } else {
        Write-Host "Operação cancelada pelo usuário" -ForegroundColor Yellow
        exit 0
    }
}

# Verificar composer.json da aplicação de exemplo
$exampleComposerJsonPath = Join-Path $ExampleAppPath "composer.json"
if (-not (Test-Path $exampleComposerJsonPath)) {
    Write-Host "ERRO: composer.json não encontrado na aplicação de exemplo" -ForegroundColor Red
    exit 1
}

Write-Host "✓ Aplicação de exemplo encontrada em: $ExampleAppPath" -ForegroundColor Green

# Configurar repositório local no composer.json da aplicação de exemplo
Write-Host "Configurando repositório local..." -ForegroundColor Cyan

$composerJson = Get-Content $exampleComposerJsonPath -Raw | ConvertFrom-Json

# Adicionar ou atualizar repositório
if (-not $composerJson.repositories) {
    $composerJson | Add-Member -NotePropertyName "repositories" -NotePropertyValue @()
}

# Verificar se já existe repositório para o framework
$frameworkRepo = $composerJson.repositories | Where-Object { $_.url -eq $frameworkPath }

if (-not $frameworkRepo) {
    $newRepo = @{
        type = "path"
        url = $frameworkPath
        options = @{
            symlink = $true
        }
    }
    
    $composerJson.repositories += $newRepo
    Write-Host "✓ Repositório local adicionado" -ForegroundColor Green
} else {
    Write-Host "✓ Repositório local já configurado" -ForegroundColor Green
}

# Salvar composer.json atualizado
$composerJson | ConvertTo-Json -Depth 10 | Set-Content -Path $exampleComposerJsonPath

# Instalar/Atualizar dependências
Write-Host "Instalando/Atualizando dependências..." -ForegroundColor Cyan
Push-Location $ExampleAppPath
try {
    composer update --no-interaction
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Dependências instaladas com sucesso" -ForegroundColor Green
    } else {
        Write-Host "ERRO: Falha ao instalar dependências" -ForegroundColor Red
        exit 1
    }
} finally {
    Pop-Location
}

# Verificar symlink
$vendorPath = Join-Path $ExampleAppPath "vendor\coyote\framework"
if (Test-Path $vendorPath) {
    $item = Get-Item $vendorPath
    if ($item.LinkType -eq "SymbolicLink") {
        Write-Host "✓ Symlink configurado com sucesso: $vendorPath -> $frameworkPath" -ForegroundColor Green
    } else {
        Write-Host "⚠ Aviso: $vendorPath não é um symlink (pode ser uma cópia)" -ForegroundColor Yellow
    }
} else {
    Write-Host "ERRO: Symlink não criado em $vendorPath" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "=== CONFIGURAÇÃO COMPLETA ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Para desenvolver no framework:" -ForegroundColor White
Write-Host "1. Edite os arquivos em: $frameworkPath\src\" -ForegroundColor Yellow
Write-Host "2. As mudanças serão refletidas automaticamente em: $ExampleAppPath\vendor\coyote\framework\" -ForegroundColor Yellow
Write-Host ""
Write-Host "Para testar a aplicação de exemplo:" -ForegroundColor White
Write-Host "cd $ExampleAppPath" -ForegroundColor Green
Write-Host "php -S localhost:8000 -t public" -ForegroundColor Green
Write-Host ""
Write-Host "Para atualizar as dependências após mudanças no composer.json do framework:" -ForegroundColor White
Write-Host "cd $ExampleAppPath && composer update" -ForegroundColor Green