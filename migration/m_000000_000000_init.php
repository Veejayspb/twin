<?php

use twin\db\Database;
use twin\migration\Migration;

class m_000000_000000_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function down(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplied(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDb(): ?Database
    {
        return null;
    }
}
