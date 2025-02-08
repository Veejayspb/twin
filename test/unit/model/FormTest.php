<?php

use PHPUnit\Framework\TestCase;
use twin\model\Form;

final class FormTest extends TestCase
{
    public function testGetLabel()
    {
        $form = $this->getForm();

        $actual = $form->getLabel('public');
        $this->assertSame('Some label', $actual);

        $actual = $form->getLabel('not_exists');
        $this->assertSame('not_exists', $actual);
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

            public function labels(): array
            {
                return [
                    'public' => 'Some label',
                ];
            }
        };
    }
}
