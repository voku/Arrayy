<?php

namespace Arrayy\tests;

use Arrayy\Arrayy as A;
use Arrayy\ArrayyIterator;

/**
 * Copy of a test class from "https://github.com/bocharsky-bw/Arrayzy/"
 *
 * @internal
 */
final class BasicArrayTest extends \PHPUnit\Framework\TestCase
{
    const TYPE_ASSOC = 'assoc';

    const TYPE_EMPTY = 'empty';

    const TYPE_MIXED = 'mixed';

    const TYPE_NUMERIC = 'numeric';

    /**
     * @var string
     */
    protected $arrayyClassName = A::class;

    /**
     * @return array
     */
    public function simpleArrayProvider(): array
    {
        return [
            'empty_array' => [
                [],
                self::TYPE_EMPTY,
            ],
            'indexed_array' => [
                [
                    1 => 'one',
                    2 => 'two',
                    3 => 'three',
                ],
                self::TYPE_NUMERIC,
            ],
            'assoc_array' => [
                [
                    'one'   => 1,
                    'two'   => 2,
                    'three' => 3,
                ],
                self::TYPE_ASSOC,
            ],
            'mixed_array' => [
                [
                    1     => 'one',
                    'two' => 2,
                    3     => 'three',
                    4     => ['1', '2'],
                ],
                self::TYPE_MIXED,
            ],
        ];
    }

