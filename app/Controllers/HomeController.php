<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use Coyote\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Página inicial
     */
    public function index()
    {
        return $this->view('home', [
            'title' => 'Bem-vindo ao Coyote Framework',
            'message' => 'Este é um exemplo de controller funcionando!',
            'features' => [
                'Roteamento avançado',
                'Sistema de middleware',
                'Controllers base',
                'Sistema de views',
                'Container DI',
                'Service Providers'
            ]
        ]);
    }

    /**
     * Página sobre
     */
    public function about()
    {
        return $this->html('<h1>Sobre o Coyote Framework</h1><p>Um micro-framework PHP leve e poderoso.</p>');
    }

    /**
     * API exemplo
     */
    public function api()
    {
        return $this->json([
            'status' => 'success',
            'message' => 'API funcionando!',
            'timestamp' => time(),
            'data' => [
                'framework' => 'Coyote',
                'version' => $this->app->version(),
                'environment' => $this->app->environment()
            ]
        ]);
    }

    /**
     * Página com parâmetros
     */
    public function show($id)
    {
        return $this->html("<h1>Item #{$id}</h1><p>Detalhes do item {$id}</p>");
    }
}