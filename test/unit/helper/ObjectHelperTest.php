<?php

namespace twin\test\unit\helper;

use twin\helper\ObjectHelper;
use twin\test\helper\BaseTestCase;

final class ObjectHelperTest extends BaseTestCase
{
    public function testSetProperties()
    {
        $object = new class {
            public $a;
            public $b;
            public static $c;
        };

        ObjectHelper::setProperties($object, ['b' => 1, 'c' => 2, 'd' => 3]);

        $this->assertNull($object->a);
        $this->assertSame(1, $object->b);
        $this->assertNull($object::$c);
        $this->assertFalse(property_exists($object, 'd'));
    }
}
