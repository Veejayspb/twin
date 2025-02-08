<?php

namespace twin\model;

abstract class Form extends Entity
{
    /**
     * Ярлыки атрибутов.
     * @return array
     */
    public function labels(): array
    {
        return [];
    }

    /**
     * Ярлык атрибута.
     * @param string $attribute - название атрибута
     * @return string
     */
    public function getLabel(string $attribute): string
    {
        $labels = $this->labels();
        return $labels[$attribute] ?? $attribute;
    }
}
