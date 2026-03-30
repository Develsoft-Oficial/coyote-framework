# Tutorial: Criando um Blog Completo

Neste tutorial, vamos criar um blog completo com o Coyote Framework, incluindo:
- Sistema de autenticação
- CRUD de posts
- Comentários
- Categorias
- Sistema de tags
- Painel administrativo

## 🎯 Funcionalidades do Blog

1. **Frontend Público:**
   - Listagem de posts com paginação
   - Página individual de post
   - Sistema de comentários
   - Busca por tags e categorias
   - Arquivo por mês/ano

2. **Backend Administrativo:**
   - Login de administrador
   - CRUD de posts
   - Gerenciamento de comentários
   - Gerenciamento de categorias e tags
   - Estatísticas do blog

## 📁 Estrutura do Projeto

```
blog/
├── app/
│   ├── Controllers/
│   │   ├── BlogController.php
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── PostController.php
│   │   │   └── CommentController.php
│   │   └── Auth/
│   │       ├── LoginController.php
│   │       └── RegisterController.php
│   ├── Models/
│   │   ├── Post.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Comment.php
│   │   └── User.php
│   └── Middleware/
│       ├── AdminMiddleware.php
│       └── ...
├── resources/
│   └── views/
│       ├── blog/
│       │   ├── index.php
│       │   ├── show.php
│       │   └── category.php
│       ├── admin/
│       │   ├── dashboard.php
│       │   ├── posts/
│       │   │   ├── index.php
│       │   │   ├── create.php
│       │   │   ├── edit.php
│       │   │   └── show.php
│       │   └── layout.php
│       └── auth/
│           ├── login.php
│           └── register.php
└── ...
```

## 🗄️ Passo 1: Configuração do Banco de Dados

### 1.1 Arquivo de Configuração `config/database.php`

```php
<?php
return [
    'default' => 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'blog_db'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
    ],
];
```

### 1.2 Arquivo `.env`

```env
APP_NAME="Meu Blog"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog_db
DB_USERNAME=root
DB_PASSWORD=
```

## 🗃️ Passo 2: Migrations

### 2.1 Migration de Usuários

```php
<?php
// database/migrations/2026_03_30_000001_create_users_table.php

use Coyote\Database\Migrations\Migration;
use Coyote\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    
    public function down()
    {
        $this->schema->dropIfExists('users');
    }
}
```

### 2.2 Migration de Posts

```php
<?php
// database/migrations/2026_03_30_000002_create_posts_table.php

class CreatePostsTable extends Migration
{
    public function up()
    {
        $this->schema->create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('views')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }
    
    public function down()
    {
        $this->schema->dropIfExists('posts');
    }
}
```

### 2.3 Migration de Categorias

```php
<?php
// database/migrations/2026_03_30_000003_create_categories_table.php

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        $this->schema->create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('#6c757d');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        $this->schema->dropIfExists('categories');
    }
}
```

### 2.4 Migration de Tags

```php
<?php
// database/migrations/2026_03_30_000004_create_tags_table.php

class CreateTagsTable extends Migration
{
    public function up()
    {
        $this->schema->create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });
        
        // Tabela pivot posts_tags
        $this->schema->create('post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['post_id', 'tag_id']);
        });
    }
    
    public function down()
    {
        $this->schema->dropIfExists('post_tag');
        $this->schema->dropIfExists('tags');
    }
}
```

### 2.5 Migration de Comentários

```php
<?php
// database/migrations/2026_03_30_000005_create_comments_table.php

class CreateCommentsTable extends Migration
{
    public function up()
    {
        $this->schema->create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'spam', 'trash'])->default('pending');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['post_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }
    
    public function down()
    {
        $this->schema->dropIfExists('comments');
    }
}
```

### 2.6 Executar Migrations

```bash
php vendor/bin/coyote migrate
```

## 🧠 Passo 3: Models

### 3.1 Model `Post.php`

