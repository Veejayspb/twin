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
        if ($savePath !== false && $this->createPath()) {
            session_save_path($savePath);
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
     * Вернуть путь до директории для сохранения файлов с данными сессии.
     * @return string|bool - FALSE, если путь не указан
     */
    protected function getSavePath()
    {
        if ($this->savePath === null) return false;
        return Alias::get($this->savePath);
    }

    /**
     * Начать сессию.
     * @return bool
     */
    public function open()
    {
        return @session_start();
    }

    /**
     * Завершить сессию.
     * @return void
     */
    public function close()
    {
        @session_write_close();
    }

    /**
     * Очистить сессию.
     * @return void
     */
    public function destroy()
    {
        if(session_id() !== '') {
            @session_unset();
            @session_destroy();
        }
    }

    /**
     * Вернуть ID текущей сессии.
     * @return string
     */
    public function getId(): string
    {
        return @session_id();
    }

    /**
     * Установить ID текущей сессии.
     * @param string $value - значение
     * @return void
     */
    public function setId(string $value)
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
    public function set(string $name, $value)
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
        return array_key_exists($name, $_SESSION) ? $_SESSION[$name] : $default;
    }

    /**
     * Удалить параметр из сессии.
     * @param string $name - название параметра
     * @return void
     */
    public function delete(string $name)
    {
        $name = $this->getName($name);
        if (array_key_exists($name, $_SESSION)) {
            unset($_SESSION[$name]);
        }
    }

    /**
     * Создать директорию для хранения файлов сессий.
     * @return bool
     */
    private function createPath(): bool
    {
        $savePath = $this->getSavePath();
        if (!is_dir($savePath)) {
            return mkdir($savePath, 0775, true);
        }
        return true;
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
