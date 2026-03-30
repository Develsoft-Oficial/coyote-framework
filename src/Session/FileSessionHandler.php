<?php

namespace Coyote\Session;

use RuntimeException;
use SessionHandlerInterface;

/**
 * FileSessionHandler
 * 
 * Manipulador de sessões baseado em arquivos para o Coyote Framework.
 * Armazena dados de sessão em arquivos no sistema de arquivos.
 */
class FileSessionHandler implements Store, SessionHandlerInterface
{
    /**
     * O caminho onde os arquivos de sessão serão armazenados.
     *
     * @var string
     */
    protected $path;

    /**
     * O tempo de vida da sessão em minutos.
     *
     * @var int
     */
    protected $lifetime;

    /**
     * Os dados da sessão.
     *
     * @var array
     */
    protected $data = [];

    /**
     * O ID da sessão.
     *
     * @var string
     */
    protected $id;

    /**
     * O nome da sessão.
     *
     * @var string
     */
    protected $name = 'coyote_session';

    /**
     * Indica se a sessão foi iniciada.
     *
     * @var bool
     */
    protected $started = false;

    /**
     * Cria uma nova instância do manipulador de sessões baseado em arquivos.
     *
     * @param string $path O caminho onde armazenar os arquivos de sessão
     * @param int $lifetime O tempo de vida da sessão em minutos
     * @return void
     */
    public function __construct(string $path, int $lifetime = 120)
    {
        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
        $this->lifetime = $lifetime;

        // Garantir que o diretório existe
        if (!is_dir($this->path)) {
            if (!mkdir($this->path, 0755, true)) {
                throw new RuntimeException("Não foi possível criar o diretório de sessões: {$this->path}");
            }
        }

        // Verificar permissões de escrita
        if (!is_writable($this->path)) {
            // Tentar tornar gravável
            if (!chmod($this->path, 0777)) {
                throw new RuntimeException("O diretório de sessões não é gravável: {$this->path}");
            }
        }
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

        // Configurar este handler como o handler de sessão do PHP
        session_set_save_handler($this, true);

        // Iniciar a sessão
        if (!session_start()) {
            throw new RuntimeException('Não foi possível iniciar a sessão.');
        }

        $this->id = session_id();
        $this->started = true;

        // Carregar dados da sessão
        $this->data = $_SESSION;

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

        // Atualizar $_SESSION com nossos dados
        $_SESSION = $this->data;

        // Fechar a sessão
        return session_write_close();
    }

    /**
     * Destrói a sessão atual.
     *
     * @param string|null $sessionId
     * @return bool
     */
    public function destroy(?string $sessionId = null): bool
    {
        if ($sessionId !== null) {
            // Chamada do SessionHandlerInterface
            return $this->destroySession($sessionId);
        }
        
        if (!$this->started) {
            return false;
        }

        // Limpar dados
        $this->data = [];
        $_SESSION = [];

        // Destruir a sessão
        $result = session_destroy();
        
        $this->started = false;
        $this->id = null;

        return $result;
    }

    /**
     * Regenera o ID da sessão.
     *
     * @param bool $deleteOldSession
     * @return bool
     */
    public function regenerate(bool $deleteOldSession = true): bool
    {
        if (!$this->started) {
            return false;
        }

        $oldId = $this->id;
        
        // Gerar novo ID
        if (!session_regenerate_id($deleteOldSession)) {
            return false;
        }

        $this->id = session_id();

        // Se solicitado, deletar a sessão antiga
        if ($deleteOldSession && $oldId) {
            $this->deleteSessionFile($oldId);
        }

        return true;
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
        
        return $this->data[$key] ?? $default;
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
        $this->data[$key] = $value;
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
        $this->data = array_merge($this->data, $values);
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
        return array_key_exists($key, $this->data);
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
        unset($this->data[$key]);
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
        
        foreach ($keys as $key) {
            unset($this->data[$key]);
        }
    }

    /**
     * Limpa todos os dados da sessão.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->ensureStarted();
        $this->data = [];
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
        
        // Armazenar em um array especial de flash
        if (!isset($this->data['_flash'])) {
            $this->data['_flash'] = ['new' => [], 'old' => []];
        }
        
        $this->data['_flash']['new'][$key] = $value;
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
        
        if (!isset($this->data['_flash'])) {
            return;
        }
        
        if (is_null($keys)) {
            // Reflash todos
            $this->data['_flash']['new'] = array_merge(
                $this->data['_flash']['new'],
                $this->data['_flash']['old']
            );
        } else {
            $keys = is_array($keys) ? $keys : func_get_args();
            
            foreach ($keys as $key) {
                if (isset($this->data['_flash']['old'][$key])) {
                    $this->data['_flash']['new'][$key] = $this->data['_flash']['old'][$key];
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
        return $this->id ?: '';
    }

    /**
     * Define o ID da sessão.
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
        session_id($id);
    }

    /**
     * Obtém o nome da sessão.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Define o nome da sessão.
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
        session_name($name);
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
        return $this->data;
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
        
        $value = ($this->get($key, 0) + $amount);
        $this->put($key, $value);
        
        return $value;
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
        
        $value = ($this->get($key, 0) - $amount);
        $this->put($key, $value);
        
        return $value;
    }

    /**
     * Abre a sessão (implementação de SessionHandlerInterface).
     *
     * @param string $savePath
     * @param string $sessionName
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * Fecha a sessão (implementação de SessionHandlerInterface).
     *
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Lê dados da sessão (implementação de SessionHandlerInterface).
     *
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId): string
    {
        $file = $this->getSessionFile($sessionId);
        
        if (file_exists($file) && is_readable($file)) {
            // Verificar se o arquivo não expirou
            if (filemtime($file) + ($this->lifetime * 60) < time()) {
                // Sessão expirada
                unlink($file);
                return '';
            }
            
            $data = file_get_contents($file);
            return $data ?: '';
        }
        
        return '';
    }

    /**
     * Escreve dados na sessão (implementação de SessionHandlerInterface).
     *
     * @param string $sessionId
     * @param string $sessionData
     * @return bool
     */
    public function write($sessionId, $sessionData): bool
    {
        $file = $this->getSessionFile($sessionId);
        
        // Não salvar sessões vazias
        if (empty($sessionData)) {
            if (file_exists($file)) {
                unlink($file);
            }
            return true;
        }
        
        return file_put_contents($file, $sessionData, LOCK_EX) !== false;
    }

    /**
     * Destrói uma sessão específica pelo ID (implementação de SessionHandlerInterface).
     *
     * @param string $sessionId
     * @return bool
     */
    public function destroySession($sessionId): bool
    {
        $file = $this->getSessionFile($sessionId);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }

    /**
     * Limpa sessões expiradas (implementação de SessionHandlerInterface).
     *
     * @param int $maxLifetime
     * @return int|false
     */
    #[\ReturnTypeWillChange]
    public function gc($maxLifetime)
    {
        $files = glob($this->path . DIRECTORY_SEPARATOR . 'sess_*');
        $deleted = 0;
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) + $maxLifetime < $now) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }

    /**
     * Obtém o caminho completo do arquivo de sessão.
     *
     * @param string $sessionId
     * @return string
     */
    protected function getSessionFile(string $sessionId): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'sess_' . $sessionId;
    }

    /**
     * Deleta um arquivo de sessão.
     *
     * @param string $sessionId
     * @return bool
     */
    protected function deleteSessionFile(string $sessionId): bool
    {
        $file = $this->getSessionFile($sessionId);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
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