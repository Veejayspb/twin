<?php

use PHPUnit\Framework\TestCase;
use twin\model\Model;

final class ModelTest extends TestCase
{
    const ERROR_MSG = 'error message';

    const ERRORS = [
        'public' => self::ERROR_MSG,
    ];

    public function testGetLabel()
    {
        $model = $this->getModel();

        $actual = $model->getLabel('public');
        $this->assertSame('Some label', $actual);

        $actual = $model->getLabel('not_exists');
        $this->assertSame('not_exists', $actual);
    }

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

    public function testAttributeNames()
    {
        $model = $this->getModel();
        $names = $model->attributeNames();

        $this->assertSame(['public'], $names);
    }

    public function testGetAttribute()
    {
        $model = $this->getModel();

        $actual = $model->getAttribute('public');
        $this->assertSame('public', $actual);

        $actual = $model->getAttribute('protected');
        $this->assertNull($actual);

        $actual = $model->getAttribute('private');
        $this->assertNull($actual);

        $actual = $model->getAttribute('static');
        $this->assertNull($actual);

        $actual = $model->getAttribute('not_exists');
        $this->assertNull($actual);
    }

    public function testSetAttribute()
    {
        $model = $this->getModel();
        $value = 'new value';

        $model->setAttribute('public', $value);
        $this->assertSame($value, $model->getAttribute('public'));

        $model->setAttribute('protected', $value);
        $this->assertNull($model->getAttribute('protected'));

        $model->setAttribute('private', $value);
        $this->assertNull($model->getAttribute('private'));

        $model->setAttribute('static', $value);
        $this->assertNull($model->getAttribute('static'));

        $model->setAttribute('not_exists', $value);
        $this->assertNull($model->getAttribute('not_exists'));
    }

    public function testSetAttributes()
    {
        $model = $this->getModel();
        $value = 'new value';

        $model->setAttributes([
            'public' => $value,
            'protected' => $value,
            'private' => $value,
            'static' => $value,
            'not_exists' => $value,
        ]);
        $this->assertSame(['public' => $value], $model->getAttributes());
    }

    public function testGetAttributes()
    {
        $model = $this->getModel();
        $value = 'new value';

        $actual = $model->getAttributes();
        $this->assertSame(['public' => 'public'], $actual);

        $model->setAttribute('public', $value);
        $model->setAttribute('protected', $value);
        $actual = $model->getAttributes();
        $this->assertSame(['public' => $value], $actual);
    }

    public function testHasAttribute()
    {
        $model = $this->getModel();

        $actual = $model->hasAttribute('public');
        $this->assertTrue($actual);

        $actual = $model->hasAttribute('protected');
        $this->assertFalse($actual);

        $actual = $model->hasAttribute('private');
        $this->assertFalse($actual);

        $actual = $model->hasAttribute('static');
        $this->assertFalse($actual);

        $actual = $model->hasAttribute('not_exists');
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
