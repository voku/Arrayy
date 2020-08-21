<?php

namespace Arrayy\tests;

use Arrayy\Arrayy;

/**
 * from https://packagist.org/packages/adbario/php-dot-notation
 *
 * @internal
 */
final class DotTest extends \PHPUnit\Framework\TestCase
{
    /*
     * --------------------------------------------------------------
     * Construct
     * --------------------------------------------------------------
     */
    public function testConstructWithoutValues()
    {
        $dot = new Arrayy();

        static::assertSame([], $dot->getAll());
    }

    public function testConstructWithArray()
    {
        $dot = new Arrayy(['foo' => 'bar']);

        static::assertSame('bar', $dot->get('foo'));
    }

    public function testConstructWithString()
    {
        $dot = new Arrayy('foobar');

        static::assertSame('foobar', $dot->get(0));
    }

    public function testConstructWithDot()
    {
        $dot1 = new Arrayy(['foo' => 'bar']);
        $dot2 = new Arrayy($dot1);

        static::assertSame('bar', $dot2->get('foo'));
    }

    public function testConstructHelper()
    {
        $dot = \Arrayy\create(['foo' => 'bar']);

        static::assertInstanceOf(Arrayy::class, $dot);
        static::assertSame('bar', $dot->get('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Add
     * --------------------------------------------------------------
     */

    public function testAddKeyValuePair()
    {
        $dot = new Arrayy();
        $dot->append('baz', 'foo.bar');

        static::assertSame('baz', $dot->get('foo.bar'));
    }

    public function testAddValueToExistingKey()
    {
        $dot = new Arrayy(['foo' => 'bar']);
        $dot->append('baz', 'foo');

        static::assertSame('baz', $dot->get('foo'));
    }

    public function testAddArrayOfKeyValuePairs()
    {
        $dot = new Arrayy(['foobar' => 'baz']);
        $dot->add([
            'foobar' => 'qux',
            'corge'  => 'grault',
        ]);

        static::assertSame([
            'foobar' => 'baz',
            0        => [
                'foobar' => 'qux',
                'corge'  => 'grault',
            ],
        ], $dot->getAll());
    }

    /*
     * --------------------------------------------------------------
     * All
     * --------------------------------------------------------------
     */

    public function testAllReturnsAllItems()
    {
        $dot = new Arrayy(['foo' => 'bar']);

        static::assertSame(['foo' => 'bar'], $dot->getAll());
    }

    /*
     * --------------------------------------------------------------
     * Clear
     * --------------------------------------------------------------
     */

    public function testClearKey()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot->clear('foo.bar');

        static::assertNull($dot->get('foo.bar'));
    }

    public function testClearNonExistingKey()
    {
        $dot = new Arrayy();
        $dot->clear('foo');

        static::assertNull($dot->get('foo'));
    }

    public function testClearArrayOfKeys()
    {
        $dot = new Arrayy(['foo' => 'bar', 'baz' => 'qux', 'lall' => 'foo']);
        $dot->clear(['foo.bar', 'baz']);

        static::assertSame(['foo' => null, 'lall' => 'foo'], $dot->getAll());
    }

    public function testClearAll()
    {
        $dot = new Arrayy(['foo' => 'bar']);
        $dot->clear();

        static::assertSame([], $dot->getAll());
    }

    /*
     * --------------------------------------------------------------
     * Delete
     * --------------------------------------------------------------
     */

    public function testDeleteKey()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot->delete('foo.bar');

        static::assertFalse($dot->has('foo.bar'));
    }

    public function testDeleteNonExistingKey()
    {
        $dot = new Arrayy(['foo' => 'bar']);
        $dot->delete('baz.qux');

        static::assertSame(['foo' => 'bar'], $dot->getAll());
    }

    public function testDeleteArrayOfKeys()
    {
        $dot = new Arrayy(['foo' => 'bar', 'baz' => 'qux']);
        $dot->delete(['foo', 'baz']);

        static::assertSame([], $dot->getAll());
    }

    /*
     * --------------------------------------------------------------
     * Flatten
     * --------------------------------------------------------------
     */
    public function testFlatten()
    {
        $dot = new Arrayy(['foo' => ['abc' => 'xyz', 'bar' => ['baz']]]);
        $flatten = $dot->flatten();
        static::assertSame('xyz', $flatten['foo.abc']);
        static::assertSame('baz', $flatten['foo.bar.0']);

        // ---

        $dot = new Arrayy([0 => ['foo' => 'var'], 1 => ['lall' => 1, 'foo' => 'lall']]);
        $flatten = $dot->flatten();
        static::assertSame('lall', $flatten['1.foo']);
    }

    public function testFlattenWithCustomDelimiter()
    {
        $dot = new Arrayy(['foo' => ['abc' => 'xyz', 'bar' => ['baz']]]);
        $flatten = $dot->flatten('_');
        static::assertSame('xyz', $flatten['foo_abc']);
        static::assertSame('baz', $flatten['foo_bar_0']);
    }

    /*
     * --------------------------------------------------------------
     * Get
     * --------------------------------------------------------------
     */

    public function testGetValueFromKey()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);

