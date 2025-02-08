<?php

use PHPUnit\Framework\TestCase;
use twin\model\Form;

final class FormTest extends TestCase
{
    const ERROR_MSG = 'error message';

    const ERRORS = [
        'public' => self::ERROR_MSG,
    ];

    public function testGetLabel()
    {
        $form = $this->getForm();

        $actual = $form->getLabel('public');
        $this->assertSame('Some label', $actual);

        $actual = $form->getLabel('not_exists');
        $this->assertSame('not_exists', $actual);
    }

    public function testSetError()
    {
        $form = $this->getForm();
        $form->clearErrors();

        $form->setError('public', self::ERROR_MSG);
        $actual = $form->getErrors();
        $this->assertSame(['public' => self::ERROR_MSG], $actual);

        $form->clearErrors();

        $form->setError('not_exists', self::ERROR_MSG);
        $actual = $form->getErrors();
        $this->assertSame([], $actual);
    }

    public function testSetErrors()
    {
        $form = $this->getForm();
        $form->clearErrors();
        $form->setErrors([
            'public' => self::ERROR_MSG,
            'not_exists' => self::ERROR_MSG,
        ]);
        $actual = $form->getErrors();
        $this->assertSame(['public' => self::ERROR_MSG], $actual);
    }

    public function testGetError()
    {
        $form = $this->getForm();
        $form->clearErrors();

        $actual = $form->getError('public');
        $this->assertNull($actual);

        $form->setError('public', self::ERROR_MSG);
        $actual = $form->getError('public');
        $this->assertSame(self::ERROR_MSG, $actual);

        $form->setError('not_exists', self::ERROR_MSG);
        $actual = $form->getError('not_exists');
        $this->assertNull($actual);
    }

    public function testGetErrors()
    {
        $form = $this->getForm();
        $actual = $form->getErrors();
        $this->assertSame(self::ERRORS, $actual);
    }

    public function testHasError()
    {
        $form = $this->getForm();
        $form->clearErrors();

        $actual = $form->hasError('not_exists');
        $this->assertFalse($actual);

        $actual = $form->hasError('public');
        $this->assertFalse($actual);

        $form->setError('public', self::ERROR_MSG);
        $actual = $form->hasError('public');
        $this->assertTrue($actual);
    }

    public function testHasErrors()
    {
        $form = $this->getForm();

        $actual = $form->hasErrors();
        $this->assertTrue($actual);

        $form->clearErrors();

        $actual = $form->hasErrors();
        $this->assertFalse($actual);
    }

    public function testClearError()
    {
        $form = $this->getForm();

        $form->clearError('not_exists');
        $actual = $form->getErrors();
        $this->assertSame(self::ERRORS, $actual);

        $form->clearError('public');
        $actual = $form->getErrors();
        $this->assertSame([], $actual);
    }

    public function testClearErrors()
    {
        $form = $this->getForm();

        // Ошибка валидации конкретного атрибута
        $form->clearErrors(['not_exists']);
        $actual = $form->getErrors();
        $this->assertSame(self::ERRORS, $actual);

        // Ошибки валидации всех атрибутов
        $form->clearErrors();
        $actual = $form->getErrors();
        $this->assertSame([], $actual);
    }

    public function testValidate()
    {
        $form = $this->getForm();
        $form->clearErrors();

        $actual = $form->validate();
        $this->assertFalse($actual);

        $actual = $form->validate(['protected']);
        $this->assertTrue($actual);
    }

    /**
     * @return Form
     */
    private function getForm(): Form
    {
        return new class extends Form
        {
            public string $public = 'public';
            protected string $protected = 'protected';
            private string $private = 'private';
            public static string $static = 'static';

            protected array $_errors = FormTest::ERRORS;

            public function labels(): array
            {
                return [
                    'public' => 'Some label',
                ];
            }

            protected function rules(): void
            {
                $this->setErrors(FormTest::ERRORS);
            }
        };
    }
}
