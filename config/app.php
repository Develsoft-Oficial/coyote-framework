<?php
// config/app.php

return [
    /*
    |--------------------------------------------------------------------------
    | Nome da Aplicação
    |--------------------------------------------------------------------------
    |
    | Este valor é o nome da sua aplicação. Este valor é usado quando o
    | framework precisa colocar o nome da aplicação em uma notificação ou
    | qualquer outro local conforme exigido pela aplicação ou seus pacotes.
    |
    */
    'name' => env('APP_NAME', 'Coyote Framework'),

    /*
    |--------------------------------------------------------------------------
    | Ambiente da Aplicação
    |--------------------------------------------------------------------------
    |
    | Este valor determina o "ambiente" em que sua aplicação está atualmente
    | em execução. Isso pode determinar como você prefere configurar vários
    | serviços que sua aplicação utiliza. Defina isso no arquivo ".env".
    |
    */
    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Modo Debug
    |--------------------------------------------------------------------------
    |
    | Quando seu aplicativo está no modo de depuração, mensagens de erro
    | detalhadas com stack traces serão mostradas em cada erro que ocorre
    | dentro de seu aplicativo. Se desativado, uma página de erro genérica
    | simples é mostrada.
    |
    */
    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL da Aplicação
    |--------------------------------------------------------------------------
    |
    | Esta URL é usada pelo console para gerar URLs corretamente ao usar
    | a ferramenta de linha de comando Artisan. Você deve definir isso como
    | a raiz da sua aplicação para que seja usado ao executar tarefas Artisan.
    |
    */
    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Fuso Horário da Aplicação
    |--------------------------------------------------------------------------
    |
    | Aqui você pode especificar o fuso horário padrão para sua aplicação,
    | que será usado pelas funções de data e hora do PHP. Nós fomos em frente
    | e definimos isso como um padrão sensato para você.
    |
    */
    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Localidade da Aplicação
    |--------------------------------------------------------------------------
    |
    | A localidade da aplicação determina a localidade padrão que será usada
    | pelo provedor de tradução. Você é livre para definir este valor
    | para qualquer uma das localidades que serão suportadas pela aplicação.
    |
    */
    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Localidade de Fallback
    |--------------------------------------------------------------------------
    |
    | A localidade de fallback determina a localidade a ser usada quando a
    | atual não está disponível. Você pode alterar o valor para corresponder
    | a qualquer uma das pastas de idiomas fornecidas em sua aplicação.
    |
    */
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Chave de Criptografia
    |--------------------------------------------------------------------------
    |
    | Esta chave é usada pelo serviço de criptografia do Coyote e deve ser
    | definida como uma string aleatória de 32 caracteres, caso contrário,
    | essas strings criptografadas não serão seguras. Por favor, faça isso
    | antes de implantar uma aplicação!
    |
    */
    'key' => env('APP_KEY', 'base64:your-secret-key-here'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Provedores de Serviço
    |--------------------------------------------------------------------------
    |
    | Os provedores de serviços são os componentes centrais do Coyote Framework.
    | Eles são carregados durante a inicialização da aplicação para fornecer
    | serviços essenciais para a execução da aplicação.
    |
    */
    'providers' => [
        // Coyote Service Providers
        Coyote\Providers\ConfigServiceProvider::class,
        Coyote\Providers\EventServiceProvider::class,
        Coyote\Providers\LogServiceProvider::class,

        // Application Service Providers
        App\Providers\AppServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Aliases de Classe
    |--------------------------------------------------------------------------
    |
    | Esta matriz de aliases de classe será registrada quando esta aplicação
    | for iniciada. No entanto, sinta-se à vontade para registrar quantos
    | quiser aqui para facilitar o desenvolvimento.
    |
    */
    'aliases' => [
        'App' => Coyote\Support\Facades\App::class,
        'Config' => Coyote\Support\Facades\Config::class,
        'Log' => Coyote\Support\Facades\Log::class,
    ],
];