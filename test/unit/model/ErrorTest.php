<?php

use PHPUnit\Framework\TestCase;
use twin\model\Model;

final class ErrorTest extends TestCase
{
    const ERROR_MSG = 'error message';

    const ERRORS = [
        'public' => self::ERROR_MSG,
    ];

    public function testSetError()
    {
        $error = $this->getModel()->error();
        $error->clearErrors();

        $error->setError('public', self::ERROR_MSG);
        $actual = $error->getErrors();
        $this->assertSame(['public' => self::ERROR_MSG], $actual);

        $error->clearErrors();

        $error->setError('not_exists', self::ERROR_MSG);
        $actual = $error->getErrors();
        $this->assertSame([], $actual);
    }

    public function testSetErrors()
    {
        $error = $this->getModel()->error();
        $error->clearErrors();
        $error->setErrors([
            'public' => self::ERROR_MSG,
            'not_exists' => self::ERROR_MSG,
        ]);
        $actual = $error->getErrors();
        $this->assertSame(['public' => self::ERROR_MSG], $actual);
    }

    public function testGetError()
    {
        $error = $this->getModel()->error();
        $error->clearErrors();

        $actual = $error->getError('public');
        $this->assertNull($actual);

        $error->setError('public', self::ERROR_MSG);
        $actual = $error->getError('public');
        $this->assertSame(self::ERROR_MSG, $actual);

        $error->setError('not_exists', self::ERROR_MSG);
        $actual = $error->getError('not_exists');
        $this->assertNull($actual);
    }

    public function testGetErrors()
    {
        $error = $this->getModel()->error();
        $actual = $error->getErrors();
        $this->assertSame(self::ERRORS, $actual);
    }

    public function testHasError()
    {
        $error = $this->getModel()->error();
        $error->clearErrors();

        $actual = $error->hasError('not_exists');
        $this->assertFalse($actual);

        $actual = $error->hasError('public');
        $this->assertFalse($actual);

        $error->setError('public', self::ERROR_MSG);
        $actual = $error->hasError('public');
        $this->assertTrue($actual);
    }

    public function testHasErrors()
    {
        $error = $this->getModel()->error();

        $actual = $error->hasErrors();
        $this->assertTrue($actual);

        $error->clearErrors();

        $actual = $error->hasErrors();
        $this->assertFalse($actual);
    }

    public function testClearError()
    {
        $error = $this->getModel()->error();

        $error->clearError('not_exists');
        $actual = $error->getErrors();
        $this->assertSame(self::ERRORS, $actual);

        $error->clearError('public');
        $actual = $error->getErrors();
        $this->assertSame([], $actual);
    }

    public function testClearErrors()
    {
        $error = $this->getModel()->error();

        // Ошибка валидации конкретного атрибута
        $error->clearErrors(['not_exists']);
        $actual = $error->getErrors();
        $this->assertSame(self::ERRORS, $actual);

        // Ошибки валидации всех атрибутов
        $error->clearErrors();
        $actual = $error->getErrors();
        $this->assertSame([], $actual);
    }

    /**
     * @return Model
     */
    protected function getModel()
    {
        return new class extends Model
        {
            public $public = 'public';
            protected $protected = 'protected';
            private $private = 'private';
            public static $static = 'static';

            public function __construct()
            {
                $this->error()->setErrors(ErrorTest::ERRORS);
            }
        };
    }
}