```php
<?php
// app/Models/Post.php

namespace App\Models;

use Coyote\Database\Model;
use Coyote\Database\ModelCollection;

class Post extends Model
{
    protected $table = 'posts';
    
    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'excerpt',
        'content', 'featured_image', 'status', 'is_featured',
        'published_at'
    ];
    
    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'views' => 'integer',
    ];
    
    protected $dates = ['published_at'];
    
    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class)->where('status', 'approved');
    }
    
    public function allComments()
    {
        return $this->hasMany(Comment::class);
    }
    
    // Escopos de consulta
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
    
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('content', 'LIKE', "%{$term}%")
              ->orWhere('excerpt', 'LIKE', "%{$term}%");
        });
    }
    
    // Métodos de negócio
    public function publish()
    {
        $this->status = 'published';
        $this->published_at = now();
        return $this->save();
    }
    
    public function unpublish()
    {
        $this->status = 'draft';
        return $this->save();
    }
    
    public function incrementViews()
    {
        $this->increment('views');
    }
    
    public function getReadingTime()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200); // 200 palavras por minuto
        return max(1, $minutes);
    }
    
    public function getExcerpt($length = 150)
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }
        
        $content = strip_tags($this->content);
        if (strlen($content) <= $length) {
            return $content;
        }
        
        return substr($content, 0, $length) . '...';
    }
}
```

### 3.2 Model `Category.php`

```php
<?php
// app/Models/Category.php

namespace App\Models;

use Coyote\Database\Model;

class Category extends Model
{
    protected $table = 'categories';
    
    protected $fillable = ['name', 'slug', 'description', 'color', 'order', 'is_active'];
    
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];
    
    // Relacionamentos
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    public function publishedPosts()
    {
        return $this->hasMany(Post::class)->published();
    }
    
    // Escopos
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
    
    // Métodos
    public function getPostCount()
    {
        return $this->publishedPosts()->count();
    }
}
```

### 3.3 Model `Tag.php`

```php
<?php
// app/Models/Tag.php

namespace App\Models;

use Coyote\Database\Model;

class Tag extends Model
{
    protected $table = 'tags';
    
    protected $fillable = ['name', 'slug'];
    
    // Relacionamentos
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }
    
    public function publishedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_tag')->published();
    }
    
    // Métodos
    public function getPostCount()
    {
        return $this->publishedPosts()->count();
    }
    
    public static function createFromArray(array $tags)
    {
        $tagIds = [];
        
        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;
            
            $slug = str_slug($tagName);
            
            $tag = static::firstOrCreate(
                ['slug' => $slug],
                ['name' => $tagName]
            );
            
            $tagIds[] = $tag->id;
        }
        
        return $tagIds;
    }
}
```

### 3.4 Model `Comment.php`

```php
<?php
// app/Models/Comment.php

namespace App\Models;

use Coyote\Database\Model;

class Comment extends Model
{
    protected $table = 'comments';
    
    protected $fillable = [
        'post_id', 'user_id', 'parent_id', 'author_name',
        'author_email', 'content', 'status'
    ];
    
    protected $casts = [
        'user_id' => 'integer',
        'parent_id' => 'integer',
    ];
    
    // Relacionamentos
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
    
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
    
    // Escopos
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    // Métodos
    public function approve()
    {
        $this->status = 'approved';
        return $this->save();
    }
    
    public function markAsSpam()
    {
        $this->status = 'spam';
        return $this->save();
    }
    
    public function isReply()
    {
        return !is_null($this->parent_id);
    }
    
    public function getAuthorName()
    {
        return $this->user ? $this->user->name : $this->author_name;
    }
    
    public function getAuthorEmail()
    {
        return $this->user ? $this->user->email : $this->author_email;
    }
}
```

## 🎮 Passo 4: Controllers

### 4.1 Controller Público `BlogController.php`

```php
<?php
// app/Controllers/BlogController.php

namespace App\Controllers;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Request;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

class BlogController extends Controller
{
    // Página inicial do blog
    public function index(Request $request)
    {
        $query = Post::published()->with(['user', 'category', 'tags']);
        
        // Filtro por categoria
        if ($request->has('category')) {
            $category = Category::where('slug', $request->get('category'))->firstOrFail();
            $query->byCategory($category->id);
        }
        
        // Filtro por tag
        if ($request->has('tag')) {
            $tag = Tag::where('slug', $request->get('tag'))->firstOrFail();
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('tags.id', $tag->id);
            });
        }
        
        // Busca
        if ($request->has('search')) {
            $query->search($request->get('search'));
        }
        
        // Ordenação
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('published_at', 'asc');
                break;
            case 'popular':
                $query->orderBy('views', 'desc');
                break;
            default:
                $query->orderBy('published_at', 'desc');
        }
        
