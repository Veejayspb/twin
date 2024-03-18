<?php

use twin\common\Component;
use test\helper\BaseTestCase;

final class ComponentTest extends BaseTestCase
{
    /**
     * @var Component
     */
    protected $object;

    public function testConstruct()
    {
        $this->noRequiredProperties();
        $this->hasRequiredProperties();
    }

    /**
     * Компонент без обязательных полей.
     * @return void
     */
    protected function noRequiredProperties(): void
    {
        $code = $this->catchExceptionCode(function () {
            $this->object = new class() extends Component {
                public $a;
            };
        });

        $this->assertSame(0, $code);
        $this->assertNull($this->object->a);

        $code = $this->catchExceptionCode(function () {
            $this->object = new class([]) extends Component {
                public $a;
            };
        });

        $this->assertSame(0, $code);
        $this->assertNull($this->object->a);

        $code = $this->catchExceptionCode(function () {
            $this->object = new class(['a' => 1]) extends Component {
                public $a;
            };
        });

        $this->assertSame(0, $code);
        $this->assertSame(1, $this->object->a);

        $code = $this->catchExceptionCode(function () {
            $this->object = new class(['a' => []]) extends Component {
                public $a;
            };
        });

        $this->assertSame(0, $code);
        $this->assertSame([], $this->object->a);
    }

    /**
     * Компонент с обязательными полями.
     * @return void
     */
    protected function hasRequiredProperties(): void
    {
        $code = $this->catchExceptionCode(function () {
            $this->object = new class() extends Component {
                public $a;
                protected $_requiredProperties = ['a', 'b'];
            };
        });

        $this->assertSame(500, $code);

        $code = $this->catchExceptionCode(function () {
            $this->object = new class([]) extends Component {
                public $a;
                protected $_requiredProperties = ['a', 'b'];
            };
        });

        $this->assertSame(500, $code);

        $code = $this->catchExceptionCode(function () {
            $this->object = new class([]) extends Component {
                public $a;
                protected $_requiredProperties = ['a', 'b'];
            };
        });

        $this->assertSame(500, $code);

        $code = $this->catchExceptionCode(function () {
            $this->object = new class(['a' => 'aaa', 'b' => 'bbb']) extends Component {
                public $a;
                protected $_requiredProperties = ['a', 'b'];
            };
        });

        $this->assertSame(0, $code);
        $this->assertSame('aaa', $this->object->a);
        $this->assertFalse(property_exists($this->object, 'b'));

        $code = $this->catchExceptionCode(function () {
            $this->object = new class(['a' => null]) extends Component {
                public $a;
                protected $_requiredProperties = ['a'];
            };
        });

        $this->assertSame(500, $code);

        $code = $this->catchExceptionCode(function () {
            $this->object = new class(['a' => []]) extends Component {
                public $a;
                protected $_requiredProperties = ['a'];
            };
        });

        $this->assertSame(500, $code);
    }
}