        static::assertSame('baz', $dot->get('foo.bar'));
    }

    public function testGetValueFromNonExistingKey()
    {
        $dot = new Arrayy();

        static::assertNull($dot->get('foo'));
    }

    public function testGetGivenDefaultValueFromNonExistingKey()
    {
        $dot = new Arrayy();

        static::assertSame('bar', $dot->get('foo', 'bar'));
    }

    /*
     * --------------------------------------------------------------
     * Has
     * --------------------------------------------------------------
     */

    public function testHasKey()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);

        static::assertTrue($dot->has('foo.bar'));

        $dot->delete('foo.bar');

        static::assertFalse($dot->has('foo.bar'));
    }

    public function testHasArrayOfKeys()
    {
        $dot = new Arrayy(['foo' => 'bar', 'baz' => 'qux']);

        static::assertTrue($dot->has(['foo', 'baz']));

        $dot->delete('foo');

        static::assertFalse($dot->has(['foo', 'baz']));
    }

    public function testHasWithEmptyDot()
    {
        $dot = new Arrayy();

        static::assertFalse($dot->has('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Is empty
     * --------------------------------------------------------------
     */

    public function testIsEmptyDot()
    {
        $dot = new Arrayy();

        static::assertTrue($dot->isEmpty());

        $dot->set('foo', 'bar');

        static::assertFalse($dot->isEmpty());
    }

    public function testIsEmptyKey()
    {
        $dot = new Arrayy();

        static::assertTrue($dot->isEmpty('foo.bar'));

        $dot->set('foo.bar', 'baz');

        static::assertFalse($dot->isEmpty('foo.bar'));
    }

    public function testIsEmptyArrayOfKeys()
    {
        $dot = new Arrayy();

        static::assertTrue($dot->isEmpty(['foo', 'bar']));

        $dot->set('foo', 'baz');

        static::assertFalse($dot->isEmpty(['foo', 'bar']));
    }

    /*
     * --------------------------------------------------------------
     * Merge
     * --------------------------------------------------------------
     */

    public function testMergeArrayWithDot()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot = $dot->mergeAppendKeepIndex(['foo' => ['bar' => 'qux']]);

        static::assertSame('qux', $dot->get('foo.bar'));
    }

    public function testMergeArrayWithKey()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot = $dot->mergeAppendKeepIndex(['foo' => ['bar' => 'qux']]);

        static::assertSame('qux', $dot->get('foo.bar'));
    }

    public function testMergeDotWithDot()
    {
        $dot1 = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot2 = new Arrayy(['foo' => ['bar' => 'qux']]);
        $dot1 = $dot1->mergeAppendNewIndex($dot2->getArray());

        static::assertSame('qux', $dot1->get('foo.bar'));
    }

    public function testMergeDotObjectWithKey()
    {
        $dot1 = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot2 = new Arrayy(['bar' => 'qux']);
        $dot1 = $dot1->mergeAppendNewIndex(['foo' => $dot2]);

        static::assertSame('qux', $dot1->get('foo.bar'));
    }

    /*
     * --------------------------------------------------------------
     * Recursive merge
     * --------------------------------------------------------------
     */

    public function testRecursiveMergeArrayWithDot()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot = $dot->mergeAppendNewIndex(['foo' => ['bar' => 'qux', 'quux' => 'quuz']], true);

        static::assertSame([
            'foo' => [
                'bar' => [
                    0 => 'baz',
                    1 => 'qux',
                ],
                'quux' => 'quuz',
            ],
        ], $dot->getAll());
        static::assertSame('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveMergeArrayWithKey()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot = $dot->mergePrependNewIndex(['foo' => ['bar' => 'qux', 'quux' => 'quuz']], true);

        static::assertSame(['qux', 'baz'], $dot->get('foo.bar')->getArray());
        static::assertSame('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveMergeDotWithDot()
    {
        $dot1 = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot2 = new Arrayy(['foo' => ['bar' => 'qux', 'quux' => 'quuz']]);
        $dot1 = $dot1->mergePrependNewIndex($dot2->getArray(), true);

        static::assertSame(['qux', 'baz'], $dot1->get('foo.bar')->getArray());
        static::assertSame('quuz', $dot1->get('foo.quux'));
    }

    public function testRecursiveMergeDotObjectWithKey()
    {
        $dot1 = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot2 = new Arrayy(['foo' => ['bar' => 'qux', 'quux' => 'quuz']]);
        $dot1 = $dot1->mergePrependNewIndex($dot2->getArray(), true);

        static::assertSame(['qux', 'baz'], $dot1->get('foo.bar')->getArray());
        static::assertSame('quuz', $dot1->get('foo.quux'));
    }

    /*
     * --------------------------------------------------------------
     * Recursive distinct merge
     * --------------------------------------------------------------
     */

    public function testRecursiveDistinctMergeArrayWithDot()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot = $dot->mergeAppendKeepIndex(['foo' => ['bar' => 'qux', 'quux' => 'quuz']], true);

        static::assertSame('qux', $dot->get('foo.bar'));
        static::assertSame('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveDistinctMergeArrayWithKey()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot = $dot->mergeAppendKeepIndex(['foo' => ['bar' => 'qux', 'quux' => 'quuz']], true);

        static::assertSame('qux', $dot->get('foo.bar'));
        static::assertSame('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveDistinctMergeDotWithDot()
    {
        $dot1 = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot2 = new Arrayy(['foo' => ['bar' => 'qux', 'quux' => 'quuz']]);
        $dot1 = $dot1->mergeAppendKeepIndex($dot2->getArray(), true);

        static::assertSame('qux', $dot1->get('foo.bar'));
        static::assertSame('quuz', $dot1->get('foo.quux'));
    }

    public function testRecursiveDistinctMergeDotObjectWithKey()
    {
        $dot1 = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot2 = new Arrayy(['foo' => ['bar' => 'qux', 'quux' => 'quuz']]);
        $dot1 = $dot1->mergeAppendKeepIndex($dot2->getArray(), true);

        static::assertSame('qux', $dot1->get('foo.bar'));
        static::assertSame('quuz', $dot1->get('foo.quux'));
    }

    /*
     * --------------------------------------------------------------
     * Pull
     * --------------------------------------------------------------
     */

    public function testPullKey()
    {
        $dot = new Arrayy(['foo' => 'bar']);

        static::assertSame('bar', $dot->pull('foo'));
        static::assertFalse($dot->has('foo'));
    }

    public function testPullNonExistingKey()
    {
        $dot = new Arrayy();

        static::assertNull($dot->pull('foo'));
    }

    public function testPullNonExistingKeyWithDefaultValue()
    {
        $dot = new Arrayy();

        static::assertSame('bar', $dot->pull('foo', 'bar'));
    }

    public function testPullAll()
    {
        $dot = new Arrayy(['foo' => 'bar']);

        static::assertSame(['foo' => 'bar'], $dot->pull());
        static::assertSame([], $dot->getAll());
    }

    /*
     * --------------------------------------------------------------
     * Push
     * --------------------------------------------------------------
     */

    public function testPushValue()
    {
        $dot = new Arrayy();
        $dot->push('foo');

        static::assertSame('foo', $dot->get(0));
    }

    public function testPushValueToKey()
    {
        $dot = new Arrayy(['foo']);
        $dot->push('baz');

        static::assertSame(['foo', 'baz'], $dot->getArray());
    }

    /*
     * --------------------------------------------------------------
     * Replace
     * --------------------------------------------------------------
     */

    public function testReplaceWithArray()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot = $dot->replace('foo', 'foo', ['qux' => 'quux']);

        static::assertSame(['qux' => 'quux'], $dot->get('foo')->getArray());
    }

    public function testReplaceKeyWithArray()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);
        $dot = $dot->replace('foo', 'bar', ['qux' => 'corge']);

        static::assertSame(['qux' => 'corge'], $dot->get('bar')->getArray());
    }

    public function testReplaceWithDot()
    {
        $dot1 = new Arrayy(['foo' => ['bar' => 'baz']]);
        $dot2 = new Arrayy(['bar' => 'qux']);
        $dot1 = $dot1->replace('foo', 'foo', $dot2->getArray());

        static::assertSame(['bar' => 'qux'], $dot1->get('foo')->getArray());
    }

    public function testReplaceKeyWithDot()
    {
        $dot1 = new Arrayy(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);
        $dot2 = new Arrayy(['qux' => 'corge']);
        $dot1 = $dot1->mergeAppendKeepIndex(['foo' => $dot2->getArray()], true);

        static::assertSame(['bar' => 'baz', 'qux' => 'corge'], $dot1->get('foo')->getArray());

        // --

        $dot1 = new Arrayy(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);
        $dot2 = new Arrayy(['qux' => 'corge']);
        $dot1 = $dot1->mergeAppendKeepIndex(['foo' => $dot2], true);

        static::assertSame(['bar' => 'baz', 'qux' => 'corge'], $dot1->get('foo')->getArray());
    }

    /*
     * --------------------------------------------------------------
     * Set
     * --------------------------------------------------------------
     */

    public function testSetKeyValuePair()
    {
        $dot = new Arrayy();
        $dot->set('foo.bar', 'baz');

        static::assertSame('baz', $dot->get('foo.bar'));
    }

    public function testSetArrayOfKeyValuePairs()
    {
        $dot = new Arrayy(['foo' => 'bar', 'baz' => 'qux']);

        static::assertSame(['foo' => 'bar', 'baz' => 'qux'], $dot->getAll());
    }

    /*
     * --------------------------------------------------------------
     * Set array
     * --------------------------------------------------------------
     */

    public function testSetArray()
    {
        $dot = (new Arrayy())::createFromArray(['foo' => 'bar']);

        static::assertSame(['foo' => 'bar'], $dot->getAll());
    }

    /*
     * --------------------------------------------------------------
     * Set reference
     * --------------------------------------------------------------
     */

    public function testSetReference()
    {
        $dot = new Arrayy();
        $items = ['foo' => 'bar'];
        $dot->createByReference($items);
        $dot->set('foo', 'baz');

        static::assertSame('baz', $items['foo']);
    }

    /*
     * --------------------------------------------------------------
     * ArrayAccess interface
     * --------------------------------------------------------------
     */

    public function testOffsetExists()
    {
        $dot = new Arrayy(['foo' => 'bar']);

        static::assertTrue(isset($dot['foo']));

        unset($dot['foo']);

        static::assertFalse(isset($dot['foo']));
    }

    public function testOffsetGet()
    {
        $dot = new Arrayy(['foo' => 'bar']);

        static::assertSame('bar', $dot['foo']);
    }

    public function testOffsetSet()
    {
        $dot = new Arrayy();
        $dot['foo.bar'] = 'baz';

        static::assertSame('baz', $dot['foo.bar']);
    }

    public function testOffsetSetWithoutKey()
    {
        $dot = new Arrayy();
        $dot[] = 'foobar';

        static::assertSame('foobar', $dot->get(0));
    }

    public function testOffsetUnset()
    {
        $dot = new Arrayy(['foo' => 'bar']);
        unset($dot['foo']);

        static::assertFalse(isset($dot['foo']));
    }

    /*
     * --------------------------------------------------------------
     * To JSON
     * --------------------------------------------------------------
     */

    public function testToJsonAll()
    {
        $dot = new Arrayy(['foo' => 'bar']);

        static::assertJsonStringEqualsJsonString(
            (string) \json_encode(['foo' => 'bar']),
            $dot->toJson()
        );
    }

    public function testToJsonAllWithOption()
    {
        $dot = new Arrayy(['foo' => "'bar'"]);

        static::assertJsonStringEqualsJsonString(
            (string) \json_encode(['foo' => "'bar'"], \JSON_HEX_APOS),
            $dot->toJson(\JSON_HEX_APOS)
        );
    }

    public function testToJsonKey()
    {
        $dot = new Arrayy(['foo' => ['bar' => 'value']]);

        static::assertJsonStringEqualsJsonString(
            (string) \json_encode(['bar' => 'value']),
            $dot->get('foo')->toJson()
        );
    }

    public function testToJsonKeyWithOptions()
    {
        $dot = new Arrayy(['foo' => ['bar' => "'value'"]]);

        static::assertSame(
            \json_encode(['bar' => "'value'"], \JSON_HEX_APOS),
            $dot->get('foo')->toJson(\JSON_HEX_APOS)
        );
    }

    /*
     * --------------------------------------------------------------
     * Countable interface
     * --------------------------------------------------------------
     */

    public function testCount()
    {
        $dot = new Arrayy([1, 2, 3]);

        static::assertSame(3, $dot->count());
    }

    public function testCountable()
    {
        $dot = new Arrayy([1, 2, 3]);

        static::assertCount(3, $dot);
    }

    /*
     * --------------------------------------------------------------
     * IteratorAggregate interface
     * --------------------------------------------------------------
     */

    public function testGetIteratorReturnsArrayIterator()
    {
        $dot = new Arrayy();

        static::assertInstanceOf(\Arrayy\ArrayyIterator::class, $dot->getIterator());
    }

    public function testIterationReturnsOriginalValues()
    {
        $dot = new Arrayy([1, 2, 3]);

        $items = [];
        foreach ($dot as $item) {
            $items[] = $item;
        }

        static::assertSame([1, 2, 3], $items);
    }

    /*
     * --------------------------------------------------------------
     * JsonSerializable interface
     * --------------------------------------------------------------
     */

    public function testJsonEncodingReturnsJson()
    {
        $dot = new Arrayy(['foo' => 'bar']);

        static::assertJsonStringEqualsJsonString(
            (string) \json_encode(['foo' => 'bar']),
            (string) \json_encode($dot)
        );
    }
}
