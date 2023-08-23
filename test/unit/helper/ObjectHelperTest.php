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

        (new ObjectHelper($object))->setProperties([
            'public_property' => 1,
            'public_static_property' => 2,
            'not_exists' => 3,
            'protected_property' => 4,
        ]);

        $this->assertSame(1, $proxy->public_property);
        $this->assertNull($object::$public_static_property);
        $this->assertNull($proxy->protected_property);
        $this->assertFalse(property_exists($object, 'not_exists'));
    }

    public function testIsPublicProperty()
    {
        $items = [
            'public_property' => true,
            'protected_property' => false,
            'private_property' => false,

            'public_static_property' => true,
            'protected_static_property' => false,
            'private_static_property' => false,

            'public_method' => false,
            'protected_method' => false,
            'private_method' => false,

            'public_static_method' => false,
            'protected_static_method' => false,
            'private_static_method' => false,

            'not_exists' => false,
            '' => false,
        ];
        
        $object = $this->getObject();

        foreach ($items as $name => $expected) {
            $actual = (new ObjectHelper($object))->isPublicProperty($name);
            $this->assertSame($expected, $actual);
        }
    }

    public function testIsProtectedProperty()
    {
        $items = [
            'public_property' => false,
            'protected_property' => true,
            'private_property' => false,

            'public_static_property' => false,
            'protected_static_property' => true,
            'private_static_property' => false,

            'public_method' => false,
            'protected_method' => false,
            'private_method' => false,

            'public_static_method' => false,
            'protected_static_method' => false,
            'private_static_method' => false,

            'not_exists' => false,
            '' => false,
        ];

        $object = $this->getObject();

        foreach ($items as $name => $expected) {
            $actual = (new ObjectHelper($object))->isProtectedProperty($name);
            $this->assertSame($expected, $actual);
        }
    }

    public function testIsPrivateProperty()
    {
        $items = [
            'public_property' => false,
            'protected_property' => false,
            'private_property' => true,

            'public_static_property' => false,
            'protected_static_property' => false,
            'private_static_property' => true,

            'public_method' => false,
            'protected_method' => false,
            'private_method' => false,

            'public_static_method' => false,
            'protected_static_method' => false,
            'private_static_method' => false,

            'not_exists' => false,
            '' => false,
        ];

        $object = $this->getObject();

        foreach ($items as $name => $expected) {
            $actual = (new ObjectHelper($object))->isPrivateProperty($name);
            $this->assertSame($expected, $actual);
        }
    }

    public function testIsStaticProperty()
    {
        $items = [
            'public_property' => false,
            'protected_property' => false,
            'private_property' => false,

            'public_static_property' => true,
            'protected_static_property' => true,
            'private_static_property' => true,

            'public_method' => false,
            'protected_method' => false,
            'private_method' => false,

            'public_static_method' => false,
            'protected_static_method' => false,
            'private_static_method' => false,

            'not_exists' => false,
            '' => false,
        ];

        $object = $this->getObject();

        foreach ($items as $name => $expected) {
            $actual = (new ObjectHelper($object))->isStaticProperty($name);
            $this->assertSame($expected, $actual);
        }
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
