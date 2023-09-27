<?php

namespace twin\test\helper;

final class Twin extends \twin\Twin
{
    /**
     * {@inheritdoc}
     */
    public static function getClassAlias(string $className): string
    {
        if (substr($className, 0, 1) == '@') {
            return $className;
        } else {
            return parent::getClassAlias($className);
        }
    }
}
