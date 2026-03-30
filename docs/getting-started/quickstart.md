# Guia Rápido - Primeira Aplicação

Este guia rápido mostra como criar uma aplicação web simples com o Coyote Framework em menos de 10 minutos.

## 🎯 O que vamos construir

Vamos criar uma aplicação de lista de tarefas (To-Do List) com:
- Página inicial com lista de tarefas
- Formulário para adicionar novas tarefas
- Funcionalidade para marcar tarefas como concluídas
- Persistência em banco de dados SQLite

## 📁 Passo 1: Estrutura do Projeto

Crie a seguinte estrutura de diretórios:

```
todo-app/
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Providers/
├── config/
├── public/
├── resources/
│   └── views/
├── routes/
├── storage/
└── vendors/
```

## ⚙️ Passo 2: Configuração Básica

### 2.1 Arquivo `public/index.php`

```php
<?php
// public/index.php

require_once __DIR__ . '/../vendor/autoload.php';

use Coyote\Core\Application;

// Criar aplicação
$app = new Application(dirname(__DIR__));

// Registrar provedores
$app->register(\Coyote\Providers\ConfigServiceProvider::class);
$app->register(\Coyote\Providers\ViewServiceProvider::class);
$app->register(\Coyote\Providers\EventServiceProvider::class);

// Executar
$app->run();
```

### 2.2 Arquivo `config/app.php`

```php
<?php
// config/app.php

return [
    'name' => 'Todo App',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://localhost:8000',
    'timezone' => 'America/Sao_Paulo',
];
```

### 2.3 Arquivo `config/database.php`

```php
<?php
// config/database.php

return [
    'default' => 'sqlite',
    
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../storage/database.sqlite',
            'prefix' => '',
        ],
        
        'mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'todo_app',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
    ],
];
```

## 🗄️ Passo 3: Model e Migration

### 3.1 Criar Model `app/Models/Task.php`

```php
<?php
// app/Models/Task.php

namespace App\Models;

use Coyote\Database\Model;

class Task extends Model
{
    /**
     * Nome da tabela
     */
    protected $table = 'tasks';
    
    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = ['title', 'description', 'completed'];
    
    /**
     * Tipos de dados para casting
     */
    protected $casts = [
        'completed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Marcar tarefa como concluída
     */
    public function markAsCompleted()
    {
        $this->completed = true;
        return $this->save();
    }
    
    /**
     * Marcar tarefa como pendente
     */
    public function markAsPending()
    {
        $this->completed = false;
        return $this->save();
    }
    
    /**
     * Escopo para tarefas concluídas
     */
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }
    
    /**
     * Escopo para tarefas pendentes
     */
    public function scopePending($query)
    {
        return $query->where('completed', false);
    }
}
```

### 3.2 Criar Migration

Crie o arquivo `database/migrations/2026_03_30_000001_create_tasks_table.php`:

```php
<?php
// database/migrations/2026_03_30_000001_create_tasks_table.php

use Coyote\Database\Migrations\Migration;
use Coyote\Database\Schema\Blueprint;

class CreateTasksTable extends Migration
{
    /**
     * Executar a migration
     */
    public function up()
    {
        $this->schema->create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }
    
    /**
     * Reverter a migration
     */
    public function down()
    {
        $this->schema->dropIfExists('tasks');
    }
}
```

### 3.3 Executar Migration

```bash
php vendor/bin/coyote migrate
```

## 🎮 Passo 4: Controller

### 4.1 Criar Controller `app/Controllers/TaskController.php`

```php
<?php
// app/Controllers/TaskController.php

namespace App\Controllers;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * Listar todas as tarefas
     */
    public function index()
    {
        $tasks = Task::orderBy('created_at', 'desc')->get();
        
        return view('tasks.index', [
            'tasks' => $tasks,
            'completedCount' => Task::completed()->count(),
            'pendingCount' => Task::pending()->count(),
        ]);
    }
    
    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        return view('tasks.create');
    }
    
    /**
     * Salvar nova tarefa
     */
    public function store(Request $request)
    {
        // Validar dados
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        // Criar tarefa
        Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'completed' => false,
        ]);
        
        // Redirecionar com mensagem de sucesso
        return redirect('/tasks')
            ->with('success', 'Tarefa criada com sucesso!');
    }
    
    /**
     * Marcar tarefa como concluída
     */
    public function complete($id)
    {
        $task = Task::findOrFail($id);
        $task->markAsCompleted();
        
        return redirect('/tasks')
            ->with('success', 'Tarefa marcada como concluída!');
    }
    
    /**
     * Excluir tarefa
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        
        return redirect('/tasks')
            ->with('success', 'Tarefa excluída com sucesso!');
    }
}
```

## 🎨 Passo 5: Views

### 5.1 Layout Principal `resources/views/layouts/app.php`

