<?php

namespace unit\helper;

use twin\helper\PasswordGenerator;
use test\helper\BaseTestCase;
use test\helper\ObjectProxy;

final class PasswordGeneratorTest extends BaseTestCase
{
    const MD5_LOWER = 'c3fcd3d76192e4007dfb496cca67e13b';
    const MD5_UPPER = '437bba8e0bf58337674f4539e75186ac';
    const MD5_DIGIT = '781e5e245d69b566979b86e28d23f2c7';

    const DEFAULT_COLLECTIONS = [
        self::MD5_LOWER => PasswordGenerator::LOWER,
        self::MD5_UPPER => PasswordGenerator::UPPER,
        self::MD5_DIGIT => PasswordGenerator::DIGIT,
    ];

    public function testConstruct()
    {
        $generator = new PasswordGenerator;
        $proxy = new ObjectProxy($generator);
        $this->assertSame(self::DEFAULT_COLLECTIONS, $proxy->collections);
    }

    public function testFixed()
    {
        $generator = new PasswordGenerator;
        $proxy = new ObjectProxy($generator);
        $proxy->collections = self::DEFAULT_COLLECTIONS;

        for ($i = 0; $i < 100; $i++) {
            $length = mt_rand(0, 20);
            $password = $generator->fixed($length);
            $this->assertSame($length, mb_strlen($password)); // Длина должна точно соответстовать переданному значению
            $this->checkPassword($password, $proxy->collections);
        }

        $proxy->collections = [];
        $password = $generator->fixed(10);
        $this->assertSame('', $password);
    }

    public function testRange()
    {
        $generator = new PasswordGenerator;
        $proxy = new ObjectProxy($generator);
        $proxy->collections = self::DEFAULT_COLLECTIONS;

        for ($i = 0; $i < 100; $i++) {
            $min = mt_rand(1, 10);
            $max = mt_rand(10, 20);
            $password = $generator->range($min, $max);
            $length = mb_strlen($password);
            $this->assertGreaterThanOrEqual($min, $length);
            $this->assertLessThanOrEqual($max, $length);
            $this->checkPassword($password, $proxy->collections);
        }

        $password = $generator->range(2, 8);
        $length = mb_strlen($password);
        $this->assertGreaterThanOrEqual(2, $length);
        $this->assertLessThanOrEqual(8, $length);
        $this->checkPassword($password, $proxy->collections);

        $proxy->collections = [];
        $password = $generator->range(10, 12);
        $this->assertSame('', $password);
    }

    public function testAddCollection()
    {
        $generator = new PasswordGenerator;
        $proxy = new ObjectProxy($generator);
        $proxy->collections = [];
        $expected = [];

        $result = $generator->addCollection(PasswordGenerator::DIGIT);
        $expected[self::MD5_DIGIT] = PasswordGenerator::DIGIT;
        $this->assertSame($generator, $result);
        $this->assertSame($expected, $proxy->collections);

        $result = $generator->addCollection(PasswordGenerator::UPPER);
        $expected[self::MD5_UPPER] = PasswordGenerator::UPPER;
        $this->assertSame($generator, $result);
        $this->assertSame($expected, $proxy->collections);

        $result = $generator->addCollection(PasswordGenerator::DIGIT);
        $expected[self::MD5_DIGIT] = PasswordGenerator::DIGIT;
        $this->assertSame($generator, $result);
        $this->assertSame($expected, $proxy->collections);

        $str = 'random';
        $result = $generator->addCollection($str);
        $expected[md5($str)] = $str;
        $this->assertSame($generator, $result);
        $this->assertSame($expected, $proxy->collections);
    }

