<?php

namespace Arrayy\tests;

use Arrayy\Arrayy;

/**
 * from https://github.com/peakphp/framework/blob/master/tests/Collection/DotNotationCollectionTest.php
 *
 * @internal
 */
final class DotNotationCollectionTest extends \PHPUnit\Framework\TestCase
{
    private $_array_test_1 = [
        'foo' => [
            'bar'  => 123,
            'deep' => [
                '\bar$' => 'ABC',
            ],
        ],
    ];

    public function testCreateObject()
    {
        $dn = new Arrayy(['foo' => 'bar']);
        static::assertFalse($dn->isEmpty());
    }

    public function testGetPath()
    {
        $dn = new Arrayy($this->_array_test_1);

        static::assertFalse($dn->isEmpty());
        static::assertEquals($dn->get('foo.bar'), 123);
        static::assertSame($dn->get('foo.deep.\bar$'), 'ABC');
        static::assertNull($dn->get('foo.test'));
    }

    public function testSetPath()
    {
        $dn = new Arrayy($this->_array_test_1);

        $dn->set('foo.jade.profile.new', ['test' => ['of' => 'path']]);

        static::assertFalse($dn->isEmpty());
        static::assertEquals($dn->get('foo.bar'), 123);

        $jade = $dn->get('foo.jade.profile.new');
        if (\method_exists(__CLASS__, 'assertIsArray')) {
            static::assertIsArray($jade->getArray());
        } else {
            /** @noinspection PhpUndefinedMethodInspection */
            static::assertInternalType('array', $jade->getArray());
        }
        static::assertSame('path', $jade['test']['of']);

        $dn->add('test');
        static::assertSame('test', $dn[0]);
    }

    public function testSetException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not set value at this path "test" because (integer)"123" is not an array.');
        $dn = new Arrayy($this->_array_test_1);
        $dn->set('foo.bar.test', ['bob']);
    }

    public function testHavePath()
    {
        $dn = new Arrayy($this->_array_test_1);

        static::assertFalse($dn->isEmpty());
        static::assertTrue($dn->has('foo.bar'));
        static::assertTrue($dn->has('foo.deep.\bar$'));
        static::assertFalse($dn->has('bar.foo'));
    }

    public function testAdd()
    {
        $dn = new Arrayy($this->_array_test_1);

        $dn->add(['test' => 456], 'foo.bar');

        static::assertTrue($dn->has('foo.bar.test'));
        static::assertTrue($dn->has('foo.bar.0'));

        $dn->add(['last' => 789], 'foo.bar');

        static::assertTrue($dn->has('foo.bar.test'));
        static::assertTrue($dn->has('foo.bar.0'));
        static::assertTrue($dn->has('foo.bar.last'));

        static::assertEquals($dn->get('foo.bar.0'), 123);
        static::assertEquals($dn->get('foo.bar.test'), 456);
        static::assertEquals($dn->get('foo.bar.last'), 789);

        $dn->add(123, 'foo.llllll');

        static::assertTrue($dn->has('foo.bar.test'));
        static::assertTrue($dn->has('foo.bar.0'));
        static::assertTrue($dn->has('foo.bar.last'));

        static::assertEquals($dn->get('foo.bar.0'), 123);
        static::assertEquals($dn->get('foo.bar.test'), 456);
        static::assertEquals($dn->get('foo.bar.last'), 789);

        static::assertEquals($dn->get('foo.llllll'), 123);

        $dn->add([123456, 789, 'foo' => 'lall'], 'foo.llllll');

        static::assertTrue($dn->has('foo.bar.test'));
        static::assertTrue($dn->has('foo.bar.0'));
        static::assertTrue($dn->has('foo.bar.last'));

        static::assertEquals($dn->get('foo.bar.0'), 123);
        static::assertEquals($dn->get('foo.bar.test'), 456);
        static::assertEquals($dn->get('foo.bar.last'), 789);

        static::assertEquals($dn->get('foo.llllll.0'), 123);
        static::assertEquals($dn->get('foo.llllll.1'), 123456);
        static::assertEquals($dn->get('foo.llllll.foo'), 'lall');
    }
}
