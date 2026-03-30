<?php
// vendors/coyote/View/View.php

namespace Coyote\View;

use Coyote\Core\Application;

/**
 * Classe View - Representa uma view renderizável
 */
class View
{
    /**
     * @var string Caminho para o arquivo da view
     */
    protected $path;

    /**
     * @var array Dados para a view
     */
    protected $data = [];

    /**
     * @var Application Instância da aplicação
     */
    protected $app;

    /**
     * @var string Diretório base das views
     */
    protected $basePath;

    /**
     * Criar nova view
     *
     * @param Application $app
     * @param string $path
     * @param array $data
     */
    public function __construct(Application $app, string $path, array $data = [])
    {
        $this->app = $app;
        $this->path = $path;
        $this->data = $data;
        $this->basePath = $app->basePath('resources/views');
    }

    /**
     * Obter caminho da view
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Obter dados da view
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Adicionar dados à view
     *
     * @param string|array $key
     * @param mixed $value
     * @return self
     */
    public function with($key, $value = null): self
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Renderizar view
     *
     * @return string
     * @throws \RuntimeException
     */
    public function render(): string
    {
        $fullPath = $this->getFullPath();

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("View file not found: {$fullPath}");
        }

        // Extrair dados para variáveis locais
        extract($this->data, EXTR_SKIP);

        // Iniciar buffer de saída
        ob_start();

        try {
            include $fullPath;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Obter caminho completo do arquivo
     *
     * @return string
     */
    protected function getFullPath(): string
    {
        // Se já for caminho absoluto, retornar
        if (file_exists($this->path)) {
            return $this->path;
        }

        // Converter notação de ponto para caminho de arquivo
        $path = str_replace('.', '/', $this->path);
        
        // Adicionar extensão .php se não tiver
        if (!preg_match('/\.php$/i', $path)) {
            $path .= '.php';
        }

        // Construir caminho completo
        return $this->basePath . '/' . $path;
    }

    /**
     * Escapar string para HTML
     *
     * @param string $value
     * @param bool $doubleEncode
     * @return string
     */
    public function e(string $value, bool $doubleEncode = true): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }

    /**
     * Verificar se view existe
     *
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->getFullPath());
    }

    /**
     * Obter string da view renderizada
     *
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Throwable $e) {
            return 'Error rendering view: ' . $e->getMessage();
        }
    }

    /**
     * Incluir partial/view
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    public function include(string $view, array $data = []): string
    {
        $viewInstance = new self($this->app, $view, array_merge($this->data, $data));
        return $viewInstance->render();
    }

    /**
     * Renderizar seção
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function section(string $name, $default = null)
    {
        // Implementação básica de seções
        // Em um sistema mais completo, isso seria gerenciado pelo ViewFactory
        return $default;
    }

    /**
     * Estender layout
     *
     * @param string $layout
     * @return void
     */
    public function extends(string $layout): void
    {
        // Em um sistema mais completo, isso seria gerenciado pelo ViewFactory
        // Por enquanto, apenas inclui o layout
        echo $this->include($layout, $this->data);
    }
}