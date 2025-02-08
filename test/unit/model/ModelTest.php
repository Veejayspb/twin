<?php

use PHPUnit\Framework\TestCase;
use twin\model\Model;

final class ModelTest extends TestCase
{
    const ERROR_MSG = 'error message';

    const ERRORS = [
        'public' => self::ERROR_MSG,
    ];

    public function testSetError()
    {
        $model = $this->getModel();
        $model->clearErrors();

        $model->setError('public', self::ERROR_MSG);
        $actual = $model->getErrors();
        $this->assertSame(['public' => self::ERROR_MSG], $actual);

        $model->clearErrors();

        $model->setError('not_exists', self::ERROR_MSG);
        $actual = $model->getErrors();
        $this->assertSame([], $actual);
    }

    public function testSetErrors()
    {
        $model = $this->getModel();
        $model->clearErrors();
        $model->setErrors([
            'public' => self::ERROR_MSG,
            'not_exists' => self::ERROR_MSG,
        ]);
        $actual = $model->getErrors();
        $this->assertSame(['public' => self::ERROR_MSG], $actual);
    }

    public function testGetError()
    {
        $model = $this->getModel();
        $model->clearErrors();

        $actual = $model->getError('public');
        $this->assertNull($actual);

        $model->setError('public', self::ERROR_MSG);
        $actual = $model->getError('public');
        $this->assertSame(self::ERROR_MSG, $actual);

        $model->setError('not_exists', self::ERROR_MSG);
        $actual = $model->getError('not_exists');
        $this->assertNull($actual);
    }

    public function testGetErrors()
    {
        $model = $this->getModel();
        $actual = $model->getErrors();
        $this->assertSame(self::ERRORS, $actual);
    }

    public function testHasError()
    {
        $model = $this->getModel();
        $model->clearErrors();

        $actual = $model->hasError('not_exists');
        $this->assertFalse($actual);

        $actual = $model->hasError('public');
        $this->assertFalse($actual);

        $model->setError('public', self::ERROR_MSG);
        $actual = $model->hasError('public');
        $this->assertTrue($actual);
    }

    public function testHasErrors()
    {
        $model = $this->getModel();

        $actual = $model->hasErrors();
        $this->assertTrue($actual);

        $model->clearErrors();

        $actual = $model->hasErrors();
        $this->assertFalse($actual);
    }

    public function testClearError()
    {
        $model = $this->getModel();

        $model->clearError('not_exists');
        $actual = $model->getErrors();
        $this->assertSame(self::ERRORS, $actual);

        $model->clearError('public');
        $actual = $model->getErrors();
        $this->assertSame([], $actual);
    }

    public function testClearErrors()
    {
        $model = $this->getModel();

        // Ошибка валидации конкретного атрибута
        $model->clearErrors(['not_exists']);
        $actual = $model->getErrors();
        $this->assertSame(self::ERRORS, $actual);

        // Ошибки валидации всех атрибутов
        $model->clearErrors();
        $actual = $model->getErrors();
        $this->assertSame([], $actual);
    }

    public function testSafe()
    {
        $model = $this->getModel();

        $actual = $model->safe();
        $this->assertSame(['public'], $actual);
    }

    public function testIsSafeAttribute()
    {
        $model = $this->getModel();

        $actual = $model->isSafeAttribute('public');
        $this->assertTrue($actual);

        $actual = $model->isSafeAttribute('protected');
        $this->assertFalse($actual);

        $actual = $model->isSafeAttribute('private');
        $this->assertFalse($actual);

        $actual = $model->isSafeAttribute('static');
        $this->assertFalse($actual);

        $actual = $model->isSafeAttribute('not_exists');
        $this->assertFalse($actual);
    }

    public function testValidate()
    {
        $model = $this->getModel();
        $model->clearErrors();

        $actual = $model->validate();
        $this->assertFalse($actual);

        $actual = $model->validate(['protected']);
        $this->assertTrue($actual);
    }

    /**
     * @return Model
     */
    private function getModel()
    {
        return new class extends Model
        {
            public $public = 'public';
            protected $protected = 'protected';
            private $private = 'private';
            public static $static = 'static';

            protected $_errors = ModelTest::ERRORS;

            public function labels(): array
            {
                return [
                    'public' => 'Some label',
                ];
            }

            protected function rules(): void
            {
                $this->setErrors(ModelTest::ERRORS);
            }
        };
    }
}
