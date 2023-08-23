<?php

use twin\helper\ObjectHelper;
use twin\test\helper\BaseTestCase;
use twin\test\helper\ObjectProxy;

final class ObjectHelperTest extends BaseTestCase
{
    public function testSetProperties()
    {
        $object = $this->getObject();
        $proxy = new ObjectProxy($object);

        ObjectHelper::setProperties($object, [
            'public_property' => 1,
            'public_static_property' => 2,
            'not_exists' => 3,
        ]);

        $this->assertSame(1, $proxy->public_property);
        $this->assertNull($object::$public_static_property);
        $this->assertNull($proxy->protected_property);
        $this->assertFalse(property_exists($object, 'not_exists'));
    }

    public function testIsPublicProperty()
    {
        $object = $this->getObject();

        $this->assertTrue(ObjectHelper::isPublicProperty($object, 'public_property'));
        $this->assertTrue(ObjectHelper::isPublicProperty($object, 'public_static_property'));
        $this->assertFalse(ObjectHelper::isPublicProperty($object, 'protected_property'));
        $this->assertFalse(ObjectHelper::isPublicProperty($object, 'private_static_property'));
        $this->assertFalse(ObjectHelper::isPublicProperty($object, 'public_method'));
        $this->assertFalse(ObjectHelper::isPublicProperty($object, 'public_static_method'));
        $this->assertFalse(ObjectHelper::isPublicProperty($object, 'not_exists'));
        $this->assertFalse(ObjectHelper::isPublicProperty($object, ''));
    }

    /**
     * @return object
     */
    private function getObject(): object
    {
        return new class {
            public $public_property;
            protected $protected_property;
            private $private_property;

            public static $public_static_property;
            protected static $protected_static_property;
            private static $private_static_property;

            public function public_method() {}
            protected function protected_method() {}
            private function private_method() {}

            public static function public_static_method() {}
            protected static function protected_static_method() {}
            private static function private_static_method() {}
        };
    }
}
