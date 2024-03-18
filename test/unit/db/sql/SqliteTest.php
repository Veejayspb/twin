<?php

use twin\db\sql\Sqlite;
use test\helper\BaseTestCase;

final class SqliteTest extends BaseTestCase
{
    public function testGetTables()
    {
        $db = $this->mock(Sqlite::class, null, null, ['query' => [
            ['name' => 'user'],
            ['name' => 'article'],
        ]]); /* @var Sqlite $db */

        $this->assertSame(
            ['user', 'article'],
            $db->getTables()
        );

        $db = $this->mock(Sqlite::class, null, null, ['query' => false]); /* @var Sqlite $db */

        $this->assertFalse($db->getTables());
    }

    public function testGetPk()
    {
        $db = $this->mock(Sqlite::class, null, null, ['query' => [
            ['pk' => '1', 'name' => 'id'],
            ['pk' => '0', 'name' => 'name'],
        ]]); /* @var Sqlite $db */

        $this->assertSame(['id'], $db->getPk('tbl'));

        $db = $this->mock(Sqlite::class, null, null, ['query' => false]); /* @var Sqlite $db */

        $this->assertSame([], $db->getPk('tbl'));
    }

    public function testGetAutoincrement()
    {
        $db = $this->mock(Sqlite::class, null, null, ['query' => [
            ['pk' => '1', 'name' => 'id', 'type' => 'integer'],
            ['pk' => '0', 'name' => 'name', 'type' => 'text'],
        ]]); /* @var Sqlite $db */

        $this->assertSame('id', $db->getAutoIncrement('tbl'));

        $db = $this->mock(Sqlite::class, null, null, ['query' => false]); /* @var Sqlite $db */

        $this->assertSame(false, $db->getAutoIncrement('tbl'));
    }

    public function testTransactionBegin()
    {
        $db = $this->mock(Sqlite::class, null, null, ['execute' => false]); /* @var Sqlite $db */
        $this->assertFalse($db->transactionBegin());

        $db = $this->mock(Sqlite::class, null, null, ['execute' => true]); /* @var Sqlite $db */
        $this->assertTrue($db->transactionBegin());
    }
}