    /**
     * @return array
     */
    public function stringWithSeparatorProvider(): array
    {
        return [
            [
                's,t,r,i,n,g',
                ',',
            ],
            [
                'He|ll|o',
                '|',
            ],
            [
                'Wo;rld',
                ';',
            ],
        ];
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testContains(array $array)
    {
        $element = 2;

        $arrayy = $this->createArrayy($array);
        $isContains = \in_array($element, $array, true);

        static::assertSame($isContains, $arrayy->contains($element));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testContainsKey(array $array)
    {
        $key = 2;

        $arrayy = $this->createArrayy($array);
        $isContainsKey = \array_key_exists($key, $array);

        static::assertSame($isContainsKey, $arrayy->containsKey($key));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testCount(array $array)
    {
        $arrayy = $this->createArrayy($array);
        $count = \count($array);

        static::assertSame($count, $arrayy->count());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testCurrent(array $array)
    {
        $arrayy = $this->createArrayy($array);
        $current = \current($array);
        $array = $arrayy->getArray();

        static::assertSame($current, \current($array));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testDebugReturn(array $array)
    {
        $arrayy = $this->createArrayy($array);
        $printed = \print_r($array, true);

        static::assertSame($printed, \print_r($arrayy->toArray(), true));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testExists(array $array)
    {
        $callable = static function ($value, $key) {
            return $key === 2 && $value === 'two';
        };

        $arrayy = $this->createArrayy($array);
        $isExists = isset($array[2]) && $array[2] === 'two';

        static::assertSame($isExists, $arrayy->exists($callable));
    }

    public function testFind()
    {
        $callable = static function ($value, $key) {
            return $value === 'a' && $key > 2;
        };

        $a = $this->createArrayy(['a', 'b', 'c', 'b', 'a']);
        $found = $a->find($callable);

        static::assertSame('a', $found);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testFirst(array $array)
    {
        $arrayy = $this->createArrayy($array);
        $first = \reset($array);

        if ($first === false) {
            $first = [];
        } else {
            $first = (array) $first;
        }

        static::assertSame($first, $arrayy->firstsMutable()->getArray());
    }

    public function testForEachWithInnerArrayy()
    {
        $arrayy = $this->createArrayy(['foo' => [3 => [33, 34, 35], 2 => [22, 23, 24], 1 => [11, 12, 13]]]);

        foreach ($arrayy as $arrayyInner) {
            static::assertInstanceOf(\Arrayy\Arrayy::class, $arrayyInner);
            static::assertSame([3, 2, 1], $arrayyInner->getKeys()->getArray());

            foreach ($arrayyInner as $arrayyInnerInnerKey => $arrayyInnerInner) {
                static::assertInstanceOf(\Arrayy\Arrayy::class, $arrayyInnerInner);

                if ($arrayyInnerInnerKey === 3) {
                    /* @var $arrayyInnerInner \Arrayy\Arrayy */
                    static::assertSame([33, 34, 35], $arrayyInnerInner->getArray());
                }
            }
        }
    }

    public function testGetIterator()
    {
        $arrayy = $this->createArrayy(['foo', 'bar', 1, null]);

        $result = $arrayy->getIterator();
        static::assertInstanceOf('ArrayIterator', $result);

        $result->next();
        static::assertSame('bar', $result->current());
    }

    public function testGetIteratorWithSubArray()
    {
        $arrayy = $this->createArrayy(['foo' => [3, 2, 1], 'bar' => [1, 2, 3], 1, null]);

        $result = $arrayy->getIterator();
        static::assertInstanceOf(\ArrayIterator::class, $result);
        static::assertInstanceOf(ArrayyIterator::class, $result);

        $result->next();
        static::assertSame([1, 2, 3], $result->current()->getArray());
        static::assertSame([1, 2, 3], $result->current()->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testGetKeys(array $array)
    {
        $arrayy = $this->createArrayy($array);
        $keys = \array_keys($array);

        static::assertSame($keys, $arrayy->keys()->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testGetObject(array $array)
    {
        $arrayy = $this->createArrayy($array);
        $result = \Arrayy\Arrayy::createFromObjectVars($arrayy->getObject())->toArray();

        static::assertSame($array, $result, \print_r($result, true));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testGetRandom(array $array)
    {
        $arrayy = $this->createArrayy($array);
        $value = $arrayy->getRandom()->getArray();

        if (\count($value) > 0) {
            static::assertNotNull($value[0]);

            if (!$value instanceof \Arrayy\Arrayy) {
                /** @noinspection TypeUnsafeArraySearchInspection */
                static::assertContains($value[0], $arrayy->toArray());
            }
        } else {
            static::assertInternalType('array', $value);
        }
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testGetRandomKey(array $array)
    {
        $arrayy = $this->createArrayy($array);

        if ($arrayy->count() > 0) {
            $key = $arrayy->getRandomKey();

            static::assertNotNull($key);
            static::assertArrayHasKey($key, $arrayy->toArray());
        } else {
            static::assertInternalType('array', $arrayy->getArray());
        }
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testGetRandomKeys(array $array)
    {
        $arrayy = $this->createArrayy($array);

        if (\count($array) < 2) {
            static::assertInternalType('array', $arrayy->getArray());
        } else {
            $keys = $arrayy->getRandomKeys(2);

            static::assertCount(2, $keys);
            foreach ($keys as $key) {
                static::assertArrayHasKey($key, $array);
            }
        }
    }

    public function testGetRandomKeysLogicExceptionGivenZero()
    {
        $this->expectException(\RangeException::class);

        $arrayy = $this->createArrayy(['a', 'b', 'c']);
        $arrayy->getRandomKeys(0);
    }

    public function testGetRandomKeysRangeException()
    {
        $this->expectException(\RangeException::class);

        $arrayy = $this->createArrayy(['a', 'b', 'c']);
        $arrayy->getRandomKeys(4);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testGetRandomKeysShouldReturnArray(array $array)
    {
        $arrayy = $this->createArrayy($array);

        if (\count($array) === 0) {
            static::assertInternalType('array', $arrayy->getArray());
        } else {
            $keys = $arrayy->getRandomKeys(\count($array))->getArray();

            static::assertInternalType('array', $keys);
        }
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testGetRandomValueSingle(array $array)
    {
        $arrayy = $this->createArrayy($array);

        if (\count($array) === 0) {
            static::assertInternalType('array', $arrayy->getArray());
        } else {
            $value = $arrayy->getRandomValue();

            if ($value instanceof \Arrayy\Arrayy) {
                $valueFirst = $value->first();
                static::assertTrue((new A($array))->contains($valueFirst, true, true));
            } else {
                static::assertContains($value, $array);
            }
        }
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testGetRandomValues(array $array)
    {
        $arrayy = $this->createArrayy($array);

        if (\count($array) < 2) {
            static::assertInternalType('array', $arrayy->getArray());

            return;
        }

        $values = $arrayy->getRandomValues(2);

        static::assertCount(2, $values);
        foreach ($values as $value) {
            if (!$value instanceof \Arrayy\Arrayy) {
                /** @noinspection TypeUnsafeArraySearchInspection */
                static::assertContains($value, $array);
            }
        }
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testGetRandomValuesSingle(array $array)
    {
        $arrayy = $this->createArrayy($array);

        if (\count($array) === 0) {
            static::assertInternalType('array', $arrayy->getArray());

            return;
        }

        $values = $arrayy->getRandomValues(1)->getArray();

        static::assertCount(1, $values);
        static::assertInternalType('array', $values);
        foreach ($values as $value) {
            if (!$value instanceof \Arrayy\Arrayy) {
                /** @noinspection TypeUnsafeArraySearchInspection */
                static::assertContains($value, $array);
            }
        }
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testIndexOf(array $array)
    {
        $element = 2;

        $arrayy = $this->createArrayy($array);
        $key = \array_search($element, $array, true);

        static::assertSame($key, $arrayy->indexOf($element));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array  $array
     * @param string $type
     */
    public function testIsAssoc(array $array, $type = null)
    {
        $arrayy = $this->createArrayy($array);
        $isAssoc = static::TYPE_ASSOC === $type;

        static::assertSame($isAssoc, $arrayy->isAssoc());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testIsEmpty(array $array)
    {
        $isEmpty = !$array;
        $arrayy = $this->createArrayy($array);

        static::assertSame($isEmpty, $arrayy->isEmpty());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array  $array
     * @param string $type
     */
    public function testIsNumeric(array $array, $type = null)
    {
        $arrayy = $this->createArrayy($array);
        $isNumeric = static::TYPE_NUMERIC === $type;

        static::assertSame($isNumeric, $arrayy->isNumeric());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testKey(array $array)
    {
        $arrayy = $this->createArrayy($array);
        $key = \key($array);
        $array = $arrayy->getArray();

        static::assertSame($key, \key($array));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testLast(array $array)
    {
        $arrayy = $this->createArrayy($array);
        $last = \end($array);
        $result = $arrayy->last();

        if (empty($array)) {
            $last = null;
        }

        static::assertSame($last, $result);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testNext(array $array)
    {
        $arrayy = $this->createArrayy($array)->getArray();
        $next = \next($array);

        static::assertSame($next, \next($arrayy));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testOffsetExists(array $array)
    {
        $offset = 1;
        $isOffsetExists = isset($array[$offset]);

        $arrayy = $this->createArrayy($array);

        static::assertSame($isOffsetExists, $arrayy->offsetExists($offset));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testOffsetGet(array $array)
    {
        $offset = 1;
        $value = $array[$offset] ?? null;

        $arrayy = $this->createArrayy($array);

        static::assertSame($value, $arrayy->offsetGet($offset));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testPrevious(array $array)
    {
        $arrayy = $this->createArrayy($array)->getArray();
        $prev = \prev($array);

        static::assertSame($prev, \prev($arrayy));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testReIndex(array $array)
    {
        $arrayy = $this->createArrayy($array);
        $values = \array_values($array);

        static::assertSame($values, $arrayy->reindex()->getArray());
    }

    public function testReduce()
    {
        $func = static function ($resultArray, $value) {
            if ($value % 2 === 0) {
                $resultArray[] = $value;
            }

            return $resultArray;
        };

        // ---

        $array = [1, 2, 3, 4];
        $arrayy = $this->createArrayy($array);
        $arrayyReduced = $arrayy->reduce($func)->getArray();
        $arrayReduced = (array) \array_reduce($array, $func);

        static::assertSame($arrayReduced, $arrayyReduced);

        // ---

        $generator = static function () {
            return (new A([1, 2, 3, 4]))->getGenerator();
        };

        $arrayy = new A($generator);
        $arrayyReduced = $arrayy->reduce($func)->getArray();
        $arrayReduced = (array) \array_reduce($array, $func);

        static::assertSame($arrayReduced, $arrayyReduced);

        // ---

        $generator = static function () {
            return A::createWithRange(2, 4)->getGenerator();
        };
        $arrayy = A::createFromGeneratorFunction($generator);

        static::assertSame([2, 3, 4], $arrayy->getArray());

        // ---

        $generator = static function () {
            return A::createWithRange(2, 4)->getGenerator();
        };
        $arrayy = A::createFromGeneratorFunction($generator);

        static::assertSame(2, $arrayy->get(0));

        // ---

        $generator = static function () {
            return A::createWithRange(2, 4)->getGenerator();
        };
        $arrayy = A::createFromGeneratorFunction($generator);
        $arrayy->set(0, 99);

        static::assertSame([99, 3, 4], $arrayy->getArray());

        // ---

        $generator = static function () {
            return A::createWithRange(2, 4)->getGenerator();
        };
        $arrayy = A::createFromGeneratorFunction($generator);
        $arrayy->set(0, 99);
        $arrayy = A::createFromGeneratorFunction($generator);

        static::assertSame([2, 3, 4], $arrayy->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testToJson(array $array)
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        $json = \json_encode($array);

        $arrayy = $this->createArrayy($array);

        static::assertSame($json, $arrayy->toJson());
    }

    /**
     * @dataProvider stringWithSeparatorProvider
     *
     * @param string $string
     * @param string $separator
     */
    public function testToString($string, $separator)
    {
        $array = \explode($separator, $string);

        $arrayy = $this->createArrayy($array);
        $resultString = \implode(',', $array);

        static::assertSame($resultString, (string) $arrayy);
        static::assertSame($string, $arrayy->toString($separator));
    }

    /**
     * @param A     $arrayy
     * @param A     $resultArrayy
     * @param array $array
     * @param array $resultArray
     */
    protected function assertImmutable(A $arrayy, A $resultArrayy, array $array, array $resultArray)
    {
        static::assertNotSame($arrayy, $resultArrayy);
        static::assertSame($array, $arrayy->toArray());
        static::assertSame($resultArray, $resultArrayy->toArray());
    }

    /**
     * @param A     $arrayy
     * @param A     $resultArrayy
     * @param array $resultArray
     */
    protected function assertMutable(A $arrayy, A $resultArrayy, array $resultArray)
    {
        static::assertSame($arrayy, $resultArrayy);
        static::assertSame($resultArray, $arrayy->toArray());
        static::assertSame($resultArray, $resultArrayy->toArray());
    }

    // The method list order by ASC

    /**
     * @param array $array
     *
     * @return A
     */
    protected function createArrayy(array $array = []): A
    {
        return new $this->arrayyClassName($array);
    }
}
