<?php

use twin\db\sql\Mysql;
use test\helper\BaseTestCase;

final class MysqlTest extends BaseTestCase
{
    public function testGetTables()
    {
        $db = $this->mock(Mysql::class, null, null, ['query' => [
            ['Tables_in_db' => 'user'],
            ['Tables_in_db' => 'article'],
        ]]); /* @var Mysql $db */

        $this->assertSame(
            ['user', 'article'],
            $db->getTables()
        );

        $db = $this->mock(Mysql::class, null, null, ['query' => false]); /* @var Mysql $db */

        $this->assertFalse($db->getTables());
    }

    public function testGetPk()
    {
        $db = $this->mock(Mysql::class, null, null, ['query' => [
            ['Column_name' => 'id'],
        ]]); /* @var Mysql $db */

        $this->assertSame(['id'], $db->getPk('tbl'));

        $db = $this->mock(Mysql::class, null, null, ['query' => false]); /* @var Mysql $db */

        $this->assertSame([], $db->getPk('tbl'));
    }

    public function testGetAutoincrement()
    {
        $db = $this->mock(Mysql::class, null, null, ['query' => [
            ['Field' => 'id', 'Extra' => 'auto_increment'],
            ['Field' => 'name', 'Extra' => ''],
        ]]); /* @var Mysql $db */

        $this->assertSame('id', $db->getAutoIncrement('tbl'));

        $db = $this->mock(Mysql::class, null, null, ['query' => false]); /* @var Mysql $db */

        $this->assertSame(false, $db->getAutoIncrement('tbl'));
    }

    public function testTransactionBegin()
    {
        $db = $this->mock(Mysql::class, null, null, ['execute' => false]); /* @var Mysql $db */
        $this->assertFalse($db->transactionBegin());

        $db = $this->mock(Mysql::class, null, null, ['execute' => true]); /* @var Mysql $db */
        $this->assertTrue($db->transactionBegin());
    }
}
