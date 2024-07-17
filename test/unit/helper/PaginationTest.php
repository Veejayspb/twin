<?php

namespace test\unit\helper;

use test\helper\BaseTestCase;
use twin\criteria\Criteria;
use twin\db\Database;
use twin\helper\Pagination;

final class PaginationTest extends BaseTestCase
{
    public function testConstruct()
    {
        $pagination = new Pagination(99, 3, 22);
        $this->assertSame(99, $pagination->total);
        $this->assertSame(3, $pagination->page);
        $this->assertSame(22, $pagination->size);

        $pagination = new Pagination(-1, 0);
        $this->assertSame(0, $pagination->total);
        $this->assertSame(1, $pagination->page);
        $this->assertSame(Pagination::DEFAULT_SIZE, $pagination->size);
    }

    public function testGet()
    {
        $pagination = new Pagination(99, 2, 10);

        $this->assertSame(10, $pagination->offset);
        $this->assertSame(10, $pagination->amount);
        $this->assertSame(11, $pagination->from);
        $this->assertSame(20, $pagination->to);
    }

    public function testSet()
    {
        $pagination = new Pagination(99, 2, 10);

        $pagination->total = 77;
        $this->assertSame(77, $pagination->total);

        $pagination->page = 12;
        $this->assertSame(12, $pagination->page);

        $pagination->size = 5;
        $this->assertSame(5, $pagination->size);

        $pagination->{'offset'} = 7;
        $this->assertNotSame(7, $pagination->offset);

        $pagination->{'amount'} = 9;
        $this->assertNotSame(9, $pagination->amount);

        $pagination->{'from'} = 1;
        $this->assertNotSame(1, $pagination->from);

        $pagination->{'to'} = 23;
        $this->assertNotSame(23, $pagination->to);
    }

    public function testApply()
    {
        $criteria = $this->getCriteria();
        $pagination = new Pagination(99, 3, 22);
        $pagination->apply($criteria);

        $this->assertSame($pagination->offset, $criteria->offset);
        $this->assertSame($pagination->size, $criteria->limit);
    }

    public function testWidget()
    {
        $pagination = new Pagination(11, 1, 10);
        $html = $pagination->widget([]);

        $this->assertStringContains($html, '1');
        $this->assertStringContains($html, '2');
        $this->assertStringNotContains($html, '3');
    }

    /**
     * @return Criteria
     */
    private function getCriteria(): Criteria
    {
        return new class extends Criteria
        {
            public function query(Database $db): array
            {
                return [];
            }
        };
    }
}
