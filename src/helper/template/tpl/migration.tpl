<?php

use twin\db\Database;
use twin\migration\Migration;
use twin\Twin;

class {{class}} extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(): bool
    {
        return true; // TODO: Implement up() method.
    }

    /**
     * {@inheritdoc}
     */
    public function down(): bool
    {
        return true; // TODO: Implement down() method.
    }

    /**
     * {@inheritdoc}
     */
    protected function getDb(): ?Database
    {
        return Twin::app()->db; // TODO: Return real db component.
    }
}
