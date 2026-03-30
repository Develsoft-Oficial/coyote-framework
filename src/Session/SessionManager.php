<?php

namespace Coyote\Session;

use Coyote\Config\Repository as ConfigRepository;
use InvalidArgumentException;
use RuntimeException;

/**
 * SessionManager
 * 
 * Gerenciador de sessões do Coyote Framework.
 * Suporta múltiplos drivers (file, database, array) e configuração flexível.
 */
class SessionManager implements Store, SessionInterface
{
    /**
     * A configuração da sessão.
     *
     * @var \Coyote\Config\Repository
     */
    protected $config;

    /**
     * O driver de sessão atual.
     *
     * @var \Coyote\Session\Store
     */
    protected $driver;

    /**
     * Os drivers de sessão registrados.
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * Indica se a sessão foi iniciada.
     *
     * @var bool
     */
    protected $started = false;

    /**
     * Dados flash para a próxima requisição.
     *
     * @var array
     */
    protected $flash = [];

    /**
     * Dados flash antigos (para reflash).
     *
     * @var array
     */
    protected $oldFlash = [];

    /**
     * Cria uma nova instância do gerenciador de sessões.
     *
     * @param \Coyote\Config\Repository $config
     * @return void
     */
    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
        $this->setupFlashData();
    }

    /**
     * Inicia a sessão.
     *
     * @return bool
     */
    public function start(): bool
    {
        if ($this->started) {
            return true;
        }

        $this->loadDriver();

        // Configurar opções da sessão
        $this->configureSession();

        // Iniciar a sessão
        if (!$this->driver->start()) {
            throw new RuntimeException('Não foi possível iniciar a sessão.');
        }

        $this->started = true;

        // Carregar dados flash da requisição anterior
        $this->loadFlashData();

        return true;
    }

    /**
     * Salva a sessão.
     *
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->started) {
            return false;
        }

        // Preparar dados flash para a próxima requisição
        $this->prepareFlashData();

        return $this->driver->save();
    }

    /**
     * Destrói a sessão.
     *
     * @return bool
     */
    public function destroy(): bool
    {
        $this->started = false;
        $this->flash = [];
        $this->oldFlash = [];

        return $this->driver->destroy();
    }

    /**
     * Regenera o ID da sessão.
     *
     * @param bool $deleteOldSession
     * @return bool
     */
    public function regenerate(bool $deleteOldSession = true): bool
    {
        return $this->driver->regenerate($deleteOldSession);
    }

    /**
     * Obtém um valor da sessão.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $this->ensureStarted();

        // Verificar primeiro nos dados flash
        if (array_key_exists($key, $this->flash)) {
            return $this->flash[$key];
        }

        return $this->driver->get($key, $default);
    }

    /**
     * Armazena um valor na sessão.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function put(string $key, $value): void
    {
        $this->ensureStarted();
        $this->driver->put($key, $value);
    }

    /**
     * Armazena múltiplos valores na sessão.
     *
     * @param array $values
     * @return void
     */
    public function putMany(array $values): void
    {
        $this->ensureStarted();
        
        foreach ($values as $key => $value) {
            $this->put($key, $value);
        }
    }

    /**
     * Verifica se uma chave existe na sessão.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();

        if (array_key_exists($key, $this->flash)) {
            return true;
        }

        return $this->driver->has($key);
    }

    /**
     * Remove um valor da sessão.
     *
     * @param string $key
     * @return void
     */
    public function forget(string $key): void
    {
        $this->ensureStarted();
        $this->driver->forget($key);
    }

    /**
     * Remove múltiplos valores da sessão.
     *
     * @param array $keys
     * @return void
     */
    public function forgetMany(array $keys): void
    {
        $this->ensureStarted();
        $this->driver->forgetMany($keys);
    }

    /**
     * Limpa todos os dados da sessão.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->ensureStarted();
        $this->driver->flush();
        $this->flash = [];
        $this->oldFlash = [];
    }

    /**
     * Armazena um valor flash na sessão.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function flash(string $key, $value): void
    {
        $this->ensureStarted();
        $this->flash[$key] = $value;
    }

    /**
     * Armazena múltiplos valores flash na sessão.
     *
     * @param array $values
     * @return void
     */
    public function flashMany(array $values): void
    {
        $this->ensureStarted();
        
        foreach ($values as $key => $value) {
            $this->flash($key, $value);
        }
    }

    /**
     * Mantém valores flash para a próxima requisição.
     *
     * @param array|string|null $keys
     * @return void
     */
    public function reflash($keys = null): void
    {
        $this->ensureStarted();

        if (is_null($keys)) {
            // Reflash todos os dados flash
            $this->flash = array_merge($this->flash, $this->oldFlash);
        } else {
            $keys = is_array($keys) ? $keys : func_get_args();
            
            foreach ($keys as $key) {
                if (array_key_exists($key, $this->oldFlash)) {
                    $this->flash[$key] = $this->oldFlash[$key];
                }
            }
        }
    }

    /**
     * Obtém o ID da sessão.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->driver->getId();
    }

    /**
     * Define o ID da sessão.
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->driver->setId($id);
    }

    /**
     * Obtém o nome da sessão.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->driver->getName();
    }

    /**
     * Define o nome da sessão.
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->driver->setName($name);
    }

    /**
     * Verifica se a sessão foi iniciada.
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Obtém todos os dados da sessão.
     *
     * @return array
     */
    public function all(): array
    {
        $this->ensureStarted();
        
        $data = $this->driver->all();
        return array_merge($data, $this->flash);
    }

    /**
     * Obtém e remove um valor da sessão.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function pull(string $key, $default = null)
    {
        $this->ensureStarted();

        $value = $this->get($key, $default);
        $this->forget($key);

        return $value;
    }

    /**
     * Incrementa um valor numérico na sessão.
     *
     * @param string $key
     * @param int $amount
     * @return int
     */
    public function increment(string $key, int $amount = 1): int
    {
        $this->ensureStarted();
        return $this->driver->increment($key, $amount);
    }

    /**
     * Decrementa um valor numérico na sessão.
     *
     * @param string $key
     * @param int $amount
     * @return int
     */
    public function decrement(string $key, int $amount = 1): int
    {
        $this->ensureStarted();
        return $this->driver->decrement($key, $amount);
    }

    /**
     * Obtém o driver de sessão atual.
     *
     * @return \Coyote\Session\Store
     */
    public function getDriver(): Store
    {
        return $this->driver;
    }

    /**
     * Define o driver de sessão.
     *
     * @param \Coyote\Session\Store $driver
     * @return void
     */
    public function setDriver(Store $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * Carrega o driver de sessão baseado na configuração.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function loadDriver(): void
    {
        if ($this->driver) {
            return;
        }

        $driver = $this->config->get('session.driver', 'file');

        if (isset($this->drivers[$driver])) {
            $this->driver = $this->drivers[$driver];
            return;
        }

        $method = 'create' . ucfirst($driver) . 'Driver';

        if (method_exists($this, $method)) {
            $this->driver = $this->{$method}();
            $this->drivers[$driver] = $this->driver;
            return;
        }

        throw new InvalidArgumentException("Driver de sessão [{$driver}] não suportado.");
    }

    /**
     * Cria o driver de sessão baseado em arquivos.
     *
     * @return \Coyote\Session\Store
     */
    protected function createFileDriver(): Store
    {
        $path = $this->config->get('session.files', __DIR__ . '/../../../storage/framework/sessions');
        $lifetime = $this->config->get('session.lifetime', 120);
        
        return new FileSessionHandler($path, $lifetime);
    }

    /**
     * Cria o driver de sessão baseado em banco de dados.
     *
     * @return \Coyote\Session\Store
     */
    protected function createDatabaseDriver(): Store
    {
        $connection = $this->config->get('session.connection');
        $table = $this->config->get('session.table', 'sessions');
        $lifetime = $this->config->get('session.lifetime', 120);
        
        return new DatabaseSessionHandler($connection, $table, $lifetime);
    }

    /**
     * Cria o driver de sessão baseado em array (para testes).
     *
     * @return \Coyote\Session\Store
     */
    protected function createArrayDriver(): Store
    {
        return new class implements Store {
            private $data = [];
            private $id;
            private $name = 'coyote_session';
            private $started = false;

            public function start(): bool { 
                $this->started = true; 
                $this->id = uniqid('array_', true);
                return true; 
            }
            public function save(): bool { return true; }
            public function destroy(): bool { 
                $this->data = []; 
                $this->started = false;
                return true; 
            }
            public function regenerate(bool $deleteOldSession = true): bool { 
                $this->id = uniqid('array_', true); 
                return true; 
            }
            public function get(string $key, $default = null) { 
                return $this->data[$key] ?? $default; 
            }
            public function put(string $key, $value): void { 
                $this->data[$key] = $value; 
            }
            public function putMany(array $values): void { 
                $this->data = array_merge($this->data, $values); 
            }
            public function has(string $key): bool { 
                return array_key_exists($key, $this->data); 
            }
            public function forget(string $key): void { 
                unset($this->data[$key]); 
            }
            public function forgetMany(array $keys): void { 
                foreach ($keys as $key) { 
                    unset($this->data[$key]); 
                } 
            }
            public function flush(): void { 
                $this->data = []; 
            }
            public function flash(string $key, $value): void { 
                $this->data['_flash'][$key] = $value; 
            }
            public function flashMany(array $values): void { 
                foreach ($values as $key => $value) { 
                    $this->flash($key, $value); 
                } 
            }
            public function reflash($keys = null): void { 
                // Implementação simplificada
            }
            public function getId(): string { 
                return $this->id; 
            }
            public function setId(string $id): void { 
                $this->id = $id; 
            }
            public function getName(): string { 
                return $this->name; 
            }
            public function setName(string $name): void { 
                $this->name = $name; 
            }
            public function isStarted(): bool { 
                return $this->started; 
            }
            public function all(): array { 
                return $this->data; 
            }
            public function pull(string $key, $default = null) { 
                $value = $this->get($key, $default); 
                $this->forget($key); 
                return $value; 
            }
            public function increment(string $key, int $amount = 1): int { 
                $value = ($this->get($key, 0) + $amount); 
                $this->put($key, $value); 
                return $value; 
            }
            public function decrement(string $key, int $amount = 1): int { 
                $value = ($this->get($key, 0) - $amount); 
                $this->put($key, $value); 
                return $value; 
            }
        };
    }

    /**
     * Configura as opções da sessão.
     *
     * @return void
     */
    protected function configureSession(): void
    {
        $name = $this->config->get('session.cookie', 'coyote_session');
        $lifetime = $this->config->get('session.lifetime', 120) * 60;
        $path = $this->config->get('session.path', '/');
        $domain = $this->config->get('session.domain');
        $secure = $this->config->get('session.secure', false);
        $httpOnly = $this->config->get('session.http_only', true);
        $sameSite = $this->config->get('session.same_site', 'lax');

        // Configurar cookie da sessão
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ]);

        // Definir nome da sessão
        session_name($name);
    }

    /**
     * Configura os dados flash.
     *
     * @return void
     */
    protected function setupFlashData(): void
    {
        $this->flash = [];
        $this->oldFlash = [];
    }

    /**
     * Carrega dados flash da requisição anterior.
     *
     * @return void
     */
    protected function loadFlashData(): void
    {
        $flashData = $this->driver->get('_flash', []);
        
        if (isset($flashData['new']) && isset($flashData['old'])) {
            $this->flash = $flashData['new'];
            $this->oldFlash = $flashData['old'];
            
            // Remover dados flash antigos
            $this->driver->put('_flash', [
                'new' => [],
                'old' => $this->flash,
            ]);
        }
    }

    /**
     * Prepara dados flash para a próxima requisição.
     *
     * @return void
     */
    protected function prepareFlashData(): void
    {
        $this->driver->put('_flash', [
            'new' => $this->flash,
            'old' => $this->oldFlash,
        ]);
    }

    /**
     * Garante que a sessão foi iniciada.
     *
     * @return void
     * @throws \RuntimeException
     */
    protected function ensureStarted(): void
    {
        if (!$this->started) {
            throw new RuntimeException('A sessão não foi iniciada.');
        }
    }
}