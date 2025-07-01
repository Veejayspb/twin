<?php

namespace twin\migration;

use DateTime;
use ReflectionClass;
use twin\common\Exception;
use twin\db\Database;
use twin\helper\Template;

abstract class Migration
{
    /**
     * Префикс названия класса.
     */
    const PREFIX = 'm_';

    /**
     * Паттерн названия миграции.
     */
    const PATTERN_NAME = '[a-z0-9_]+';

    /**
     * Паттерн названия класса.
     */
    const PATTERN_CLASS = '/^' . self::PREFIX . '([0-9]{6}_[0-9]{6})_(' . self::PATTERN_NAME . ')$/';

    /**
     * Формат даты/времени.
     */
    const DATE_FORMAT = 'ymd_His';

    /**
     * Алиас шаблона для класса.
     */
    const TEMPLATE_ALIAS = '@twin/helper/template/migration.tpl';

    /**
     * Компонент с менеджером миграций.
     * @var MigrationManager
     */
    protected MigrationManager $manager;

    /**
     * @param MigrationManager $manager
     * @throws Exception
     */
    public function __construct(MigrationManager $manager)
    {
        $this->manager = $manager;
        $isValidClass = $this->isValidClass();

        if (!$isValidClass) {
            $class = $this->getClass();
            throw new Exception(500, "Wrong migration name: $class");
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
     * Название вызванного класса.
     * @return string
     */
    public function getClass(): string
    {
        $reflection = new ReflectionClass($this);
        return $reflection->getShortName();
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
     * Вернуть уникальный хэш текущей миграции (первичный ключ).
     * @return string
     */
    public function getHash(): string
    {
        $class = $this->getClass();
        return md5($class);
    }

    /**
     * Применена ли миграция.
     * @return bool
     */
    public function isApplied(): bool
    {
        $db = $this->getDb();

        if ($db === null) {
            return false;
        }

        $row = $db->findByAttributes($this->manager->table, [
            'hash' => $this->getHash(),
        ]);

        return $row !== null;
    }

    /**
     * Добавить запись о миграции в БД.
     * @return bool
     */
    public function apply(): bool
    {
        $db = $this->getDb();

        if ($db === null) {
            return false;
        }

        $result = $db->insert($this->manager->table, [
            'hash' => $this->getHash(),
            'name' => $this->getClass(),
            'timestamp' => time(),
        ]);

        return $result !== null;
    }

    /**
     * Удалить запись о миграции из БД.
     * @return bool
     */
    public function cancel(): bool
    {
        $db = $this->getDb();

        if ($db === null) {
            return false;
        }

        return $db->delete($this->manager->table, [
            'hash' => $this->getHash(),
        ]);
    }

    /**
     * Создать новую миграцию.
     * @param string $alias - алиас директории для сохранения
     * @param string $name - название миграции
     * @return bool
     */
    public static function create(string $alias, string $name): bool
    {
        $pattern = '/^' . static::PATTERN_NAME . '$/';

        if (!preg_match($pattern, $name)) {
            return false;
        }

        $class = static::createName($name);
        $template = new Template(self::TEMPLATE_ALIAS);
        $alias.= "/$class.php";

        return $template->save($alias, [
            'class' => $class,
        ]);
    }

    /**
     * Применить текущую миграцию.
     * @return bool
     */
    abstract public function up(): bool;

    /**
     * Отменить текущую миграцию.
     * @return bool
     */
    abstract public function down(): bool;

    /**
     * Валидация названия текущего класса.
     * @return bool
     */
    protected function isValidClass(): bool
    {
        $class = $this->getClass();
        return preg_match(self::PATTERN_CLASS, $class);
    }

    /**
     * Создать название класса по названию миграции.
     * @param string $name - название
     * @return string
     */
    protected static function createName(string $name): string
    {
        $dateTime = date(self::DATE_FORMAT);
        return self::PREFIX . $dateTime . '_' . $name;
    }

    /**
     * Вернуть компонент БД для хранения миграций.
     * @return Database|null
     */
    abstract protected function getDb(): ?Database;
}
