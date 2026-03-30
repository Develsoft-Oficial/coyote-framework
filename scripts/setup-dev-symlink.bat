@echo off
REM Script de configuração de desenvolvimento symlink para Coyote Framework
REM Windows Batch version

echo === Configuração de Desenvolvimento Symlink - Coyote Framework ===
echo.

REM Verificar se estamos no diretório correto
if not exist composer.json (
    echo ERRO: Não encontrado composer.json no diretório atual
    echo Execute este script do diretório raiz do Coyote Framework
    pause
    exit /b 1
)

set FRAMEWORK_PATH=%CD%
echo ✓ Framework encontrado em: %FRAMEWORK_PATH%

REM Definir caminho da aplicação de exemplo
set EXAMPLE_APP_PATH=..\coyote-example-app

REM Verificar/Criar aplicação de exemplo
if not exist "%EXAMPLE_APP_PATH%" (
    echo Aplicação de exemplo não encontrada em: %EXAMPLE_APP_PATH%
    echo Deseja criar uma aplicação de exemplo? (S/N)
    set /p RESPONSE=
    
    if /i "%RESPONSE%"=="S" (
        echo Criando aplicação de exemplo...
        
        REM Criar diretório
        mkdir "%EXAMPLE_APP_PATH%" 2>nul
        
        REM Criar composer.json básico
        echo {> "%EXAMPLE_APP_PATH%\composer.json"
        echo     "name": "coyote/example-app",>> "%EXAMPLE_APP_PATH%\composer.json"
        echo     "description": "Aplicação de exemplo do Coyote Framework",>> "%EXAMPLE_APP_PATH%\composer.json"
        echo     "type": "project",>> "%EXAMPLE_APP_PATH%\composer.json"
        echo     "require": {>> "%EXAMPLE_APP_PATH%\composer.json"
        echo         "coyote/framework": "@dev">> "%EXAMPLE_APP_PATH%\composer.json"
        echo     },>> "%EXAMPLE_APP_PATH%\composer.json"
        echo     "repositories": [>> "%EXAMPLE_APP_PATH%\composer.json"
        echo         {>> "%EXAMPLE_APP_PATH%\composer.json"
        echo             "type": "path",>> "%EXAMPLE_APP_PATH%\composer.json"
        echo             "url": "%FRAMEWORK_PATH:\=/%",>> "%EXAMPLE_APP_PATH%\composer.json"
        echo             "options": {>> "%EXAMPLE_APP_PATH%\composer.json"
        echo                 "symlink": true>> "%EXAMPLE_APP_PATH%\composer.json"
        echo             }>> "%EXAMPLE_APP_PATH%\composer.json"
        echo         }>> "%EXAMPLE_APP_PATH%\composer.json"
        echo     ],>> "%EXAMPLE_APP_PATH%\composer.json"
        echo     "minimum-stability": "dev",>> "%EXAMPLE_APP_PATH%\composer.json"
        echo     "prefer-stable": true>> "%EXAMPLE_APP_PATH%\composer.json"
        echo }>> "%EXAMPLE_APP_PATH%\composer.json"
        
        REM Criar estrutura básica
        mkdir "%EXAMPLE_APP_PATH%\app" 2>nul
        mkdir "%EXAMPLE_APP_PATH%\public" 2>nul
        
        echo ✓ Aplicação de exemplo criada em: %EXAMPLE_APP_PATH%
    ) else (
        echo Operação cancelada pelo usuário
        pause
        exit /b 0
    )
)

REM Verificar composer.json da aplicação de exemplo
if not exist "%EXAMPLE_APP_PATH%\composer.json" (
    echo ERRO: composer.json não encontrado na aplicação de exemplo
    pause
    exit /b 1
)

echo ✓ Aplicação de exemplo encontrada em: %EXAMPLE_APP_PATH%

REM Nota: Para configuração automática do composer.json, use o script PowerShell
echo.
echo Para configurar o symlink, execute o script PowerShell:
echo powershell -ExecutionPolicy Bypass -File scripts\setup-dev-symlink.ps1
echo.
echo OU configure manualmente:
echo.
echo 1. Adicione ao composer.json da aplicação de exemplo:
echo.
echo "repositories": [
echo     {
echo         "type": "path",
echo         "url": "%FRAMEWORK_PATH:\=/%",
echo         "options": {
echo             "symlink": true
echo         }
echo     }
echo ]
echo.
echo 2. Execute na aplicação de exemplo:
echo cd %EXAMPLE_APP_PATH%
echo composer update
echo.
pause