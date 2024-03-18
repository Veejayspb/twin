<?php

namespace twin\session;

use twin\common\Component;
use twin\helper\Alias;

class Session extends Component
{
    /**
     * Префикс параметров.
     */
    const NAME_PREFIX = 's_';

    /**
     * Автоматический старт сессии при запуске приложения.
     * @var bool
     */
    public $autoStart = true;

    /**
     * Время жизни куки сессии.
     * @var int
     */
    public $lifeTime = 0;

    /**
     * Путь/алиас директории для сохранения файлов с данными сессии.
     * @var string|null
     */
    public $savePath;

    /**
     * {@inheritdoc}
     */
    protected $_requiredProperties = ['autoStart', 'lifeTime'];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        $savePath = $this->getSavePath();

        if ($savePath !== null && $this->createPath()) {
            ini_set('session.save_path', $savePath);
        }

        ini_set('session.cookie_lifetime', $this->lifeTime);
        ini_set('session.gc_maxlifetime', $this->lifeTime + 60); // Через 60 сек после истечения срока жизни сессии GC удаляет файл
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);

        if ($this->autoStart) {
            $this->open();
        }
    }

    /**
     * Начать сессию.
     * @return bool
     */
    public function open(): bool
    {
        return @session_start();
    }

    /**
     * Завершить сессию.
     * @return void
     */
    public function close(): void
    {
        @session_write_close();
    }

    /**
     * Очистить сессию.
     * @return void
     */
    public function destroy(): void
    {
        if (session_id() !== '') {
            @session_unset();
            @session_destroy();
        }
    }

    /**
     * Вернуть ID текущей сессии.
     * @return string|null
     */
    public function getId(): ?string
    {
        return @session_id() ?: null;
    }

    /**
     * Установить ID текущей сессии.
     * @param string $value - значение
     * @return void
     */
    public function setId(string $value): void
    {
        @session_id($value);
    }

    /**
     * Обновить ID текущей сессии.
     * @param bool $deleteOldSession - удалить старую сессию
     * @return bool
     */
    public function regenerateId(bool $deleteOldSession = false): bool
    {
        return @session_regenerate_id($deleteOldSession);
    }

    /**
     * Сохранить параметр в сессию.
     * @param string $name - название параметра
     * @param mixed $value - значение
     * @return void
     */
    public function set(string $name, $value): void
    {
        $name = $this->getName($name);
        $_SESSION[$name] = $value;
    }

    /**
     * Вернуть параметр из сессии.
     * @param string $name - название параметра
     * @param mixed|null $default - значение по-умолчанию
     * @return mixed|null - NULL, если параметр отсутствует
     */
    public function get(string $name, $default = null)
    {
        $name = $this->getName($name);
        return $_SESSION[$name] ?? $default;
    }

    /**
     * Удалить параметр из сессии.
     * @param string $name - название параметра
     * @return void
     */
    public function delete(string $name): void
    {
        $name = $this->getName($name);

        if (array_key_exists($name, $_SESSION)) {
            unset($_SESSION[$name]);
        }
    }

    /**
     * Вернуть путь до директории для сохранения файлов с данными сессии.
     * @return string|null - NULL, если путь не указан
     */
    protected function getSavePath(): ?string
    {
        if ($this->savePath === null) {
            return null;
        }

        return Alias::get($this->savePath);
    }

    /**
     * Создать директорию для хранения файлов сессий.
     * @return bool
     */
    private function createPath(): bool
    {
        $savePath = $this->getSavePath();

        if (is_dir($savePath)) {
            return true;
        }

        return mkdir($savePath, 0775, true);
    }

    /**
     * Вернуть название параметра с префиксом.
     * @param string $name - название параметра
     * @return string
     */
    private function getName(string $name): string
    {
        return static::NAME_PREFIX . $name;
    }
}
