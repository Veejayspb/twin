<?php

namespace twin\migration;

use DateTime;
use twin\common\Exception;
use twin\db\Database;
use twin\helper\template\Template;
use twin\Twin;

abstract class Migration
{
    /**
     * Префикс названия класса-миграции.
     */
    const PREFIX = 'm_';

    /**
     * Формат даты/времени.
     */
    const DATE_FORMAT = 'ymd_His';

    /**
     * Паттерн названия миграции.
     */
    const PATTERN_NAME = '[a-z0-9_]+';

    /**
     * Паттерн названия класса миграции.
     */
    const PATTERN_CLASS = '/^' . self::PREFIX . '([0-9]{6}_[0-9]{6})_(' . self::PATTERN_NAME . ')$/';

    /**
     * Компонент с менеджером миграций.
     * @var MigrationManager
     */
    protected $manager;

    /**
     * @param MigrationManager $manager
     * @throws Exception
     */
    public function __construct(MigrationManager $manager)
    {
        $this->manager = $manager;
        $class = $this->getClass();

        if (!preg_match(self::PATTERN_CLASS, $class, $matches)) {
            throw new Exception(500, "Wrong migration name: $class");
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return bool
     * @throws Exception
     * @todo: use transaction or additional check after each step
     */
    public function __call(string $name, array $arguments)
    {
        switch ($name) {
            case 'up':
                if ($this->isApplied()) {
                    return true;
                }

                return $this->up() && $this->save();
            case 'down':
                if (!$this->isApplied()) {
                    return true;
                }

                return $this->down() && $this->delete();
            default:
                throw new Exception(500, "Call to unknown method: $name");
        }
    }

    /**
     * Название вызванного класса-миграции.
     * @return string
     */
    public function getClass(): string
    {
        return get_called_class();
    }

    /**
     * Название миграции.
     * m_000000_000000_{NAME}
     * @return string
     */
    public function getName(): string
    {
        preg_match(self::PATTERN_CLASS, $this->getClass(), $matches);
        return $matches[2] ?: '';
    }

    /**
     * Дата создания файла миграции.
     * m_{000000_000000}_name
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        preg_match(self::PATTERN_CLASS, $this->getClass(), $matches);

        if (isset($matches[1])) {
            return DateTime::createFromFormat(self::DATE_FORMAT, $matches[1]);
        } else {
            return new DateTime;
        }
    }

    /**
     * Компонент с менеджером миграций.
     * @return MigrationManager
     */
    public function getManager(): MigrationManager
    {
        return $this->manager;
    }

    /**
     * Применена ли миграция.
     * @return bool
     */
    public function isApplied(): bool
    {
        return $this->getDb()->isMigrationApplied($this);
    }

    /**
     * Вернуть уникальный хэш текущей миграции (первичный ключ).
     * @return string
     */
    public function getHash(): string
    {
        $timestamp = $this->getDate()->getTimestamp();
        $name = $this->getName();
        return md5($name . $timestamp);
    }

    /**
     * Создать новую миграцию.
     * @param string $path - путь до директории для сохранения
     * @param string $component - название компонента с БД
     * @param string $name - название миграции
     * @return bool
     */
    public static function create(string $path, string $component, string $name): bool
    {
        $pattern = '/^' . static::PATTERN_NAME . '$/';

        if (false === preg_match($pattern, $name)) {
            return false;
        }

        $class = static::createName($name);
        $template = new Template('@twin/helper/template/tpl/migration.tpl');
        $path.= DIRECTORY_SEPARATOR . $class . '.php';

        return $template->save($path, [
            'class' => $class,
            'component' => $component,
        ]);
    }

    /**
     * Название компонента для работы с БД.
     * @return string
     */
    abstract public function getComponent(): string;

    /**
     * Вернуть компонент БД для хранения миграций.
     * @return Database|null
     */
    protected function getDb(): ?Database
    {
        $name = $this->getComponent();
        $component = Twin::app()->{$name}; /* @var Database $component */
        return $component ?: null;
    }

    /**
     * Сохранить текущую миграцию в БД.
     * @return bool
     */
    protected function save(): bool
    {
        return $this->getDb()->addMigration($this);
    }

    /**
     * Удалить миграцию из БД.
     * @return bool
     */
    protected function delete(): bool
    {
        return $this->getDb()->deleteMigration($this);
    }

    /**
     * Создать название файла по названию миграции.
     * @param string $name - название
     * @return string
     */
    protected static function createName(string $name): string
    {
        $date_time = date(self::DATE_FORMAT);
        return self::PREFIX . "{$date_time}_{$name}";
    }

    /**
     * Применить текущую миграцию.
     * @return bool
     */
    abstract protected function up(): bool;

    /**
     * Отменить текущую миграцию.
     * @return bool
     */
    abstract protected function down(): bool;
}
