<?php

namespace twin\migration;

use DateTime;
use twin\common\Exception;
use twin\db\Database;
use twin\helper\template\Template;
use twin\Twin;

/**
 * Class Migration
 *
 * @property-read string $name
 * @property-read DateTime $date
 * @property-read string $class
 * @property-read Database|null $db
 * @property-read MigrationManager $manager
 * @property-read string $component
 */
abstract class Migration
{
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
    const PATTERN_CLASS = '/^m_([0-9]{6}_[0-9]{6})_(' . self::PATTERN_NAME . ')$/';

    /**
     * Название миграции.
     * @var string
     */
    protected $name;

    /**
     * Дата создания миграции.
     * @var DateTime
     */
    protected $date;

    /**
     * Название компонента для работы с БД.
     * @var string
     */
    protected $component;

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
        $class = get_called_class();

        if (!preg_match(self::PATTERN_CLASS, $class, $matches)) {
            throw new Exception(500, "Wrong migration name: $class");
        }

        $this->date = DateTime::createFromFormat(self::DATE_FORMAT, $matches[1]);
        $this->name = $matches[2];
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'class':
                return get_called_class();
            case 'db':
                return $this->getDb();
            default:
                return $this->$name;
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return bool
     * @throws Exception
     * @todo: use transaction or additional check after each step
     */
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'up':
                if ($this->isApplied()) return true;
                return $this->up() && $this->save();
            case 'down':
                if (!$this->isApplied()) return true;
                return $this->down() && $this->delete();
            default:
                throw new Exception(500, "Call to unknown method: $name");
        }
    }

    /**
     * Применена ли миграция.
     * @return bool
     */
    public function isApplied(): bool
    {
        return $this->db->isMigrationApplied($this);
    }

    /**
     * Вернуть уникальный хэш текущей миграции (первичный ключ).
     * @return string
     */
    public function getHash(): string
    {
        $timestamp = $this->date->getTimestamp();
        return md5($this->name . $timestamp);
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
        if (false === preg_match($pattern, $name)) return false;

        $class = static::createName($name);
        $template = new Template('@twin/helper/template/tpl/migration.tpl');
        $path = $path . DIRECTORY_SEPARATOR . $class . '.php';

        return $template->save($path, [
            'class' => $class,
            'component' => $component,
        ]);
    }

    /**
     * Сохранить текущую миграцию в БД.
     * @return bool
     */
    protected function save(): bool
    {
        return $this->db->addMigration($this);
    }

    /**
     * Удалить миграцию из БД.
     * @return bool
     */
    protected function delete(): bool
    {
        return $this->db->deleteMigration($this);
    }

    /**
     * Создать название файла по названию миграции.
     * @param string $name - название
     * @return string
     */
    protected static function createName(string $name): string
    {
        $date_time = date(self::DATE_FORMAT);
        return "m_{$date_time}_{$name}";
    }

    /**
     * Вернуть компонент БД для хранения миграций.
     * @return Database|null
     */
    private function getDb()
    {
        $component = Twin::app()->{$this->component}; /* @var Database $component */
        return $component ?: null;
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
