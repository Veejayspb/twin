<?php

namespace twin\helper;

use twin\Twin;

/**
 * Сборщик конфига приложения.
 * Подключает данные из родительского конфига, указанного в секции parent.
 *
 * $config = new ConfigConstructor([ config file data ]);
 * $data = $config->getData(true);
 *
 * Class ConfigConstructor
 */
class ConfigConstructor
{
    /**
     * Данные конфига.
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Данные конфига.
     * @param bool $parent - включить родительский конфиг
     * @return array
     */
    public function getData(bool $parent = false): array
    {
        if ($parent && $parentConfig = $this->getParent()) {
            return ArrayHelper::merge(
                $this->getData(),
                $parentConfig->getData(true)
            );
        }

        $data = $this->data;

        if (array_key_exists('parent', $data)) {
            unset($data['parent']);
        }

        return $data;
    }

    /**
     * Вернуть объект с родительским конфигом.
     * @return static|null
     */
    public function getParent(): ?self
    {
        if (!array_key_exists('parent', $this->data) || !is_string($this->data['parent'])) {
            return null;
        }

        $data = $this->getDataFromFile($this->data['parent']);

        if ($data === null) {
            return null;
        }

        return new static($data);
    }

    /**
     * Вернуть массив данных из файла.
     * @param string $alias
     * @return array|null
     */
    protected function getDataFromFile(string $alias): ?array
    {
        $path = Alias::get($alias);

        if (!is_file($path)) {
            return null;
        }

        $data = Twin::import($path);

        if (!is_array($data)) {
            return null;
        }

        return $data;
    }
}
