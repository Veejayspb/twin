<?php

namespace twin\migration;

use twin\template\Template;
use twin\Twin;

/**
 * Class Migration
 * @package twin\migration
 *
 * @property string $name
 * @property int $timestamp
 * @property string $class
 */
abstract class Migration
{
    /**
     * Паттерн названия миграции.
     */
    const NAME_PATTERN = '[a-z]+(?:[a-z_]+[a-z]+)?';

    /**
     * Название миграции.
     * @var string
     */
    protected $name;

    /**
     * Дата создания миграции.
     * @var int
     */
    protected $timestamp;

    public function __construct()
    {
        $class = get_called_class();
        $pattern = '/^m_([0-9]+)_(' . static::NAME_PATTERN . ')$/';
        if (preg_match($pattern, $class, $matches)) {
            $this->name = $matches[2];
            $this->timestamp = (int)$matches[1];
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($name == 'class') {
            return $this->getClass();
        } else {
            return $this->$name;
        }
    }

    /**
     * Создать новую миграцию.
     * @param string $path - путь до директории для сохранения
     * @param string $name - название
     * @return bool
     */
    public static function create(string $path, string $name): bool
    {
        $pattern = '/^' . static::NAME_PATTERN . '$/';
        if (!preg_match($pattern, $name)) return false;
        $class = static::createName($name);

        $templatePath = Twin::getAlias('@twin/template/tpl/migration.tpl');
        $template = new Template($templatePath);

        $path = $path . DIRECTORY_SEPARATOR . $class . '.php';
        return $template->save($path, ['class' => $class]);
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
     * Создать название файла по названию миграции.
     * @param string $name - название
     * @return string
     */
    protected static function createName(string $name): string
    {
        $result = 'm_' . time();
        if (!empty($name)) {
            $result.= '_' . $name;
        }
        return $result;
    }

    /**
     * Полное название миграции.
     * @return string
     */
    protected function getClass(): string
    {
        return get_called_class();
    }
}
