<?php

namespace twin\helper;

use twin\common\Component;
use twin\Twin;

/**
 * Сборщик конфига приложения.
 * Подключает данные из родительского конфига, указанного в секции parent, а также данные из системных конфигов.
 *
 * $data = Twin::import('path/to/config/file.php');
 * $config = (new ConfigConstructor($data))
 *     ->registerDefault(ConfigConstructor::WEB)
 *     ->run();
 *
 * Class ConfigConstructor
 */
class ConfigConstructor
{
    const WEB = 'web';
    const CONSOLE = 'console';

    /**
     * Данные конфига.
     * @var array
     */
    protected $data = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $this->prepare($data);
    }

    /**
     * Подключить системный конфиг.
     * @param string $type - тип конфига web|console
     * @return static
     * @todo: Избавиться от данного метода. Вместо него использовать parent со ссылкой на системный конфиг.
     */
    public function registerDefault(string $type): self
    {
        $data = $this->getDefaultConfig($type);

        $this->data = ArrayHelper::merge(
            $this->prepare($data),
            $this->data
        );

        return $this;
    }

    /**
     * Вернуть результирующий массив данных.
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Вернуть объект с указанным компонентом.
     * @param string $name - название компонента
     * @return Component|null
     */
    public function getComponent(string $name)
    {
        $data = $this->data['components'] ?? [];

        if (!array_key_exists($name, $data)) {
            return null;
        }

        $component = $this->createComponent($data[$name]);

        if (!$component) {
            return null;
        }

        return $component;
    }

    /**
     * Вернуть объекты с компонентами.
     * @return Component[]
     */
    public function getComponents(): array
    {
        $data = $this->data['components'] ?? [];
        $result = [];

        foreach ((array)$data as $name => $properties) {
            $component = $this->createComponent($properties);
            if (!$component) continue;
            $result[$name] = $component;
        }

        return $result;
    }

    /**
     * Создать объект с компонентом на основе переданных свойств.
     * @param array $properties - свойства компонента
     * @return Component|bool - FALSE в случае невозможности создать компонент
     */
    protected function createComponent(array $properties)
    {
        $class = $properties['class'] ?? null;

        if (
            $class === null ||
            !class_exists($class) ||
            !is_subclass_of($class, Component::class)
        ) {
            return false;
        }

        return new $class($properties);
    }

    /**
     * Подключение родительского конфига из секции parent.
     * @param array $data
     * @return array
     */
    protected function prepare(array $data): array
    {
        if (!array_key_exists('parent', $data)) {
            return $data;
        }

        $parentData = $this->getConfigByAlias($data['parent']);
        unset($data['parent']);

        $parentData = $this->prepare($parentData);
        return ArrayHelper::merge($parentData, $data);
    }

    /**
     * Вернуть системный конфиг.
     * @param string $type - тип конфига web|console
     * @return array
     */
    protected function getDefaultConfig(string $type): array
    {
        if (!in_array($type, [self::WEB, self::CONSOLE])) {
            return [];
        }

        $alias = "@twin/config/$type.php";
        return $this->getConfigByAlias($alias);
    }

    /**
     * Вернуть конфиг по алиасу пути до файла конфига.
     * @param string $alias - алиас пути
     * @return array
     */
    protected function getConfigByAlias(string $alias): array
    {
        return Twin::import($alias) ?: [];
    }
}
