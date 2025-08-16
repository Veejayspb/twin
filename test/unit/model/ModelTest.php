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
        $model->error()->clearErrors();

        $actual = $model->validate();
        $this->assertFalse($actual);

        $actual = $model->validate(['protected']);
        $this->assertTrue($actual);
    }

    public function testPropagate()
    {
        $model = $this->getModel();
        $actual = $model::propagate([
            ['public' => 'one'],
            ['protected' => 'two'],
            ['undefined' => 'three'],
        ]);

        $this->assertCount(3, $actual);
        $this->assertSame('one', $actual[0]->public);
        $this->assertSame('public', $actual[1]->public);
        $this->assertSame('public', $actual[2]->public);
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

            public function labels(): array
            {
                return [
                    'public' => 'Some label',
                ];
            }

            protected function rules(): void
            {
                $this->error()->setErrors(ModelTest::ERRORS);
            }
        };
    }
}
