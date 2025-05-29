<?php

namespace test\helper;

final class Twin extends \twin\Twin
{
    /**
     * Указать в кач-ве объека приложения текущий класс.
     * @return void
     */
    public static function resetSingletone(): void
    {
        parent::$instance = new self;
    }
}
