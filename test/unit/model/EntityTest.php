<?php

use PHPUnit\Framework\TestCase;
use twin\model\Entity;

class EntityTest extends TestCase
{
    public function testAttributeNames()
    {
        $entity = $this->getEntity();
        $names = $entity->attributeNames();

        $this->assertSame(['public'], $names);
    }

    public function testGetAttribute()
    {
        $entity = $this->getEntity();

        $actual = $entity->getAttribute('public');
        $this->assertSame('public', $actual);

        $actual = $entity->getAttribute('protected');
        $this->assertNull($actual);

        $actual = $entity->getAttribute('private');
        $this->assertNull($actual);

        $actual = $entity->getAttribute('static');
        $this->assertNull($actual);

        $actual = $entity->getAttribute('not_exists');
        $this->assertNull($actual);
    }

    public function testSetAttribute()
    {
        $entity = $this->getEntity();
        $value = 'new value';

        $entity->setAttribute('public', $value);
        $this->assertSame($value, $entity->getAttribute('public'));

        $entity->setAttribute('protected', $value);
        $this->assertNull($entity->getAttribute('protected'));

        $entity->setAttribute('private', $value);
        $this->assertNull($entity->getAttribute('private'));

        $entity->setAttribute('static', $value);
        $this->assertNull($entity->getAttribute('static'));

        $entity->setAttribute('not_exists', $value);
        $this->assertNull($entity->getAttribute('not_exists'));
    }

    public function testSetAttributes()
    {
        $entity = $this->getEntity();
        $value = 'new value';

        $entity->setAttributes([
            'public' => $value,
            'protected' => $value,
            'private' => $value,
            'static' => $value,
            'not_exists' => $value,
        ]);
        $this->assertSame(['public' => $value], $entity->getAttributes());
    }

    public function testGetAttributes()
    {
        $entity = $this->getEntity();
        $value = 'new value';

        $actual = $entity->getAttributes();
        $this->assertSame(['public' => 'public'], $actual);

        $entity->setAttribute('public', $value);
        $entity->setAttribute('protected', $value);
        $actual = $entity->getAttributes();
        $this->assertSame(['public' => $value], $actual);
    }

    public function testHasAttribute()
    {
        $entity = $this->getEntity();

        $actual = $entity->hasAttribute('public');
        $this->assertTrue($actual);

        $actual = $entity->hasAttribute('protected');
        $this->assertFalse($actual);

        $actual = $entity->hasAttribute('private');
        $this->assertFalse($actual);

        $actual = $entity->hasAttribute('static');
        $this->assertFalse($actual);

        $actual = $entity->hasAttribute('not_exists');
        $this->assertFalse($actual);
    }

    /**
     * @return Entity
     */
    private function getEntity()
    {
        return new class extends Entity
        {
            public string $public = 'public';
            protected string $protected = 'protected';
            private string $private = 'private';
            public static string $static = 'static';
        };
    }
}
