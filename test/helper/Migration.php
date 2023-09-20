<?php

namespace twin\test\helper;

use twin\db\Database;

final class Migration extends \twin\migration\Migration
{
    /**
     * Возвращаемые значения методов.
     * @var array
     */
    public static $_returnValues = [];

    /**
     * {@inheritdoc}
     */
    public function getClass(): string
    {
        return self::$_returnValues[__FUNCTION__] ?? self::PREFIX . '230920_191600_name';
    }

    /**
     * {@inheritdoc}
     */
    public function isApplied(): bool
    {
        return self::$_returnValues[__FUNCTION__] ?? parent::isApplied();
    }

    /**
     * {@inheritdoc}
     */
    public function getComponent(): string
    {
        return self::$_returnValues[__FUNCTION__] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDb(): ?Database
    {
        return self::$_returnValues[__FUNCTION__] ?? new \twin\test\helper\Database(['dbname' => 'test']);
    }

    /**
     * {@inheritdoc}
     */
    protected function up(): bool
    {
        return self::$_returnValues[__FUNCTION__] ?? true;
    }

    /**
     * {@inheritdoc}
     */
    protected function down(): bool
    {
        return self::$_returnValues[__FUNCTION__] ?? true;
    }
}
