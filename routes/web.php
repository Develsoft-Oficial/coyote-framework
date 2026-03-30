<?php
// routes/web.php

use Coyote\Routing\Router;

/** @var Router $router */

// Rota principal
$router->get('/', 'App\Controllers\HomeController@index')->name('home');

// Rota sobre
$router->get('/about', 'App\Controllers\HomeController@about')->name('about');

// Rota API
$router->get('/api', 'App\Controllers\HomeController@api')->name('api');

// Rota com parâmetro
$router->get('/items/{id}', 'App\Controllers\HomeController@show')->name('items.show');

// Exemplo de grupo de rotas
$router->group(['prefix' => 'admin', 'name' => 'admin.'], function ($router) {
    $router->get('/dashboard', function () {
        return 'Painel Administrativo';
    })->name('dashboard');
    
    $router->get('/users', function () {
        return 'Lista de Usuários';
    })->name('users');
});

// Exemplo de rota POST
$router->post('/contact', function () {
    return 'Formulário de contato enviado!';
})->name('contact.submit');

// Exemplo de rota com múltiplos métodos
$router->match(['GET', 'POST'], '/login', function () {
    return 'Página de Login';
})->name('login');

// Exemplo de recurso RESTful (comentado por enquanto)
// $router->resource('products', 'App\Controllers\ProductController');