```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Todo App' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">Todo App</a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <?php if (session()->has('success')): ?>
            <div class="alert alert-success">
                <?= session()->get('success') ?>
            </div>
        <?php endif; ?>
        
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger">
                <?= session()->get('error') ?>
            </div>
        <?php endif; ?>
        
        <?= $content ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

### 5.2 Lista de Tarefas `resources/views/tasks/index.php`

```php
<?php
// resources/views/tasks/index.php
$content = ob_start();
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Minhas Tarefas</h5>
                <a href="/tasks/create" class="btn btn-primary btn-sm">
                    + Nova Tarefa
                </a>
            </div>
            <div class="card-body">
                <?php if ($tasks->isEmpty()): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">Nenhuma tarefa encontrada.</p>
                        <a href="/tasks/create" class="btn btn-primary">
                            Criar Primeira Tarefa
                        </a>
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($tasks as $task): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 <?= $task->completed ? 'text-decoration-line-through text-muted' : '' ?>">
                                        <?= htmlspecialchars($task->title) ?>
                                    </h6>
                                    <?php if ($task->description): ?>
                                        <p class="mb-1 text-muted small">
                                            <?= htmlspecialchars($task->description) ?>
                                        </p>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        Criada em: <?= $task->created_at->format('d/m/Y H:i') ?>
                                    </small>
                                </div>
                                <div class="btn-group">
                                    <?php if (!$task->completed): ?>
                                        <a href="/tasks/<?= $task->id ?>/complete" 
                                           class="btn btn-success btn-sm">
                                            Concluir
                                        </a>
                                    <?php endif; ?>
                                    <form action="/tasks/<?= $task->id ?>" 
                                          method="POST" 
                                          class="d-inline">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <small>
                    <?= $completedCount ?> concluídas • 
                    <?= $pendingCount ?> pendentes • 
                    <?= count($tasks) ?> total
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Estatísticas</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Total de Tarefas:</strong> <?= count($tasks) ?>
                </div>
                <div class="mb-3">
                    <strong>Concluídas:</strong> <?= $completedCount ?>
                </div>
                <div class="mb-3">
                    <strong>Pendentes:</strong> <?= $pendingCount ?>
                </div>
                <div>
                    <strong>Taxa de Conclusão:</strong>
                    <?php if (count($tasks) > 0): ?>
                        <?= round(($completedCount / count($tasks)) * 100) ?>%
                    <?php else: ?>
                        0%
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
```

### 5.3 Formulário de Criação `resources/views/tasks/create.php`

```php
<?php
// resources/views/tasks/create.php
$content = ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Nova Tarefa</h5>
            </div>
            <div class="card-body">
                <form action="/tasks" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título *</label>
                        <input type="text" 
                               class="form-control" 
                               id="title" 
                               name="title" 
                               required
                               maxlength="255">
                        <div class="form-text">
                            Título breve da tarefa (máx. 255 caracteres)
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="4"></textarea>
                        <div class="form-text">
                            Detalhes adicionais sobre a tarefa (opcional)
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/tasks" class="btn btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Salvar Tarefa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
```

## 🛣️ Passo 6: Rotas

### 6.1 Arquivo `routes/web.php`

```php
<?php
// routes/web.php

use App\Controllers\TaskController;

// Rotas da aplicação
$router->get('/', [TaskController::class, 'index']);
$router->get('/tasks', [TaskController::class, 'index']);
$router->get('/tasks/create', [TaskController::class, 'create']);
$router->post('/tasks', [TaskController::class, 'store']);
$router->post('/tasks/{id}/complete', [TaskController::class, 'complete']);
$router->delete('/tasks/{id}', [TaskController::class, 'destroy']);
```

## 🚀 Passo 7: Executar a Aplicação

### 7.1 Iniciar o servidor de desenvolvimento:

```bash
php -S localhost:8000 -t public/
```

### 7.2 Acessar no navegador:

```
http://localhost:8000
```

## 📝 Passo 8: Testar Funcionalidades

1. **Adicionar tarefas:** Clique em "Nova Tarefa" e preencha o formulário
2. **Marcar como concluída:** Clique no botão "Concluir" ao lado de uma tarefa
3. **Excluir tarefas:** Clique no botão "Excluir"
4. **Ver estatísticas:** Observe o painel lateral com as estatísticas

## 🔧 Personalizações Avançadas

### Adicionar Validação Personalizada

```php
// No TaskController::store()
$validated = $request->validate([
    'title' => [
        'required',
        'string',
        'max:255',
        function ($attribute, $value, $fail) {
            if (str_word_count($value) < 2) {
                $fail('O título deve ter pelo menos 2 palavras.');
            }
        },
    ],
    'description' => 'nullable|string|max:1000',
]);
```

### Adicionar Filtros

```php
// Nova rota para filtrar tarefas
$router->get('/tasks/filter/{status}', [TaskController::class, 'filter']);

// No TaskController
public function filter($status)
{
    if ($status === 'completed') {
        $tasks = Task::completed()->get();
    } elseif ($status === 'pending') {
        $tasks = Task::pending()->get();
    } else {
        $tasks = Task::all();
    }
    
    return view('tasks.index', [
        'tasks' => $tasks,
        'filter' => $status,
    ]);
}
```

### Adicionar API REST

```php
// routes/api.php
$router->prefix('api')->group(function ($router) {
    $router->get('/tasks', [TaskController::class, 'apiIndex']);
    $router->post('/tasks', [TaskController::class, 'apiStore']);
    $router->get('/tasks/{id}', [TaskController::class, 'apiShow']);
    $router->put('/tasks/{id}', [TaskController::class, 'apiUpdate']);
    $router->delete('/tasks/{id}', [TaskController::class, 'apiDestroy']);
});
```

## 🎉 Conclusão

Parabéns! Você criou sua primeira aplicação completa com o Coyote Framework. Você aprendeu:

- ✅ Configurar uma aplicação básica
- ✅ Criar models e migrations
- ✅ Implementar controllers
- ✅ Desenvolver views com templates
- ✅ Definir rotas
- ✅ Trabalhar com banco de dados

## 📚 Próximos Passos

Agora que você tem uma aplicação funcional, explore:

1. **[Autenticação de Usuários](../auth/auth-manager.md)** - Adicion