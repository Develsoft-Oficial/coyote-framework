<?php
// vendors/coyote/Http/Controllers/RestController.php

namespace Coyote\Http\Controllers;

use Coyote\Http\Request;
use Coyote\Http\Response;

/**
 * Controller base para APIs RESTful
 */
abstract class RestController extends Controller
{
    /**
     * @var array Métodos HTTP suportados
     */
    protected $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * @var string Formato de resposta padrão
     */
    protected $responseFormat = 'json';

    /**
     * Manipular requisição RESTful
     *
     * @param string $method
     * @param array $parameters
     * @return Response
     */
    public function callAction(string $method, array $parameters): Response
    {
        // Verificar método HTTP
        $httpMethod = $this->request->getMethod();
        
        if (!in_array($httpMethod, $this->allowedMethods)) {
            return $this->methodNotAllowed();
        }

        // Mapear método HTTP para método do controller
        $actionMethod = $this->mapHttpMethodToAction($httpMethod, $method, $parameters);
        
        if ($actionMethod === null) {
            return $this->methodNotAllowed();
        }

        return parent::callAction($actionMethod, $parameters);
    }

    /**
     * Mapear método HTTP para método do controller
     *
     * @param string $httpMethod
     * @param string $method
     * @param array $parameters
     * @return string|null
     */
    protected function mapHttpMethodToAction(string $httpMethod, string $method, array $parameters): ?string
    {
        // Se o método já existe, usá-lo
        if (method_exists($this, $method)) {
            return $method;
        }

        // Mapeamento padrão RESTful
        $mappings = [
            'GET' => 'index',
            'POST' => 'store',
            'PUT' => 'update',
            'PATCH' => 'update',
            'DELETE' => 'destroy',
        ];

        // Se o método for um ID, usar métodos específicos
        if (!empty($parameters) && is_numeric($parameters[0])) {
            $mappings = [
                'GET' => 'show',
                'PUT' => 'update',
                'PATCH' => 'update',
                'DELETE' => 'destroy',
            ];
        }

        $action = $mappings[$httpMethod] ?? null;
        
        if ($action && method_exists($this, $action)) {
            return $action;
        }

        return null;
    }

    /**
     * Retornar lista de recursos
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Index method not implemented',
            'data' => []
        ], 501);
    }

    /**
     * Mostrar recurso específico
     *
     * @param int $id
     * @return Response
     */
    public function show(int $id): Response
    {
        return $this->json([
            'message' => 'Show method not implemented',
            'id' => $id
        ], 501);
    }

    /**
     * Armazenar novo recurso
     *
     * @return Response
     */
    public function store(): Response
    {
        return $this->json([
            'message' => 'Store method not implemented'
        ], 501);
    }

    /**
     * Atualizar recurso
     *
     * @param int $id
     * @return Response
     */
    public function update(int $id): Response
    {
        return $this->json([
            'message' => 'Update method not implemented',
            'id' => $id
        ], 501);
    }

    /**
     * Remover recurso
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        return $this->json([
            'message' => 'Destroy method not implemented',
            'id' => $id
        ], 501);
    }

    /**
     * Retornar erro 405 Method Not Allowed
     *
     * @return Response
     */
    protected function methodNotAllowed(): Response
    {
        return $this->json([
            'error' => 'Method Not Allowed',
            'message' => 'The requested method is not allowed for this resource',
            'allowed_methods' => $this->allowedMethods
        ], 405);
    }

    /**
     * Retornar sucesso
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return Response
     */
    protected function success($data = null, string $message = 'Success', int $status = 200): Response
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => time()
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->json($response, $status);
    }

    /**
     * Retornar erro
     *
     * @param string $message
     * @param int $status
     * @param array $errors
     * @return Response
     */
    protected function errorResponse(string $message, int $status = 400, array $errors = []): Response
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => time()
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return $this->json($response, $status);
    }

    /**
     * Validar requisição
     *
     * @param array $rules
     * @return array|Response
     */
    protected function validate(array $rules)
    {
        if ($this->app->bound('validator')) {
            $validator = $this->app->make('validator');
            return $validator->validate($this->request->all(), $rules);
        }

        // Validação simples
        $data = $this->request->all();
        $errors = [];

        foreach ($rules as $field => $rule) {
            if (is_string($rule)) {
                $ruleParts = explode('|', $rule);
                
                foreach ($ruleParts as $part) {
                    if ($part === 'required' && (!isset($data[$field]) || $data[$field] === '')) {
                        $errors[$field][] = "The {$field} field is required.";
                    }
                    
                    if ($part === 'email' && isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = "The {$field} must be a valid email address.";
                    }
                    
                    if (strpos($part, 'min:') === 0 && isset($data[$field])) {
                        $min = (int) substr($part, 4);
                        if (strlen($data[$field]) < $min) {
                            $errors[$field][] = "The {$field} must be at least {$min} characters.";
                        }
                    }
                }
            }
        }

        if (!empty($errors)) {
            return $this->errorResponse('Validation failed', 422, $errors);
        }

        return $data;
    }

    /**
     * Obter dados paginados
     *
     * @param array $data
     * @param int $total
     * @param int $page
     * @param int $perPage
     * @return array
     */
    protected function paginate(array $data, int $total, int $page = 1, int $perPage = 15): array
    {
        $totalPages = ceil($total / $perPage);
        
        return [
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_previous' => $page > 1
            ]
        ];
    }
}