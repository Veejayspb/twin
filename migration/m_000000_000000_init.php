<?php

use twin\migration\Migration;

class m_000000_000000_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    protected function up(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function down(): bool
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
}