    public function testRemoveCollection()
    {
        $generator = new PasswordGenerator;
        $proxy = new ObjectProxy($generator);
        $proxy->collections = $expected = self::DEFAULT_COLLECTIONS;

        $result = $generator->removeCollection(PasswordGenerator::UPPER);
        unset($expected[self::MD5_UPPER]);
        $this->assertSame($generator, $result);
        $this->assertSame($expected, $proxy->collections);

        $result = $generator->removeCollection(PasswordGenerator::LOWER);
        unset($expected[self::MD5_LOWER]);
        $this->assertSame($generator, $result);
        $this->assertSame($expected, $proxy->collections);

        $result = $generator->removeCollection(PasswordGenerator::DIGIT);
        unset($expected[self::MD5_DIGIT]);
        $this->assertSame($generator, $result);
        $this->assertSame($expected, $proxy->collections);

        $result = $generator->removeCollection(PasswordGenerator::DIGIT);
        $this->assertSame($generator, $result);
        $this->assertSame($expected, $proxy->collections);
    }

    public function testClearCollections()
    {
        $generator = new PasswordGenerator;
        $proxy = new ObjectProxy($generator);
        $proxy->collections = $expected = self::DEFAULT_COLLECTIONS;

        $result = $generator->clearCollections();
        $this->assertSame($generator, $result);
        $this->assertSame([], $proxy->collections);
    }

    public function testGetCollection()
    {
        $generator = new PasswordGenerator;
        $proxy = new ObjectProxy($generator);
        $proxy->collections = self::DEFAULT_COLLECTIONS;

        $collection = $proxy->getCollection(-3);
        $this->assertSame(PasswordGenerator::LOWER, $collection);

        $collection = $proxy->getCollection(-2);
        $this->assertSame(PasswordGenerator::DIGIT, $collection);

        $collection = $proxy->getCollection(-1);
        $this->assertSame(PasswordGenerator::UPPER, $collection);

        $collection = $proxy->getCollection(0);
        $this->assertSame(PasswordGenerator::LOWER, $collection);

        $collection = $proxy->getCollection(1);
        $this->assertSame(PasswordGenerator::UPPER, $collection);

        $collection = $proxy->getCollection(2);
        $this->assertSame(PasswordGenerator::DIGIT, $collection);

        $collection = $proxy->getCollection(3);
        $this->assertSame(PasswordGenerator::LOWER, $collection);

        $proxy->collections = [];
        $collection = $proxy->getCollection(0);
        $this->assertNull($collection);
    }

    /**
     * Проверить пароль на наличие необходимых символов из разных коллекций.
     * @param string $password
     * @param array $collections
     * @return void
     */
    private function checkPassword(string $password, array $collections): void
    {
        $length = mb_strlen($password);
        $collectionsCount = count($collections);
        $diversity = $this->getDiversity($password, $collections);

        if ($collectionsCount < $length) {
            // Если кол-во коллекций < длина строки, то в строке должны присутствовать символы из каждой коллекции
            $this->assertSame($collectionsCount, $diversity);
        } else {
            // Если длина строки <= кол-ва коллекций, то символы из каждой коллекции должны использоваться не более 1 раза
            $this->assertSame($length, $diversity);
        }
    }

    /**
     * Кол-во разных коллекций, использованных для генерации строки.
     * @param string $str
     * @param array $collections
     * @return int
     */
    private function getDiversity(string $str, array $collections): int
    {
        $result = 0;

        foreach ($collections as $collection) {
            if ($this->hasChar($str, $collection)) {
                $result++;
            }
        }

        return $result;
    }

    /**
     * Проверить строку на наличие хотя бы одного символа из коллекции.
     * @param string $str - строка
     * @param string $collection - коллекция символов
     * @return bool
     */
    private function hasChar(string $str, string $collection): bool
    {
        $length = mb_strlen($str);

        for ($i = 0; $i < $length; $i++) {
            $char = $str[$i];
            $pos = mb_strpos($collection, $char);

            if ($pos !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return PasswordGenerator
     */
    /*private function getPasswordGenerator(): PasswordGenerator
    {
        return $this->mock(PasswordGenerator::class, null, [], []);
    }*/
}
