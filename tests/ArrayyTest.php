<?php

namespace Arrayy\tests;

use Arrayy\Arrayy;
use Arrayy\Arrayy as A;

/**
 * @internal
 */
final class ArrayyTest extends \PHPUnit\Framework\TestCase
{
    const TYPE_ASSOC = 'assoc';

    const TYPE_EMPTY = 'empty';

    const TYPE_MIXED = 'mixed';

    const TYPE_NUMERIC = 'numeric';

    /**
     * @return array
     */
    public function appendProvider(): array
    {
        return [
            [[], ['foo'], 'foo'],
            [[0 => false], [false, true], true],
            [[0 => true], [true, false], false],
            [[0 => -9], [0 => -9, 1 => -6], -6],
            [[0 => -9, 1 => 1, 2 => 2], [0 => -9, 1 => 1, 2 => 2, 3 => 3], 3],
            [[0 => 1.18, 1 => 1.5], [0 => 1.18, 1 => 1.5, 2 => 1.2], 1.2],
            [['fòô' => 'bàř'], ['fòô' => 'bàř', 0 => 'foo'], 'foo'],
            [
                [3 => 'string', 'foo', 'lall'],
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foobar',
                ],
                'foobar',
            ],
        ];
    }

    /**
     * @return array
     */
    public function appendToEachKeyProvider(): array
    {
        $a = new \stdClass();
        $a->x = 42;

        $b = new \stdClass();
        $b->y = 42;

        $c = new \stdClass();
        $c->x = 43;

        return [
            [[], []],
            [[0 => false], ['foo_0' => false]],
            [[0 => true], ['foo_0' => true]],
            [[0 => -9, 1 => -9], ['foo_0' => -9, 'foo_1' => -9]],
            [[0 => -9, 1 => 1, 2 => 2], ['foo_0' => -9, 'foo_1' => 1, 'foo_2' => 2]],
            [[0 => 1.18, 1 => 1.5], ['foo_0' => 1.18, 'foo_1' => 1.5]],
            [['lall' => 'foo'], ['foo_lall' => 'foo']],
            [
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                ],
                [
                    'foo_3' => 'string',
                    'foo_4' => 'foo',
                    'foo_5' => 'lall',
                    'foo_6' => 'foo',
                ],
            ],
            [
                [
                    2 => 1,
                    3 => 2,
                    4 => 2,
                ],
                [
                    'foo_2' => 1,
                    'foo_3' => 2,
                    'foo_4' => 2,
                ],
            ],
            [
                [
                    $a,
                    $a,
                    $b,
                    $b,
                    $c,
                    $c,
                ],
                [
                    'foo_0' => $a,
                    'foo_1' => $a,
                    'foo_2' => $b,
                    'foo_3' => $b,
                    'foo_4' => $c,
                    'foo_5' => $c,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function appendToEachValueProvider(): array
    {
        $a = new \stdClass();
        $a->x = 42;

        $b = new \stdClass();
        $b->y = 42;

        $c = new \stdClass();
        $c->x = 43;

        return [
            [[], []],
            [[0 => false], [0 => 'foo_']],
            [[0 => true], [0 => 'foo_1']],
            [[0 => -9, 1 => -9], [0 => 'foo_-9', 1 => 'foo_-9']],
            [[0 => -9, 1 => 1, 2 => 2], [0 => 'foo_-9', 1 => 'foo_1', 2 => 'foo_2']],
            [[0 => 1.18, 1 => 1.5], [0 => 'foo_1.18', 1 => 'foo_1.5']],
            [['lall' => 'foo'], ['lall' => 'foo_foo']],
            [
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                ],
                [
                    3 => 'foo_string',
                    4 => 'foo_foo',
                    5 => 'foo_lall',
                    6 => 'foo_foo',
                ],
            ],
            [
                [
                    2 => 1,
                    3 => 2,
                    4 => 2,
                ],
                [
                    2 => 'foo_1',
                    3 => 'foo_2',
                    4 => 'foo_2',
                ],
            ],
            [
                [
                    $a,
                    $a,
                    $b,
                    $b,
                    $c,
                    $c,
                ],
                [
                    $a,
                    $a,
                    $b,
                    $b,
                    $c,
                    $c,
                ],
            ],
        ];
    }

    /**
     * Asserts that a variable is of a Arrayy instance.
     *
     * @param mixed $actual
     */
    public static function assertArrayy($actual)
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        static::assertInstanceOf(\Arrayy\Arrayy::class, $actual);
    }

    /**
     * @return array
     */
    public function averageProvider(): array
    {
        return [
            [[], null, 0],
            [[], 0.0, 0],
            [[0 => false], false, 0.0],
            [[0 => true], true, 1.0],
            [[0 => -9, -8, -7], 1, -8.0],
            [[0 => -9, -8, -7, 1.32], 2, -5.67],
            [[1.18], 1, 1.2],
            [[1.18, 1.89], 1, 1.5],
            [['string', 'foo'], 1, 0.0],
            [['string', 'foo123'], 'foo', 0.0],
        ];
    }

    /**
     * @return array
     */
    public function cleanProvider(): array
    {
        // breaking-change from PHP8
        // -> Implement the negative_array_index RFC: https://github.com/php/php-src/commit/6732028273b109cb342387ab5580c367f629d0ac
        if (\PHP_VERSION_ID >= 80000) {
            return [
                [[], []],
                [[null, false], []],
                [[0 => true], [0 => true]],
                [[0 => -9, 0], [0 => -9]],
                [[-8 => -9, 1, 2 => false], [-8 => -9, -7 => 1]],
                [[0 => 1.18, 1 => false], [0 => 1.18]],
                [['foo' => false, 'foo', 'lall'], ['foo', 'lall']],
            ];
        }

        return [
            [[], []],
            [[null, false], []],
            [[0 => true], [0 => true]],
            [[0 => -9, 0], [0 => -9]],
            [[-8 => -9, 1, 2 => false], [-8 => -9, 0 => 1]],
            [[0 => 1.18, 1 => false], [0 => 1.18]],
            [['foo' => false, 'foo', 'lall'], ['foo', 'lall']],
        ];
    }

    /**
     * @return array
     */
    public function containsCaseInsensitiveProvider(): array
    {
        return [
            [[], null, false],
            [[], false, false],
            [[0 => false], false, true],
            [[0 => true], true, true],
            [[0 => -9], -9, true],
            [[1.18], 1.18, true],
            [[1.18], 1.17, false],
            [['string', '💩'], '💩', true],
            [[' ', 'É'], 'é', true],
            [['string', 'foo'], 'foo', true],
            [['string', 'Foo'], 'foo', true],
            [['string', 'foo123'], 'foo', false],
            [['String', 'foo123'], 'foo', false],
        ];
    }

    /**
     * @return array
     */
    public function containsCaseInsensitiveProviderRecursive(): array
    {
        return [
            [[], null, false],
            [[], false, false],
            [[0 => false], false, true],
            [[0 => true], true, true],
            [[0 => [-9]], -9, true],
            [[1.18], 1.18, true],
            [[[1.18]], 1.17, false],
            [['string', ['💩']], '💩', true],
            [[' ', ['É']], 'é', true],
            [['string', 'foo'], 'foo', true],
            [['string', 'Foo', ['lall']], 'foo', true],
            [['string', 'foo123'], 'foo', false],
            [['String', ['foo123']], 'foo', false],
        ];
    }

    /**
     * @return array
     */
    public function containsProvider(): array
    {
        return [
            [[], null, false],
            [[], false, false],
            [[0 => false], false, true],
            [[0 => true], true, true],
            [[0 => -9], -9, true],
            [[1.18], 1.18, true],
            [[1.18], 1.17, false],
            [['string', 'foo'], 'foo', true],
            [['string', 'foo123'], 'foo', false],
        ];
    }

    /**
     * @return array
     */
    public function containsProviderRecursive(): array
    {
        return [
            [[], null, false],
            [[], false, false],
            [[0 => false], false, true],
            [[0 => true], true, true],
            [[0 => -8, [0 => -9]], -9, true],
            [[1.18], 1.18, true],
            [[1.18], 1.17, false],
            [['string', ['foo']], 'foo', true],
            [['string', ['foo123']], 'foo', false],
        ];
    }

    /**
     * @return array
     */
    public function countProvider(): array
    {
        return [
            [[], 0],
            [[null], 1],
            [[0 => false], 1],
            [[0 => true], 1],
            [[0 => -9, -8, -7], 3],
            [[0 => -9, -8, -7, 1.32], 4],
            [[1.18], 1],
            [[1.18, 1.89], 2],
            [['string', 'foo'], 2],
            [['string', 'foo123'], 2],
        ];
    }

    /**
     * @return array
     */
    public function countProviderRecursive(): array
    {
        return [
            [[], 0],
            [[null], 1],
            [[0 => false], 1],
            [[0 => true], 1],
            [[0 => -9, 1 => [-8, -7]], 4],
            [[0 => -9, -8, -7, 1.32], 4],
            [[[1.18]], 2],
            [[1.18, 1.89], 2],
            [['string', ['foo', 'lall']], 4],
            [['string', 'foo123'], 2],
        ];
    }

    /**
     * @return array
     */
    public function diffProvider(): array
    {
        return [
            [[], [], []],
            [[0 => false], [false], []],
            [[0 => true], [true], []],
            [
                [
                    0 => -9,
                    1 => -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                ],
                [],
            ],
            [
                [
                    0 => -9,
                    1,
                    2,
                ],
                [
                    0 => 2,
                    1 => 1,
                    2 => -9,
                ],
                [],
            ],
            [
                [1.18, 1.5],
                [1.5, 1.18],
                [],
            ],
            [
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar1',
                ],
                [
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                    'foo' => 'bar2',
                ],
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar1',
                ],
            ],
            [
                [
                    3 => 'string',
                    'foo',
                    'lall',
                    'foo',
                ],
                [
                    0 => 'foo',
                    1 => 'lall',
                    2 => 'foo',
                    3 => 'string',
                ],
                [],
            ],
        ];
    }

    /**
     * @return array
     */
    public function diffKeyProvider(): array
    {
        return [
            [[], [], []],
            [[0 => false], [false], []],
            [[0 => true], [true], []],
            [
                [
                    0 => -9,
                    1 => -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                ],
                [],
            ],
            [
                [
                    0 => -9,
                    1,
                    2,
                ],
                [
                    0 => 2,
                    1 => 1,
                    2 => -9,
                ],
                [],
            ],
            [
                [1.18, 1.5],
                [1.5, 1.18],
                [],
            ],
            [
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar1',
                ],
                [
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                    'foo' => 'bar2',
                ],
                [
                    1 => 'one',
                    2 => 'two',
                ],
            ],
            [
                [
                    3 => 'string',
                    'foo',
                    'lall',
                    'foo',
                ],
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                ],
                [],
            ],
        ];
    }

    /**
     * @return array
     */
    public function diffKeyAndValueProvider(): array
    {
        return [
            [[], [], []],
            [[0 => false], [false], []],
            [[0 => true], [true], []],
            [
                [
                    0 => -9,
                    1 => -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                ],
                [],
            ],
            [
                [
                    0 => -9,
                    1,
                    2,
                ],
                [
                    0 => 2,
                    1 => 1,
                    2 => -9,
                ],
                [
                    0 => -9,
                    2 => 2,
                ],
            ],
            [
                [1.18, 1.5],
                [1.5, 1.18],
                [1.18, 1.5],
            ],
            [
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar1',
                ],
                [
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                    'foo' => 'bar2',
                ],
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar1',
                ],
            ],
            [
                [
                    3 => 'string',
                    'foo',
                    'lall',
                    'foo',
                ],
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                ],
                [],
            ],
        ];
    }

    /**
     * @return array
     */
    public function diffReverseProvider(): array
    {
        return [
            [[], [], []],
            [[0 => false], [false], []],
            [[0 => true], [true], []],
            [
                [
                    0 => -9,
                    -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                ],
                [],
            ],
            [
                [
                    0 => -9,
                    1,
                    2,
                ],
                [
                    0 => 2,
                    1 => 1,
                    2 => -9,
                ],
                [],
            ],
            [
                [1.18, 1.5],
                [1.5, 1.18],
                [],
            ],
            [
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar1',
                ],
                [
                    'foo' => 'bar2',
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                ],
                [
                    'foo' => 'bar2',
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                ],
            ],
            [
                [
                    3 => 'string',
                    'foo',
                    'lall',
                    'foo',
                ],
                [
                    0 => 'foo',
                    1 => 'lall',
                    2 => 'foo',
                    3 => 'string',
                ],
                [],
            ],
        ];
    }

    /**
     * @return array
     */
    public function fillWithDefaultsProvider(): array
    {
        return [
            [[], 2, 'lall', ['lall', 'lall']],
            [[], 1, false, [false]],
            [[], 0, null, []],
            [[0 => true], 3, 'lall', [0 => true, 1 => 'lall', 2 => 'lall']],
            [[0 => -9, 1 => 1, 2 => 2], 3, 'lall', [0 => -9, 1 => 1, 2 => 2]],
            [[0 => 1.18], 3, 'lall', [0 => 1.18, 1 => 'lall', 2 => 'lall']],
            [['string', 'foo', 'lall'], 3, 'lall', ['string', 'foo', 'lall']],
        ];
    }

    /**
     * @return array
     */
    public function findProvider(): array
    {
        return [
            [[], [null], false],
            [[], [false], false],
            [[0 => true], true, true],
            [[0 => -9], -9, -9],
            [[0 => -9, 1 => 1, 2 => 2], false, false],
            [[0 => 1.18], 1.18, 1.18],
            [['string', 'foo', 'lall'], 'foo', 'foo'],
        ];
    }

    /**
     * @return array
     */
    public function firstProvider(): array
    {
        return [
            [[], null],
            [[null, false], null],
            [[0 => true], true],
            [[0 => -9, 0], -9],
            [[-8 => -9, 1, 2 => false], -9],
            [[1.18, false], 1.18],
            [['foo' => false, 'foo', 'lall'], false],
            [[-8 => -9, 1, 2 => false], -9],
            [[1.18, false], 1.18],
            [['foo' => false, 'foo', 'lall'], false],
            [[2 => 'foo', 3 => 'bar', 4 => 'lall'], 'foo'],
        ];
    }

    /**
     * @return array
     */
    public function firstsProvider(): array
    {
        return [
            [[], []],
            [[null, false], []],
            [[0 => true], [true]],
            [[0 => -9, 0], [-9]],
            [[-8 => -9, 1, 2 => false], [-9]],
            [[1.18, false], [1.18]],
            [['foo' => false, 'foo', 'lall'], [false]],
            [[-8 => -9, 1, 2 => false], [], 0],
            [[1.18, false], [1.18], 1],
            [['foo' => false, 'foo', 'lall'], ['foo' => false, 'foo'], 2],
            [[2 => 'foo', 3 => 'bar', 4 => 'lall'], [0 => 'foo', 1 => 'bar'], 2],
        ];
    }

    /**
     * @return array
     */
    public function getProvider(): array
    {
        return [
            [null, [0 => null], 0],
            [false, [0 => false], 0],
            [null, [0 => true], 1],
            [null, [0 => false], 1],
            [true, [0 => true], 0],
            [1, [0 => -9, 1 => 1, 2 => 0, 3 => false], 1],
            [1.18, [0 => 1.18], 0],
            [null, [0 => ' string  ', 1 => 'foo'], 'foo'],
            ['foo', [0 => ' string  ', 'foo' => 'foo'], 'foo'],
        ];
    }

    /**
     * @return array
     */
    public function hasProvider(): array
    {
        return [
            [false, [], 0],
            [true, [0 => null], 0],
            [true, [0 => null], 0],
            [true, [0 => false], 0],
            [false, [0 => true], 1],
            [false, [0 => false], 1],
            [true, [0 => true], 0],
            [true, [0 => -9, 1 => 1, 2 => 0, 3 => false], 1],
            [true, [0 => 1.18], 0],
            [false, [' string  ', 'foo'], 'foo'],
            [true, [' string  ', 'foo' => 'foo'], 'foo'],
        ];
    }

    /**
     * @return array
     */
    public function implodeKeysProvider(): array
    {
        return [
            [[], ''],
            [[0 => false], '0'],
            [[1 => true], '1'],
            [[-9 => -9], '-9', '|'],
            [[-9 => -9, 1 => 1, 2 => 2], '-9|1|2', '|'],
            [[1 => 1.18], '1'],
            [['string' => 'string', 'foo' => 'foo', 0 => 'lall'], 'string,foo,0', ','],
            [
                [
                    'string1' => 'string2',
                    0         => 'foo',
                    9         => ['9_1' => 'lall', '9_2' => 'foo', 'string1' => 'string3'],
                ],
                'string1,0,9,9_1,9_2,string1',
            ],
        ];
    }

    /**
     * @return array
     */
    public function implodeProvider(): array
    {
        return [
            [[], ''],
            [[0 => false], ''],
            [[0 => true], '1'],
            [[0 => -9], '-9', '|'],
            [[0 => -9, 1 => 1, 2 => 2], '-9|1|2', '|'],
            [[0 => 1.18], '1.18'],
            [[3 => 'string', 'foo', 'lall'], 'string,foo,lall', ','],
            [
                [
                    3 => 'string',
                    'foo',
                    9 => ['lall', 'foo'],
                ],
                'string,foo,lall,foo',
            ],
        ];
    }

    /**
     * @return array
     */
    public function initialProvider(): array
    {
        return [
            [[], []],
            [[null, false], [null]],
            [[0 => true], []],
            [[0 => -9, 0], [-9]],
            [[-8 => -9, 1, 2 => false], [-9, 1]],
            [[1.18, false], [1.18]],
            [['foo' => false, 'foo', 'lall'], ['foo' => false, 0 => 'foo']],
            [[-8 => -9, 1, 2 => false], [0 => -9, 1 => 1, 2 => false], 0],
            [[1.18, false], [1.18], 1],
            [['foo' => false, 'foo', 'lall'], ['foo' => false], 2],
            [[2 => 'foo', 3 => 'bar', 4 => 'lall'], [0 => 'foo'], 2],
            [[2 => 'foo', 3 => 'bar', 4 => 'lall'], [0 => 'foo', 1 => 'bar'], 1],
        ];
    }

    /**
     * @return array
     */
    public function isAssocProvider(): array
    {
        return [
            [[], false],
            [[0 => true], false],
            [[0 => -9, 0], false],
            [[-8 => -9, 1, 2 => false], false],
            [[-8 => -9, 1, 2 => false], false],
            [[1.18, false], false],
            [[0 => 1, 1 => 2, 2 => 3, 3 => 4], false],
            [[1, 2, 3, 4], false],
            [[0, 1, 2, 3], false],
            [['foo' => false, 'foo1' => 'lall'], true],
        ];
    }

    /**
     * @return array
     */
    public function isMultiArrayProvider(): array
    {
        return [
            [[0 => true], false],
            [[0 => -9, 0], false],
            [[-8 => -9, 1, 2 => false], false],
            [[-8 => -9, 1, 2 => false], false],
            [[1.18, false], false],
            [[0 => 1, 1 => 2, 2 => 3, 3 => 4], false],
            [[1, 2, 3, 4], false],
            [[0, 1, 2, 3], false],
            [['foo' => false, 'foo', 'lall'], false],
            [['foo' => false, 'foo', 'lall'], false],
            [['foo' => false, 'foo', 'lall'], false],
            [['foo' => ['foo', 'lall']], true],
            [['foo' => ['foo', 'lall'], 'bar' => ['foo', 'lall']], true],
        ];
    }

    /**
     * @return array
     */
    public function lastProvider(): array
    {
        return [
            [[], []],
            [[0 => null, 1 => null], [0 => null]],
            [[0 => null, 1 => false], [0 => false]],
            [[0 => true], [0 => true]],
            [[0 => -9, 1 => 0], [0 => 0]],
            [[-8 => -9, 1, 2 => false], [false]],
            [[1.18, false], [false]],
            [['foo' => false, 'foo', 'lall'], ['lall']],
            [[-8 => -9, 1, 2 => false], [-9, 1, false], 0],
            [[1.18, false], [false], 1],
            [['foo' => false, 'foo', 'lall'], ['foo', 'lall'], 2],
            [[2 => 'foo', 3 => 'bar', 4 => 'lall'], [0 => 'bar', 1 => 'lall'], 2],
            [[2 => 'foo', 3 => 'bar', 4 => 'lall'], [0 => 'lall']],
        ];
    }

    /**
     * @return array
     */
    public function matchesAnyProvider(): array
    {
        return [
            [[], [0 => null], false],
            [[], [0 => false], false],
            [[0 => 'string', 1 => 'foo', 2 => 'lall'], [1 => 'str', 2 => 'bar'], false],
            [[0 => null], [0 => null], true],
            [[0 => false], [0 => false], true],
            [[0 => null], [], false],
            [[0 => false], [], false],
            [[0 => true], [0 => true], true],
            [[0 => -9], [0 => -9, 1 => 1, 2 => 0, 3 => false], true],
            [[0 => -9, 1 => 1, 2 => 2], [0 => -9, 1 => 1, 2 => 0, false], true],
            [[0 => 1.18], [0 => 1.18], true],
            [[0 => 'string', 1 => 'foo', 2 => 'lall'], [1 => 'string', 2 => 'foo'], true],
            [[0 => 'string', 1 => 'foo', 2 => 'lall'], [1 => 'foo'], true],
        ];
    }

    /**
     * @return array
     */
    public function matchesProvider(): array
    {
        return [
            [[], [0 => null], false],
            [[], [0 => false], false],
            [[0 => null], [0 => null], true],
            [[0 => false], [], false],
            [[0 => true], [], false],
            [[0 => false], [0 => false], true],
            [[0 => true], [0 => true], true],
            [[0 => -9], [0 => -9, 1 => 1, 2 => 0, false], true],
            [[0 => -9, 1 => 1, 2 => 2], [0 => -9, 1 => 1, 2 => 0, 3 => false], false],
            [[0 => 1.18], [0 => 1.18], true],
            [[0 => 'string', 1 => 'foo', 2 => 'lall'], [0 => 'string', 1 => 'foo'], false],
            [[0 => 'string', 1 => 'foo', 2 => 'lall'], [0 => 'str', 1 => 'foo', 2 => 'lall'], false],
            [[0 => 'string', 1 => 'foo', 2 => 'lall'], [0 => 'String', 1 => 'foo', 2 => 'lall'], false],
            [[0 => 'string', 1 => 'foo', 2 => 'lall'], [0 => 'string', 1 => 'foo', 2 => 'lall'], true],
        ];
    }

    /**
     * @return array
     */
    public function maxProvider(): array
    {
        return [
            [[], false],
            [[null], null],
            [[0 => false], false],
            [[0 => true], true],
            [[0 => -9, -8, -7], -7],
            [[0 => -9, -8, -7, 1.32], 1.32],
            [[1.18], 1.18],
            [[1.18, 1.89], 1.89],
            [['string', 'foo'], 'string'],
            [['string', 'zoom'], 'zoom'],
        ];
    }

    /**
     * @return array
     */
    public function mergeAppendKeepIndexProvider(): array
    {
        return [
            [[], [], []],
            [[0 => false], [false], [false]],
            [[0 => true], [true], [true]],
            [
                [
                    0 => -9,
                    -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                ],
            ],
            [
                [
                    0 => -9,
                    1,
                    2,
                ],
                [
                    0 => 2,
                    1 => 1,
                    2 => -9,
                ],
                [
                    0 => 2,
                    1 => 1,
                    2 => -9,
                ],
            ],
            [
                [1.18, 1.5],
                [1.5, 1.18],
                [1.5, 1.18],
            ],
            [
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar1',
                ],
                [
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                    'foo' => 'bar2',
                ],
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar2',
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                ],
            ],
            [
                [
                    3 => 'string',
                    'foo',
                    'lall',
                    'foo',
                ],
                [
                    0 => 'foo',
                    1 => 'lall',
                    2 => 'foo',
                    3 => 'string',
                ],
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                    0 => 'foo',
                    1 => 'lall',
                    2 => 'foo',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function mergeAppendNewIndexProvider(): array
    {
        return [
            [[], [], []],
            [[0 => false], [false], [false, false]],
            [[0 => true], [true], [true, true]],
            [
                [
                    0 => -9,
                    -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                    2 => -9,
                    3 => -9,
                ],
            ],
            [
                [
                    0 => -9,
                    1 => 1,
                    2 => 2,
                ],
                [
                    0 => 2,
                    1 => 1,
                    2 => -9,
                ],
                [
                    0 => -9,
                    1 => 1,
                    2 => 2,
                    3 => 2,
                    4 => 1,
                    5 => -9,
                ],
            ],
            [
                [1.18, 1.5],
                [1.5, 1.18],
                [1.18, 1.5, 1.5, 1.18],
            ],
            [
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar1',
                ],
                [
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                    'foo' => 'bar2',
                ],
                [
                    0     => 'one',
                    1     => 'two',
                    'foo' => 'bar2',
                    2     => 'three',
                    3     => 'four',
                    4     => 'six',
                ],
            ],
            [
                [
                    3 => 'string',
                    'foo',
                    'lall',
                    'foo',
                ],
                [
                    0 => 'foo',
                    1 => 'lall',
                    2 => 'foo',
                    3 => 'string',
                ],
                [
                    0 => 'string',
                    1 => 'foo',
                    2 => 'lall',
                    3 => 'foo',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                    7 => 'string',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function mergePrependKeepIndexProvider(): array
    {
        return [
            [[], [], []],
            [[0 => false], [false], [false]],
            [[0 => true], [true], [true]],
            [
                [
                    0 => -9,
                    -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                ],
            ],
            [
                [
                    0 => -9,
                    1,
                    2,
                ],
                [
                    0 => 2,
                    1 => 1,
                    2 => -9,
                ],
                [
                    0 => -9,
                    1 => 1,
                    2 => 2,
                ],
            ],
            [
                [1.18, 1.5],
                [1.5, 1.18],
                [1.18, 1.5],
            ],
            [
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar1',
                ],
                [
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                    'foo' => 'bar2',
                ],
                [
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                    'foo' => 'bar1',
                    1     => 'one',
                    2     => 'two',
                ],
            ],
            [
                [
                    3 => 'string',
                    'foo',
                    'lall',
                    'foo',
                ],
                [
                    0 => 'foo',
                    1 => 'lall',
                    2 => 'foo',
                    3 => 'string',
                ],
                [
                    0 => 'foo',
                    1 => 'lall',
                    2 => 'foo',
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function mergePrependNewIndexProvider(): array
    {
        return [
            [[], [], []],
            [[0 => false], [false], [false, false]],
            [[0 => true], [true], [true, true]],
            [
                [
                    0 => -9,
                    1 => -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                ],
                [
                    0 => -9,
                    1 => -9,
                    2 => -9,
                    3 => -9,
                ],
            ],
            [
                [
                    0 => -9,
                    1,
                    2,
                ],
                [
                    0 => 2,
                    1 => 1,
                    2 => -9,
                ],
                [
                    0 => 2,
                    1 => 1,
                    2 => -9,
                    3 => -9,
                    4 => 1,
                    5 => 2,
                ],
            ],
            [
                [1.18, 1.5],
                [1.5, 1.18],
                [1.5, 1.18, 1.18, 1.5],
            ],
            [
                [
                    1     => 'one',
                    2     => 'two',
                    'foo' => 'bar1',
                ],
                [
                    3     => 'three',
                    4     => 'four',
                    6     => 'six',
                    'foo' => 'bar2',
                ],
                [
                    0     => 'three',
                    1     => 'four',
                    2     => 'six',
                    'foo' => 'bar1',
                    3     => 'one',
                    4     => 'two',
                ],
            ],
            [
                [
                    3 => 'string',
                    'foo',
                    'lall',
                    'foo',
                ],
                [
                    0 => 'foo',
                    1 => 'lall',
                    2 => 'foo',
                    3 => 'string',
                ],
                [
                    0 => 'foo',
                    1 => 'lall',
                    2 => 'foo',
                    3 => 'string',
                    4 => 'string',
                    5 => 'foo',
                    6 => 'lall',
                    7 => 'foo',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function minProvider(): array
    {
        return [
            [[], false],
            [[null], null],
            [[0 => false], false],
            [[0 => true], true],
            [[0 => -9, -8, -7], -9],
            [[0 => -9, -8, -7, 1.32], -9],
            [[1.18], 1.18],
            [[1.18, 1.89], 1.18],
            [['string', 'foo'], 'foo'],
            [['string', 'zoom'], 'string'],
        ];
    }

    /**
     * @return array
     */
    public function prependProvider(): array
    {
        return [
            [[], ['foo'], 'foo'],
            [[0 => false], [true, false], true],
            [[0 => true], [false, true], false],
            [[0 => -9], [-6, -9], -6],
            [[0 => -9, 1, 2], [3, -9, 1, 2], 3],
            [[1.18, 1.5], [1.2, 1.18, 1.5], 1.2],
            [
                [3 => 'string', 'foo', 'lall'],
                [
                    0 => 'foobar',
                    1 => 'string',
                    2 => 'foo',
                    3 => 'lall',
                ],
                'foobar',
            ],
        ];
    }

    /**
     * @return array
     */
    public function prependToEachKeyProvider(): array
    {
        $a = new \stdClass();
        $a->x = 42;

        $b = new \stdClass();
        $b->y = 42;

        $c = new \stdClass();
        $c->x = 43;

        return [
            [[], []],
            [[0 => false], ['0_foo' => false]],
            [[0 => true], ['0_foo' => true]],
            [[0 => -9, 1 => -9], ['0_foo' => -9, '1_foo' => -9]],
            [[0 => -9, 1 => 1, 2 => 2], ['0_foo' => -9, '1_foo' => 1, '2_foo' => 2]],
            [[0 => 1.18, 1 => 1.5], ['0_foo' => 1.18, '1_foo' => 1.5]],
            [['lall' => 'foo'], ['lall_foo' => 'foo']],
            [
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                ],
                [
                    '3_foo' => 'string',
                    '4_foo' => 'foo',
                    '5_foo' => 'lall',
                    '6_foo' => 'foo',
                ],
            ],
            [
                [
                    2 => 1,
                    3 => 2,
                    4 => 2,
                ],
                [
                    '2_foo' => 1,
                    '3_foo' => 2,
                    '4_foo' => 2,
                ],
            ],
            [
                [
                    $a,
                    $a,
                    $b,
                    $b,
                    $c,
                    $c,
                ],
                [
                    '0_foo' => $a,
                    '1_foo' => $a,
                    '2_foo' => $b,
                    '3_foo' => $b,
                    '4_foo' => $c,
                    '5_foo' => $c,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function prependToEachValueProvider(): array
    {
        $a = new \stdClass();
        $a->x = 42;

        $b = new \stdClass();
        $b->y = 42;

        $c = new \stdClass();
        $c->x = 43;

        return [
            [[], []],
            [[0 => false], [0 => '_foo']],
            [[0 => true], [0 => '1_foo']],
            [[0 => -9, 1 => -9], [0 => '-9_foo', 1 => '-9_foo']],
            [[0 => -9, 1 => 1, 2 => 2], [0 => '-9_foo', 1 => '1_foo', 2 => '2_foo']],
            [[0 => 1.18, 1 => 1.5], [0 => '1.18_foo', 1 => '1.5_foo']],
            [['lall' => 'foo'], ['lall' => 'foo_foo']],
            [
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                ],
                [
                    3 => 'string_foo',
                    4 => 'foo_foo',
                    5 => 'lall_foo',
                    6 => 'foo_foo',
                ],
            ],
            [
                [
                    2 => 1,
                    3 => 2,
                    4 => 2,
                ],
                [
                    2 => '1_foo',
                    3 => '2_foo',
                    4 => '2_foo',
                ],
            ],
            [
                [
                    $a,
                    $a,
                    $b,
                    $b,
                    $c,
                    $c,
                ],
                [
                    $a,
                    $a,
                    $b,
                    $b,
                    $c,
                    $c,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function randomProvider(): array
    {
        return [
            [[0 => null]],
            [[0 => true]],
            [[0 => -9, 0]],
            [[-8 => -9, 1, 2 => false]],
            [[-8 => -9, 1, 2 => false], 2],
            [[1.18, false]],
            [['foo' => false, 'foo', 'lall']],
            [['foo' => false, 'foo', 'lall'], 1],
            [['foo' => false, 'foo', 'lall'], 3],
        ];
    }

    /**
     * @return array
     */
    public function randomWeightedProvider(): array
    {
        return [
            [[0 => true]],
            [[0 => -9, 0]],
            [[-8 => -9, 1, 2 => false]],
            [[-8 => -9, 1, 2 => false], 2],
            [[1.18, false]],
            [['foo' => false, 'foo', 'lall']],
            [['foo' => false, 'foo', 'lall'], 1],
            [['foo' => false, 'foo', 'lall'], 3],
        ];
    }

    /**
     * @return array
     */
    public function removeFirstProvider(): array
    {
        return [
            [[], []],
            [[0 => false], []],
            [[0 => true], []],
            [[0 => -9], []],
            [[0 => -9, 1, 2], [1, 2]],
            [[1.18, 1.5], [1.5]],
            [[3 => 'string', 'foo', 'lall'], ['foo', 'lall']],
        ];
    }

    /**
     * @return array
     */
    public function removeLastProvider(): array
    {
        return [
            [[], []],
            [[0 => false], []],
            [[0 => true], []],
            [[0 => -9], []],
            [[0 => -9, 1, 2], [-9, 1]],
            [[1.18, 1.5], [1.18]],
            [[3 => 'string', 'foo', 'lall'], [3 => 'string', 4 => 'foo']],
        ];
    }

    /**
     * @return array
     */
    public function removeProvider(): array
    {
        return [
            [[null], 0, []],
            [[false], 0, []],
            [[true], 1, [true]],
            [[false], 1, [false]],
            [[true], 0, []],
            [[-9, 1, 0, false], 1, [0 => -9, 2 => 0, 3 => false]],
            [[-9, 1, 0, false], [1, 2, 99], [0 => -9, 3 => false]],
            [[1.18], 0, []],
            [[' string  ', 'foo'], 'foo', [' string  ', 'foo']],
            [[' string  ', 'foo' => 'foo'], 'foo', [' string  ']],
        ];
    }

    /**
     * @return array
     */
    public function removeV2Provider(): array
    {
        return [
            [[], [], null],
            [[0 => false], [], 0],
            [[0 => true], [], 0],
            [[0 => -9], [0 => -9], -1],
            [[0 => -9, 1, 2], [0 => -9, 2 => 2], 1],
            [[1.18, 1.5], [1 => 1.5], 0],
            [[3 => 'string', 'foo', 'lall'], [3 => 'string', 'foo'], 5],
        ];
    }

    /**
     * @return array
     */
    public function removeValueProvider(): array
    {
        return [
            [[], [], ''],
            [[0 => false], [], false],
            [[0 => true], [], true],
            [[0 => -9], [], -9],
            [[0 => -9, 1, 2], [-9, 1], 2],
            [[1.18, 1.5], [1.18], 1.5],
            [[3 => 'string', 'foo', 'lall'], [3 => 'string', 4 => 'foo'], 'lall'],
            [['string', 'foo', 'lall'], [0 => 'string', 1 => 'foo'], 'lall'],
        ];
    }

    /**
     * @return array
     */
    public function restProvider(): array
    {
        return [
            [[], []],
            [[null, false], [false]],
            [[0 => -9, 0], [0]],
            [[-8 => -9, 1, 2 => false], [0 => 1, 1 => false]],
            [[1.18, false], [false]],
            [['foo' => false, 'foo', 'lall'], [0 => 'foo', 1 => 'lall']],
            [[-8 => -9, 1, 2 => false], [0 => -9, 1 => 1, 2 => false], 0],
            [[1.18, false], [false], 1],
            [['foo' => false, 'foo', 'lall'], ['lall'], 2],
            [[2 => 'foo', 3 => 'bar', 4 => 'lall'], [0 => 'lall'], 2],
            [[2 => 'foo', 3 => 'bar', 4 => 'lall'], [0 => 'bar', 1 => 'lall'], 1],
        ];
    }

    /**
     * @return array
     */
    public function reverseProvider(): array
    {
        return [
            [[], []],
            [[0 => false], [false]],
            [[0 => true], [true]],
            [[0 => -9, -9], [0 => -9, 1 => -9]],
            [[0 => -9, 1, 2], [0 => 2, 1 => 1, 2 => -9]],
            [[1.18, 1.5], [1.5, 1.18]],
            [
                [3 => 'string', 'foo', 'lall', 'foo'],
                [
                    0 => 'foo',
                    1 => 'lall',
                    2 => 'foo',
                    3 => 'string',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function searchIndexProvider(): array
    {
        return [
            [false, [null], ''],
            [false, [false], true],
            [0, [false], false],
            [0, [true], true],
            [2, [-9, 1, 0, false], -0],
            [0, [1.18], 1.18],
            [1, ['string', 'foo'], 'foo'],
        ];
    }

    /**
     * @return array
     */
    public function searchValueProvider(): array
    {
        return [
            [[], [0 => null], ''],
            [[], [0 => false], 1],
            [[0 => null], [0 => null], 0],
            [[0 => false], [0 => false], false],
            [[0 => false], [0 => false], 0],
            [[0 => true], [0 => true], 0],
            [[0 => 1], [0 => -9, 1 => 1, 2 => 0, false], 1],
            [[0 => 1.18], [0 => 1.18], 0],
            [[0 => 'foo'], [0 => 'string', 1 => 'foo'], 1],
        ];
    }

    /**
     * @return array
     */
    public function setAndGetProvider(): array
    {
        return [
            [[0 => null], 0, null],
            [[0 => 'foo'], 0, 'foo'],
            [[0 => false], 0, false],
            [[0 => true], 1, 'foo'],
            [[0 => false], 1, 'foo'],
            [[0 => true], 0, true],
            [[0 => -9, 1 => 1, 2 => 0, 3 => false], 1, 1],
            [[0 => 1.18], 0, 1.18],
            [[0 => ' string  ', 1 => 'foo'], 'foo', 'lall'],
            [[0 => ' string  ', 'foo' => 'foo'], 'foo', 'foo'],
        ];
    }

    /**
     * @return array
     */
    public function setProvider(): array
    {
        return [
            [[0 => null], 0, null],
            [[0 => null], 0, 'foo'],
            [[0 => false], 0, true],
            [[0 => true], 1, 'foo'],
            [[0 => false], 1, 'foo'],
            [[0 => true], 0, 'foo'],
            [[0 => -9, 1 => 1, 2 => 0, 3 => false], 1, 'foo'],
            [[0 => 1.18], 0, 1],
            [[0 => ' string  ', 1 => 'foo'], 'foo', 'lall'],
            [[0 => ' string  ', 'foo' => 'foo'], 'foo', 'lall'],
        ];
    }

    /**
     * @return array
     */
    public function simpleArrayProvider(): array
    {
        return [
            'empty_array' => [
                [],
                0,
                self::TYPE_EMPTY,
            ],
            'indexed_array' => [
                [
                    1 => 'one',
                    2 => 'two',
                    3 => 'three',
                ],
                3,
                self::TYPE_NUMERIC,
            ],
            'assoc_array' => [
                [
                    'one'   => 1,
                    'two'   => 2,
                    'three' => 3,
                ],
                3,
                self::TYPE_ASSOC,
            ],
            'mixed_array' => [
                [
                    1     => 'one',
                    'two' => 2,
                    3     => 'three',
                ],
                3,
                self::TYPE_MIXED,
            ],
        ];
    }

    /**
     * @return array
     */
    public function reduceDimensionProvider(): array
    {
        return [
            [[], [], false],
            [[], [], false],
            [[0 => false], [0 => false], false],
            [[0 => true], [0 => true], false],
            [[0 => -9], [-9], false],
            [[0 => -9, 1, 2], [-9, 1, 2], false],
            [[1 => 2, 0 => 1], [2, 1], false],
            [[1.18], [1.18], false],
            [[3 => 'string', 'foo', 'lall'], ['string', 'foo', 'lall'], false],
            [[3 => 'string', [3 => 'foo', 'lall'], ['lall', 3 => 'string']], ['string', 'foo', 'lall', 'lall', 'string'], false],
            [[3 => 'string', [3 => 'foo', 'lall'], ['lall', 3 => 'string']], ['string', 'foo', 'lall'], true],
            [[3 => 'string', [3 => 'foo', 'lall'], ['lall', 3 => 'string']], ['string', 'foo', 'lall', 'lall', 'string'], false],
            [[3 => 'string', [3 => 'foo', 'lall'], ['lall', 3 => ['string', 'lall3']]], ['string', 'foo', 'lall', 'lall3'], true],
            [[3 => 'string', [3 => 'foo', 'lall'], ['lall', 3 => ['string', 'lall3']]], ['string', 'foo', 'lall', 'lall', 'string', 'lall3'], false],
        ];
    }

    /**
     * @return array
     */
    public function sortKeysProvider(): array
    {
        return [
            [[], []],
            [[], []],
            [[0 => false], [false]],
            [[0 => true], [true]],
            [[0 => -9], [-9], 'ASC'],
            [[0 => -9, 1, 2], [-9, 1, 2], 'asc'],
            [[1 => 2, 0 => 1], [1, 2], 'asc'],
            [[1.18], [1.18], 'ASC'],
            [[3 => 'string', 'foo', 'lall'], [5 => 'lall', 4 => 'foo', 3 => 'string'], 'desc'],
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
            [
                'Wo;rld',
                null,
            ],
        ];
    }

    public function testAdd()
    {
        $array = [1, 2];
        $arrayy = new A($array);
        $resultArrayy = $arrayy->add(3);
        $array[] = 3;

        self::assertMutable($arrayy, $resultArrayy, $array);
    }

    public function testAppendImmutableYield()
    {
        $array = [1, 2];
        $arrayy = new A($array);
        $resultArrayy = $arrayy->appendImmutable(3);
        $arrayResult = $array;
        $arrayResult[] = 3;

        self::assertImmutable($arrayy, $resultArrayy, $array, $arrayResult);
    }

    public function testPrependImmutableYield()
    {
        $array = [3 => 1, 2];
        $arrayy = new A($array);
        $resultArrayy = $arrayy->prependImmutable(3);
        $arrayResult = [3, 3 => 1, 2];

        self::assertImmutable($arrayy, $resultArrayy, $array, $arrayResult);
    }

    /**
     * @dataProvider appendProvider()
     *
     * @param array $array
     * @param array $result
     * @param mixed $value
     */
    public function testAppend($array, $result, $value)
    {
        $arrayy = A::create($array)->append($value);

        static::assertSame($result, $arrayy->getArray());
    }

    /**
     * @dataProvider appendToEachKeyProvider
     *
     * @param array $array
     * @param array $result
     */
    public function testAppendToEachKey($array, $result)
    {
        $resultTmp = A::create($array)->appendToEachKey('foo_');

        static::assertSame($result, $resultTmp->toArray());
    }

    /**
     * @dataProvider appendToEachValueProvider
     *
     * @param array $array
     * @param array $result
     */
    public function testAppendToEachValue($array, $result)
    {
        $resultTmp = A::create($array)->appendToEachValue('foo_');

        static::assertSame($result, $resultTmp->toArray());
    }

    /**
     * @dataProvider averageProvider()
     *
     * @param array     $array
     * @param mixed     $value
     * @param float|int $expected
     */
    public function testAverage($array, $value, $expected)
    {
        $arrayy = new A($array);

        static::assertSame($expected, $arrayy->average($value), 'tested: ' . $value);
    }

    public function testCanDoSomethingAtEachValue()
    {
        $arrayy = A::create(['foo', 'bar' => 'bis']);

        $closure = static function ($value, $key) {
            echo $key . ':' . $value . ':';
        };

        $arrayy->at($closure);
        $result = '0:foo:bar:bis:';
        $this->expectOutputString($result);
    }

    public function testGetValue()
    {
        $arrayy = (['bàř' => 'bàř']);

        static::assertSame('bàř', $arrayy['bàř']);
        static::assertSame('bàř', $arrayy['bàř']);
    }

    public function testCanGetIntersectionOfTwoArrays()
    {
        $a = ['foo', 'bar'];
        $b = ['bar', 'baz'];
        $array = A::create($a)->intersection($b, false);
        static::assertSame([0 => 'bar'], $array->getArray());
    }

    public function testCanGetIntersectionOfTwoArraysKeepKeys()
    {
        $a = ['foo', 'bar'];
        $b = ['bar', 'baz'];
        $array = A::create($a)->intersection($b, true);
        static::assertSame([1 => 'bar'], $array->getArray());
    }

    public function testCanGroupValues()
    {
        $under = A::create(\range(1, 5))->group(
            static function ($value) {
                return $value % 2 === 0;
            }
        );
        $matcher = [
            [1, 3, 5],
            [2, 4],
        ];
        static::assertSame($matcher, $under->getArray());
        static::assertSame($matcher, $under->getAll());
    }

    public function testCanGroupValuesWithNonExistingKey()
    {
        static::assertSame([], A::create(\range(1, 5))->group('unknown', true)->getArray());

        static::assertSame([], A::create(\range(1, 5))->group('unknown', false)->getArray());
    }

    public function testCanGroupValuesWithSavingKeys()
    {
        $grouper = static function ($value) {
            return $value % 2 === 0;
        };
        $under = A::create(\range(1, 5))->group($grouper, true);
        $matcher = [
            [0 => 1, 2 => 3, 4 => 5],
            [1 => 2, 3 => 4],
        ];
        static::assertSame($matcher, $under->getArray());
    }

    public function testCanGroupValuesWithSavingKeysViaYield()
    {
        $grouper = static function ($value) {
            return $value % 2 === 0;
        };
        $under = A::create(\range(1, 5))->group($grouper, true);
        $matcher = [
            [0 => 1, 2 => 3, 4 => 5],
            [1 => 2, 3 => 4],
        ];

        static::assertSame(2, $under->count());

        $result = [];
        foreach ($under->getGenerator() as $key => $value) {
            $result[$key] = $value;
        }
        static::assertSame($matcher, $result);
    }

    public function testCanIndexBy()
    {
        $array = [
            ['name' => 'moe', 'age' => 40],
            ['name' => 'larry', 'age' => 50],
            ['name' => 'curly', 'age' => 60],
        ];
        $expected = [
            40 => ['name' => 'moe', 'age' => 40],
            50 => ['name' => 'larry', 'age' => 50],
            60 => ['name' => 'curly', 'age' => 60],
        ];
        static::assertSame($expected, A::create($array)->indexBy('age')->getArray());
    }

    public function testCanIndexByViaYield()
    {
        $array = [
            ['name' => 'moe', 'age' => 40],
            ['name' => 'larry', 'age' => 50],
            ['name' => 'curly', 'age' => 60],
        ];
        $expected = [
            40 => ['name' => 'moe', 'age' => 40],
            50 => ['name' => 'larry', 'age' => 50],
            60 => ['name' => 'curly', 'age' => 60],
        ];

        $result = [];
        foreach (A::create($array)->indexBy('age')->getGenerator() as $key => $value) {
            $result[$key] = $value;
        }
        static::assertSame($expected, $result);
    }

    public function testChangeKeyCase()
    {
        // upper

        $array = [
            'foo'   => 'a',
            1       => 'b',
            0       => 'c',
            'Foo'   => 'd',
            'FOO'   => 'e',
            'ΣΣΣ'   => 'f',
            'Κόσμε' => 'g',
        ];

        $arrayy = A::create($array)->changeKeyCase(\CASE_UPPER);
        $result = $arrayy->getArray();

        $expected = [
            'FOO'   => 'e',
            1       => 'b',
            0       => 'c',
            'ΣΣΣ'   => 'f',
            'ΚΌΣΜΕ' => 'g',
        ];

        static::assertSame($expected, $result);

        // lower

        $array = [
            'foo'   => 'a',
            1       => 'b',
            0       => 'c',
            'Foo'   => 'd',
            'FOO'   => 'e',
            'ΣΣΣ'   => 'f',
            'Κόσμε' => 'g',
        ];

        $arrayy = A::create($array)->changeKeyCase(\CASE_LOWER);
        $result = $arrayy->getArray();

        $expected = [
            'foo'   => 'e',
            1       => 'b',
            0       => 'c',
            'σσσ'   => 'f',
            'κόσμε' => 'g',
        ];

        static::assertSame($expected, $result);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testChunk(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->chunk(2);
        $resultArray = \array_chunk($array, 2);

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);

        // ---

        $arrayy = new A([-9, -8, -7, 1.32]);
        $result = $arrayy->chunk(2);

        static::assertSame([[-9, -8], [-7, 1.32]], $result->getArray());
    }

    /**
     * @dataProvider cleanProvider()
     *
     * @param array $array
     * @param array $result
     */
    public function testClean($array, $result)
    {
        $arrayy = A::create($array);

        static::assertSame($result, $arrayy->clean()->getArray(), 'tested: ' . \print_r($array, true));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testClear(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->clear();

        self::assertMutable($arrayy, $resultArrayy, []);
    }

    public function testColumn()
    {
        $rows = [0 => ['id' => '3', 'title' => 'Foo', 'date' => '2013-03-25']];

        static::assertSame(A::create($rows)->getArray(), A::create($rows)->getColumn(null, 0)->getArray());
        static::assertSame(A::create($rows)->getArray(), A::create($rows)->getColumn(null)->getArray());
        static::assertSame(A::create($rows)->getArray(), A::create($rows)->getColumn()->getArray());

        $expected = [
            0 => '3',
        ];
        static::assertSame(A::create($expected)->getArray(), A::create($rows)->getColumn('id')->getArray());

        // ---

        $rows = [
            456 => ['id' => '3', 'title' => 'Foo', 'date' => '2013-03-25'],
            457 => ['id' => '5', 'title' => 'Bar', 'date' => '2012-05-20'],
        ];

        $expected = [
            3 => 'Foo',
            5 => 'Bar',
        ];
        static::assertSame(A::create($expected)->getArray(), A::create($rows)->getColumn('title', 'id')->getArray());

        $expected = [
            0 => 'Foo',
            1 => 'Bar',
        ];
        static::assertSame(A::create($expected)->getArray(), A::create($rows)->getColumn('title', null)->getArray());

        // pass null as second parameter to get back all columns indexed by third parameter
        $expected1 = [
            3 => ['id' => '3', 'title' => 'Foo', 'date' => '2013-03-25'],
            5 => ['id' => '5', 'title' => 'Bar', 'date' => '2012-05-20'],
        ];
        static::assertSame(A::create($expected1)->getArray(), A::create($rows)->getColumn(null, 'id')->getArray());

        // pass null as second parameter and bogus third param to get back zero-indexed array of all columns
        $expected2 = [
            ['id' => '3', 'title' => 'Foo', 'date' => '2013-03-25'],
            ['id' => '5', 'title' => 'Bar', 'date' => '2012-05-20'],
        ];
        static::assertSame(A::create($expected2)->getArray(), A::create($rows)->getColumn(null, 'foo')->getArray());

        // pass null as second parameter and no third param to get back array_values(input) (same as $expected2)
        static::assertSame(A::create($expected2)->getArray(), A::create($rows)->getColumn(null)->getArray());
    }

    public function testCompareToPhpArray()
    {
        $initArray = [
            'fruit' => [
                'orange',
                'avocado',
                'cherry',
            ],
        ];

        $arrayy = Arrayy::create($initArray)
            ->appendArrayValues(['pear', 'avocado'], 'fruit')
            ->get('fruit')
            ->uniqueKeepIndex()
            ->walk(
                static function (&$value) {
                    $value .= '*';
                },
                true
            )
            ->filter(
                static function ($value) {
                    return \strpos($value, 'a') !== false;
                }
            );

        static::assertSame([0 => 'orange*', 1 => 'avocado*', 3 => 'pear*'], $arrayy->getArray());
    }

    public function testConstruct()
    {
        $testArray = ['foo bar', 'UTF-8'];
        $arrayy = new A($testArray);
        self::assertArrayy($arrayy);
        static::assertSame('foo bar,UTF-8', (string) $arrayy);
    }

    public function testConstructWithArray()
    {
        $this->expectException(\InvalidArgumentException::class);

        new A(new \stdClass());
        static::fail('Expecting exception when the constructor is passed an array');
    }

    /**
     * @dataProvider containsProvider()
     *
     * @param array $array
     * @param mixed $value
     * @param bool  $expected
     */
    public function testContains($array, $value, $expected)
    {
        $arrayy = new A($array);

        static::assertSame($expected, $arrayy->contains($value));
        static::assertSame($expected, $arrayy->containsValue($value)); // alias
        static::assertSame($expected, $arrayy->containsValueRecursive($value)); // alias
    }

    /**
     * @dataProvider containsCaseInsensitiveProvider()
     *
     * @param array $array
     * @param mixed $value
     * @param bool  $expected
     */
    public function testContainsCaseInsensitive($array, $value, $expected)
    {
        $arrayy = new A($array);

        static::assertSame($expected, $arrayy->containsCaseInsensitive($value));
        static::assertSame($expected, $arrayy->containsCaseInsensitive($value, true));
    }

    /**
     * @dataProvider containsCaseInsensitiveProviderRecursive()
     *
     * @param array $array
     * @param mixed $value
     * @param bool  $expected
     */
    public function testContainsCaseInsensitiveRecursive($array, $value, $expected)
    {
        $arrayy = new A($array);

        static::assertSame($expected, $arrayy->containsCaseInsensitive($value, true), 'tested: ' . \print_r($value, true));
    }

    public function testContainsKeys()
    {
        static::assertTrue(A::create(['a' => 0, 'b' => 1, 'd' => ['c' => 2]])->containsKeys(['a', 'b', 'c'], true));
        static::assertFalse(A::create(['a' => 0, 'b' => 1, 'd' => ['c' => 2]])->containsKeys(['a', 'b', 'c'], false));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, 'd' => ['c' => 2]])->containsKeys(['a', 'b'], false));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, 'c' => 2])->containsKeys(['a', 'b'], true));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, 'c' => 2])->containsKeys(['a', 'b']));
        static::assertFalse(A::create(['a' => 0, 'b' => 1, 'd' => 2])->containsKeys(['a', 'b', 'c']));
        static::assertFalse(A::create(['a' => 0, 'b' => 1, 'e' => ['d' => 2]])->containsKeys(['a', 'b', 'c'], true));
        static::assertTrue(A::create([])->containsKeys([]));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, 'c' => 2])->containsKeys([]));
        static::assertFalse(A::create([])->containsKeys(['a', 'b', 'c']));
    }

    public function testPull()
    {
        static::assertSame([0, 1, null], A::create(['a' => 0, 'b' => 1, 'd' => ['c' => 2]])->pull(['a', 'b', 'foo']));
        static::assertSame([0, 1], A::create(['a' => 0, 'b' => 1, 'd' => ['c' => 2]])->pull(['a', 'b']));
        static::assertSame([], A::create([])->pull([]));
        static::assertSame([], A::create(['a' => 0, 'b' => 1, 'd' => ['c' => 2]])->pull([]));
        static::assertSame(0, A::create(['a' => 0, 'b' => 1, 'd' => ['c' => 2]])->pull('a'));
        static::assertSame([null, null, null], A::create([])->pull(['a', 'b', 'c']));

        // ---

        $test = A::create(['a' => 0, 'b' => 1, 'd' => ['c' => 2]]);
        static::assertSame([0, 1, null], $test->pull(['a', 'b', 'foo']));
        static::assertSame(['d' => ['c' => 2]], $test->getArray());
    }

    public function testContainsKeysRecursive()
    {
        static::assertTrue(A::create(['a' => 0, 'b' => 1, ['c' => 2]])->containsKeysRecursive(['a', 'b', 'c']));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, ['c' => 2]])->containsKeysRecursive(['a', 'b']));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, ['c' => [2]]])->containsKeysRecursive(['a', 'b', 'c']));
        static::assertFalse(A::create(['a' => 0, 'b' => 1, ['d' => 2]])->containsKeysRecursive(['a', 'b', 'c']));
        static::assertTrue(A::create([])->containsKeysRecursive([]));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, ['c' => 2]])->containsKeysRecursive([]));
        static::assertFalse(A::create([])->containsKeysRecursive(['a', 'b', 'c']));

        // ---

        static::assertTrue(A::create(['a' => 0, 'b' => 1, ['c' => 2]])->containsKeys(['a', 'b', 'c'], true));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, ['c' => 2]])->containsKeys(['a', 'b'], true));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, ['c' => [2]]])->containsKeys(['a', 'b', 'c'], true));
        static::assertFalse(A::create(['a' => 0, 'b' => 1, ['d' => 2]])->containsKeys(['a', 'b', 'c'], true));
        static::assertTrue(A::create([])->containsKeys([], true));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, ['c' => 2]])->containsKeys([], true));
        static::assertFalse(A::create([])->containsKeys(['a', 'b', 'c'], true));

        // ---

        static::assertTrue(A::create(['a' => 0, 'b' => 1, 'c' => 2])->containsKeys(['a', 'b'], true));
        static::assertFalse(A::create(['a' => 0, 'b' => 1, 'd' => 2])->containsKeys(['a', 'b', 'c'], true));
        static::assertTrue(A::create([])->containsKeys([], true));
        static::assertTrue(A::create(['a' => 0, 'b' => 1, 'c' => 2])->containsKeys([], true));
        static::assertFalse(A::create([])->containsKeys(['a', 'b', 'c'], true));
    }

    /**
     * @dataProvider containsProviderRecursive()
     *
     * @param array $array
     * @param mixed $value
     * @param bool  $expected
     */
    public function testContainsRecursive($array, $value, $expected)
    {
        $arrayy = new A($array);

        static::assertSame($expected, $arrayy->contains($value, true));
        static::assertSame($expected, $arrayy->containsValueRecursive($value)); // alias
    }

    public function testContainsValues()
    {
        static::assertTrue(A::create(['a', 'b', 'c'])->containsValues(['a', 'b']));
        static::assertFalse(A::create(['a', 'b', 'd'])->containsValues(['a', 'b', 'c']));
        static::assertTrue(A::create([])->containsValues([]));
        static::assertTrue(A::create(['a', 'b', 'c'])->containsValues([]));
        static::assertFalse(A::create([])->containsValues(['a', 'b', 'c']));
    }

    /**
     * @dataProvider countProvider()
     *
     * @param array $array
     * @param int   $expected
     */
    public function testCount($array, $expected)
    {
        $arrayy = new A($array);

        /** @noinspection PhpUnitTestsInspection */
        static::assertCount($expected, $arrayy);
        static::assertSame($expected, $arrayy->count());
        static::assertSame($expected, $arrayy->size());
        static::assertSame($expected, $arrayy->length());
    }

    /**
     * @dataProvider countProviderRecursive()
     *
     * @param array $array
     * @param int   $expected
     */
    public function testCountRecursive($array, $expected)
    {
        $arrayy = new A($array);

        static::assertSame($expected, $arrayy->count(\COUNT_RECURSIVE));
        static::assertSame($expected, $arrayy->size(\COUNT_RECURSIVE));
        static::assertSame($expected, $arrayy->sizeRecursive());
        static::assertSame($expected, $arrayy->length(\COUNT_RECURSIVE));
    }

    public function testCountValues()
    {
        $array = ['foo', 'lall', 'bar', 'bar', 'foo', 'bar'];
        $arrayy = new A($array);

        $expected = ['foo' => 2, 'lall' => 1, 'bar' => 3];
        static::assertSame($expected, $arrayy->countValues()->getArray());
    }

    public function testCreateByReference()
    {
        $testArray = ['foo bar', 'UTF-8'];
        $arrayy = new A();
        $arrayy->createByReference($testArray);
        $arrayy['foo'] = 'bar';

        static::assertSame(['foo bar', 'UTF-8', 'foo' => 'bar'], $testArray);
        static::assertSame($testArray, $arrayy->toArray());
    }

    public function testCreateFromJsonApiResponse()
    {
        $str = '
        {
            "type": "person",
            "location": {
                "primary": { 
                    "city":"bakersfield",
                    "state":"ca"
                }
            }
        }';

        $arrayy = A::createFromJson($str);

        $expected = [
            'type'     => 'person',
            'location' => [
                'primary' => [
                    'city'  => 'bakersfield',
                    'state' => 'ca',
                ],
            ],
        ];

        // test JSON -> Array
        static::assertSame($expected, $arrayy->getArray());

        // test Array -> JSON
        static::assertSame(
            \str_replace([' ', "\n", "\n\r", "\r"], '', $str),
            $arrayy->toJson()
        );
    }

    public function testCreateFromJson()
    {
        $str = '
    {"employees":[
      {"firstName":"John", "lastName":"Doe"},
      {"firstName":"Anna", "lastName":"Smith"},
      {"firstName":"Peter", "lastName":"Jones"}
    ]}';

        $arrayy = A::createFromJson($str);

        $expected = [
            'employees' => [
                0 => [
                    'firstName' => 'John',
                    'lastName'  => 'Doe',
                ],
                1 => [
                    'firstName' => 'Anna',
                    'lastName'  => 'Smith',
                ],
                2 => [
                    'firstName' => 'Peter',
                    'lastName'  => 'Jones',
                ],
            ],
        ];

        // test JSON -> Array
        static::assertSame($expected, $arrayy->getArray());

        // test Array -> JSON
        static::assertSame(
            \str_replace([' ', "\n", "\n\r", "\r"], '', $str),
            $arrayy->toJson()
        );
    }

    /**
     * @dataProvider stringWithSeparatorProvider
     *
     * @param string      $string
     * @param string|null $separator
     */
    public function testCreateFromString($string, $separator)
    {
        if ($separator !== null) {
            $array = \explode($separator, $string);
        } else {
            $array = [$string];
        }
        \assert(\is_array($array));

        $arrayy = new A($array);

        $resultArrayy = A::createFromString($string, $separator);

        self::assertImmutable($arrayy, $resultArrayy, $array, $array);
    }

    public function testCreateFromTraversableImmutable()
    {
        $array = ['recipe' => 'pancakes', 'egg', 'milk', 'flour'];
        $iterator = new \ArrayIterator($array);
        $arrayy = new A($array);

        $resultArrayy = A::createFromTraversableImmutable($iterator);

        self::assertImmutable($arrayy, $resultArrayy, $array, $array);
    }

    public function testCreateFromStringRegEx()
    {
        $str = '
    [2016-03-02 02:37:39] WARN  main : router: error in file-name: jquery.min.map
    [2016-03-02 02:39:07] WARN  main : router: error in file-name: jquery.min.map
    [2016-03-02 02:44:01] WARN  main : router: error in file-name: jquery.min.map
    [2016-03-02 02:45:21] WARN  main : router: error in file-name: jquery.min.map
    ';

        $arrayy = A::createFromString($str, null, '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/');

        $expected = [
            '[2016-03-02 02:37:39] WARN  main : router: error in file-name: jquery.min.map',
            '[2016-03-02 02:39:07] WARN  main : router: error in file-name: jquery.min.map',
            '[2016-03-02 02:44:01] WARN  main : router: error in file-name: jquery.min.map',
            '[2016-03-02 02:45:21] WARN  main : router: error in file-name: jquery.min.map',
        ];

        // test String -> Array
        static::assertSame($expected, $arrayy->getArray());
    }

    public function testCreateFromStringSimple()
    {
        $str = 'John, Doe, Anna, Smith';

        $arrayy = A::createFromString($str, ',');

        $expected = ['John', 'Doe', 'Anna', 'Smith'];

        // test String -> Array
        static::assertSame($expected, $arrayy->getArray());
    }

    public function testCreateWithRange()
    {
        $arrayy1 = A::createWithRange(2, 7);
        $array1 = \range(2, 7);
        $arrayy2 = A::createWithRange('d', 'h');
        $array2 = \range('d', 'h');
        $arrayy3 = A::createWithRange(22, 11, 2);
        $array3 = \range(22, 11, 2);
        $arrayy4 = A::createWithRange('y', 'k', 2);
        $array4 = \range('y', 'k', 2);

        static::assertSame($array1, $arrayy1->toArray());
        static::assertSame($array2, $arrayy2->toArray());
        static::assertSame($array3, $arrayy3->toArray());
        static::assertSame($array4, $arrayy4->toArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testCustomSort(array $array)
    {
        $callable = static function ($a, $b) {
            if ($a === $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        };

        $arrayy = new A($array);
        $resultArrayy = $arrayy->customSortValues($callable);
        $resultArray = $array;
        \usort($resultArray, $callable);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testCustomSortImmutable(array $array)
    {
        $callable = static function ($a, $b) {
            if ($a === $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        };

        $arrayy = new A($array);
        $resultArrayy = $arrayy->customSortValuesImmutable($callable);
        $resultArray = $array;
        \usort($resultArray, $callable);

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testCustomSortKeys(array $array)
    {
        $callable = static function ($a, $b) {
            if ($a === $b) {
                return 0;
            }

            return ($a > $b) ? -1 : 1;
        };

        $arrayy = new A($array);
        $resultArrayy = $arrayy->customSortKeys($callable);
        $resultArray = $array;
        \uksort($resultArray, $callable);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);

        // ---

        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->uksort($callable);

        self::assertMutable($arrayy, $resultArrayy, $resultArrayV2->getArray());
    }

    public function testCustomSortKeysSimple()
    {
        $callable = static function ($a, $b) {
            if ($a === $b) {
                return 0;
            }

            return ($a > $b) ? 1 : -1;
        };

        // customSortKeys

        $input = [
            'three' => 3,
            'one'   => 1,
            'two'   => 2,
        ];
        $arrayy = new A($input);
        $resultArrayy = $arrayy->customSortKeys($callable);
        $expected = [
            'one'   => 1,
            'three' => 3,
            'two'   => 2,
        ];
        static::assertSame($expected, $resultArrayy->getArray());

        // uksort

        $input = [
            'three' => 3,
            'one'   => 1,
            'two'   => 2,
        ];
        $arrayy = new A($input);
        $arrayy->uksort($callable);
        $expected = [
            'one'   => 1,
            'three' => 3,
            'two'   => 2,
        ];
        static::assertSame($expected, $arrayy->getArray());

        // ---

        $input = [
            'three' => 3,
            'one'   => 1,
            'two'   => 2,
        ];
        $arrayy = new A($input);
        $resultArrayy = $arrayy->uksortImmutable($callable);
        $expected = [
            'one'   => 1,
            'three' => 3,
            'two'   => 2,
        ];
        static::assertSame($expected, $resultArrayy->getArray());
        self::assertImmutable($arrayy, $resultArrayy, $input, $resultArrayy->getArray());
    }

    public function testCustomSortValuesByDateTimeObject()
    {
        $birthDates = [
            ['Lucienne Adkisson', \date_create('2017-10-17')],
            ['Sheryll Nestle', \date_create('2017-02-16')],
            ['Tim Pittman', \date_create('2017-07-29')],
            ['Elmer Letts', \date_create('2017-12-01')],
            ['Gino Massengale', \date_create('2017-04-16')],
            ['Jeremy Wiggs', \date_create('2017-09-17')],
            ['Julian Bulloch', \date_create('2017-06 -21')],
            ['Joella Hinshaw', \date_create('2017-06-25')],
            ['Mamie Burchill', \date_create('2017-11-15')],
            ['Constance Segers', \date_create('2017-06-30')],
            ['Jessy Pinkmann', \date_create('2017-09-11')],
            ['Dudley Currie', \date_create('2017-02-10')],
        ];

        $birthDatesAraayy = new Arrayy($birthDates);

        $currentDate = new \DateTime('2017-09-11');
        $format = 'Y-m-d H:i:s';

        /**
         * sort by date - helper-function
         *
         * @param array $a
         * @param array $b
         *
         * @return int
         */
        $closureSort = static function ($a, $b) use ($format) {
            /* @var \DateTime $aDate  */
            /* @var \DateTime $bDate */
            $aDate = $a[1];
            $bDate = $b[1];

            if ($aDate->format($format) === $bDate->format($format)) {
                return 0;
            }

            return $aDate->format($format) > $bDate->format($format) ? -1 : 1;
        };

        /**
         * reduce by date - helper-function
         *
         * @param array $resultArray
         * @param array $value
         *
         * @return array
         */
        $closureReduce = static function ($resultArray, $value) use ($currentDate) {
            /* @var $valueDate \DateTime */
            $valueDate = $value[1];
            $valueDateInterval = $currentDate->diff($valueDate);

            if ($valueDateInterval->format('%R%a') >= 0) {
                $resultArray['thisYear'][] = $value;
            } else {
                $value[1] = $valueDate->modify('+1 year');

                $resultArray['nextYear'][] = $value;
            }

            return $resultArray;
        };

        //
        // reduce && sort the array
        //

        /* @var $resultMatch Arrayy|Arrayy[] */
        $resultMatch = $birthDatesAraayy->reduce($closureReduce);

        $thisYear = $resultMatch['thisYear']->customSortValues($closureSort);
        $nextYear = $resultMatch['nextYear']->customSortValues($closureSort);

        $resultMatch = $nextYear->reverse()->mergePrependNewIndex($thisYear->reverse()->getArray());

        //
        // check the result
        //

        $result = [];
        foreach ($resultMatch->getArray() as $key => $value) {
            $result[$key][] = $value[0];
            $result[$key][] = $value[1]->format('Y-m-d');
        }

        static::assertSame(
            [
                ['Jessy Pinkmann', \date_create('2017-09-11')->format('Y-m-d')],
                ['Jeremy Wiggs', \date_create('2017-09-17')->format('Y-m-d')],
                ['Lucienne Adkisson', \date_create('2017-10-17')->format('Y-m-d')],
                ['Mamie Burchill', \date_create('2017-11-15')->format('Y-m-d')],
                ['Elmer Letts', \date_create('2017-12-01')->format('Y-m-d')],
                ['Dudley Currie', \date_create('2018-02-10')->format('Y-m-d')],
                ['Sheryll Nestle', \date_create('2018-02-16')->format('Y-m-d')],
                ['Gino Massengale', \date_create('2018-04-16')->format('Y-m-d')],
                ['Julian Bulloch', \date_create('2018-06 -21')->format('Y-m-d')],
                ['Joella Hinshaw', \date_create('2018-06-25')->format('Y-m-d')],
                ['Constance Segers', \date_create('2018-06-30')->format('Y-m-d')],
                ['Tim Pittman', \date_create('2018-07-29')->format('Y-m-d')],
            ],
            $result
        );
    }

    /**
     * @dataProvider diffProvider()
     *
     * @param array $array
     * @param array $arrayNew
     * @param array $result
     */
    public function testDiff($array, $arrayNew, $result)
    {
        $arrayy = A::create($array)->diff($arrayNew);

        static::assertSame($result, $arrayy->getArray(), 'tested: ' . \print_r($array, true));
    }

    /**
     * @dataProvider diffKeyProvider()
     *
     * @param array $array
     * @param array $arrayNew
     * @param array $result
     */
    public function testDiffKey($array, $arrayNew, $result)
    {
        $arrayy = A::create($array)->diffKey($arrayNew);

        static::assertSame($result, $arrayy->getArray(), 'tested: ' . \print_r($array, true));
    }

    /**
     * @dataProvider diffKeyAndValueProvider()
     *
     * @param array $array
     * @param array $arrayNew
     * @param array $result
     */
    public function testDiffKeyAndValue($array, $arrayNew, $result)
    {
        $arrayy = A::create($array)->diffKeyAndValue($arrayNew);

        static::assertSame($result, $arrayy->getArray(), 'tested: ' . \print_r($array, true));
    }

    public function testDiffRecursive()
    {
        $testArray1 = [
            'test1' => ['lall'],
            'test2' => ['lall'],
        ];

        $testArray2 = [
            'test1' => ['lall'],
            'test2' => ['lall'],
        ];

        static::assertSame(
            (new A([]))->getArray(),
            A::create($testArray1)->diffRecursive($testArray2)->getArray()
        );

        $testArray1 = [
            'test1' => ['lall'],
            'test3' => ['lall'],
        ];

        $testArray2 = [
            'test1' => ['lall'],
            'test2' => ['lall'],
        ];

        static::assertSame(
            (new A(['test3' => ['lall']]))->getArray(),
            A::create($testArray1)->diffRecursive($testArray2)->getArray()
        );

        $testArray1 = [
            'test1' => ['lall'],
            'test2' => ['lall'],
        ];

        $testArray2 = [
            'test1' => ['lall'],
            'test2' => ['foo'],
        ];

        static::assertSame(
            (new A(['test2' => ['lall']]))->getArray(),
            A::create($testArray1)->diffRecursive($testArray2)->getArray()
        );

        $testArray1 = [1 => [1 => 1], 2 => [2 => 2]];
        $testArray2 = [1 => [1 => 1]];

        static::assertSame(
            (new A([2 => [2 => 2]]))->getArray(),
            A::create($testArray1)->diffRecursive($testArray2)->getArray()
        );

        $testArray1 = [1 => [1 => 1], 2 => new A([2 => [2 => 2]])];
        $testArray2 = [1 => [1 => 1], 2 => [2 => [2 => 2]]];

        static::assertSame(
            (new A([]))->getArray(),
            A::create($testArray1)->diffRecursive($testArray2)->getArray()
        );
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testDiffWith(array $array)
    {
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($array);
        $resultArrayy = $arrayy->diff($secondArray);
        $resultArray = \array_diff($array, $secondArray);

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
    }

    public function testDivide()
    {
        $arrayy = new A(['id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01']);
        $arrayyResult = new A(['id', 'name', 'group', 'value', 'when', 999, 'flux', '', 6868, '2015-01-01']);

        static::assertSame($arrayyResult->toString(), $arrayy->divide()->toString());
    }

    public function testEach()
    {
        $array = [1 => 'bar', 'foo' => 'foo'];
        $arrayy = A::create($array);

        $closure = static function ($value, $key) {
            return $key . ':' . $value;
        };

        $under = $arrayy->each($closure);
        $result = [1 => '1:bar', 'foo' => 'foo:foo'];
        static::assertSame($result, $under->getArray(), 'tested: ' . \print_r($array, true));
    }

    public function testEmptyConstruct()
    {
        $arrayy = new A();
        self::assertArrayy($arrayy);
        static::assertSame('', (string) $arrayy);
    }

    public function testExchangeArray()
    {
        $input = [
            'three' => 3,
            'one'   => 1,
            'two'   => 2,
        ];
        $arrayy = new A($input);
        $arrayy->exchangeArray('foo');

        static::assertSame(['foo'], $arrayy->getArray());
        static::assertSame(['foo'], $arrayy->getArrayCopy());
    }

    /**
     * @dataProvider fillWithDefaultsProvider()
     *
     * @param array $array
     * @param int   $num
     * @param mixed $default
     * @param array $expected
     */
    public function testFillWithDefaults($array, $num, $default, $expected)
    {
        $arrayy = new A($array);

        $result = $arrayy->fillWithDefaults($num, $default);

        // test for immutable
        static::assertNotSame($result, $arrayy);

        // test for logic
        static::assertSame($expected, $result->getArray());
    }

    public function testFillWithDefaultsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $arrayy = new A([1, 2, 3]);

        $arrayy->fillWithDefaults(-1);
    }

    public function testIsEqual()
    {
        $arrayy = new A([1, 2, 3]);

        static::assertTrue($arrayy->isEqual([1, 2, 3]));
        static::assertFalse($arrayy->isEqual([3, 2, 3]));
        static::assertFalse($arrayy->isEqual([3, 2, 1]));
    }

    public function testFilter()
    {
        if (!\defined('ARRAY_FILTER_USE_BOTH')) {
            \define('ARRAY_FILTER_USE_BOTH', 1);
        }

        if (!\defined('ARRAY_FILTER_USE_KEY')) {
            \define('ARRAY_FILTER_USE_KEY', 2);
        }

        $under = A::create([1, 2, 3, 4])->filter(
            static function ($value) {
                return $value % 2 !== 0;
            }
        );
        static::assertSame([0 => 1, 2 => 3], $under->getArray());

        // ---

        $under = A::create([1, 2, 3, 4])->filter();
        static::assertSame([1, 2, 3, 4], $under->getArray());

        $under = A::create([0 => 1, 1 => false, 2 => 3, 3 => 4])->filter();
        static::assertSame([0 => 1, 2 => 3, 3 => 4], $under->getArray());

        // ---

        $under = A::create([0 => 1, 1 => 2, 2 => 3, 3 => 4])->filter(
            static function ($value) {
                return $value % 2 !== 0;
            },
            \ARRAY_FILTER_USE_KEY
        );
        static::assertSame([1 => 2, 3 => 4], $under->getArray());

        // ---

        $under = A::create([0 => 1, 1 => 2, 2 => 3, 3 => 4, 7 => 7])->filter(
            /** @phpstan-ignore-next-line | FP from phpstan?  */
            static function ($key, $value): bool {
                return ($value % 2 !== 0) && ($key & 2 !== 0);
            },
            \ARRAY_FILTER_USE_BOTH
        );
        static::assertSame([7 => 7], $under->getArray());

        // ---

        $under = A::create([0 => 1, 1 => 2, 2 => 3, 3 => 4, 7 => 7])->filter(
            static function ($value) {
                return $value % 2 !== 0;
            },
            0
        );
        static::assertSame([0 => 1, 2 => 3, 7 => 7], $under->getArray());
    }

    public function testFilterBy()
    {
        $a = [
            ['id' => 123, 'name' => 'foo', 'group' => 'primary', 'value' => 123456, 'when' => '2014-01-01'],
            ['id' => 456, 'name' => 'bar', 'group' => 'primary', 'value' => 1468, 'when' => '2014-07-15'],
            ['id' => 499, 'name' => 'baz', 'group' => 'secondary', 'value' => 2365, 'when' => '2014-08-23'],
            ['id' => 789, 'name' => 'ter', 'group' => 'primary', 'value' => 2468, 'when' => '2010-03-01'],
            ['id' => 888, 'name' => 'qux', 'value' => 6868, 'when' => '2015-01-01'],
            ['id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01'],
        ];

        $arrayy = new A($a);

        $b = $arrayy->filterBy('name', 'baz');
        static::assertCount(1, $b);
        /** @noinspection OffsetOperationsInspection */
        static::assertSame(2365, $b[0]['value']);

        $b = $arrayy->filterBy('name', ['baz']);
        static::assertCount(1, $b);
        /** @noinspection OffsetOperationsInspection */
        static::assertSame(2365, $b[0]['value']);

        $c = $arrayy->filterBy('value', 2468);
        static::assertCount(1, $c);
        /** @noinspection OffsetOperationsInspection */
        static::assertSame('primary', $c[0]['group']);

        $d = $arrayy->filterBy('group', 'primary');
        static::assertCount(3, $d);

        $e = $arrayy->filterBy('value', 2000, 'lt');
        static::assertCount(1, $e);
        /** @noinspection OffsetOperationsInspection */
        static::assertSame(1468, $e[0]['value']);

        $e = $arrayy->filterBy('value', [2468, 2365], 'contains');
        static::assertCount(2, $e);

        // ---

        $e = $arrayy->findBy('value', [2468, 2365], 'contains');
        static::assertCount(2, $e);

        $e = $arrayy->findBy('value', 2468);
        static::assertCount(1, $e);
    }

    /**
     * @dataProvider findProvider()
     *
     * @param array       $array
     * @param mixed       $search
     * @param false|mixed $result
     */
    public function testFind($array, $search, $result)
    {
        $closure = static function ($value) use ($search) {
            return $value === $search;
        };

        $arrayy = A::create($array);
        $resultMatch = $arrayy->find($closure);

        static::assertSame($result, $resultMatch, 'tested:' . \print_r($array, true));
    }

    /**
     * @dataProvider firstProvider()
     *
     * @param array $array
     * @param array $result
     */
    public function testFirst($array, $result)
    {
        $arrayy = A::create($array);

        static::assertSame($result, $arrayy->first());
    }

    /**
     * @dataProvider firstsProvider()
     *
     * @param array $array
     * @param array $result
     * @param null  $take
     */
    public function testFirsts($array, $result, $take = null)
    {
        $arrayy = A::create($array);
        $resultNew = $arrayy->firstsImmutable($take);
        static::assertSame($result, $resultNew->getArray(), 'tested:' . \print_r($array, true));
        static::assertNotSame($arrayy, $resultNew);

        $arrayy = A::create($array);
        $resultNew = $arrayy->firstsMutable($take);
        static::assertSame($result, $resultNew->getArray());
        static::assertSame($arrayy, $resultNew);
    }

    public function testFlip()
    {
        $testArray = [0 => 'foo', 2 => 'bar', 4 => 'lall'];
        $arrayy = A::create($testArray)->flip();

        $expected = ['foo' => 0, 'bar' => 2, 'lall' => 4];
        static::assertSame($expected, $arrayy->getArray());
    }

    public function testForEach()
    {
        $arrayy = new A([1 => 'foo bar', 'öäü']);

        foreach ($arrayy as $key => $value) {
            if ($key === 1) {
                static::assertSame('foo bar', $arrayy[$key]);
            } elseif ($key === 2) {
                static::assertSame('öäü', $arrayy[$key]);
            }
        }
    }

    public function testGet()
    {
        $arrayy = new A(['foo bar', 'öäü']);
        self::assertArrayy($arrayy);
        static::assertSame('öäü', $arrayy[1]);
    }

    /**
     * @dataProvider getProvider()
     *
     * @param mixed $expected
     * @param array $array
     * @param mixed $key
     */
    public function testGetV2($expected, $array, $key)
    {
        $arrayy = new A($array);
        static::assertSame($expected, $arrayy->get($key), 'tested:' . \print_r($array, true));
    }

    public function testGetViaDotNotation()
    {
        $arrayy = new A(['Lars' => ['lastname' => 'Moelleken']]);
        $result = $arrayy->get('Lars.lastname');
        static::assertSame('Moelleken', $result);

        // ---

        $arrayy = new A(['Lars' => ['lastname' => null]]);
        $result = $arrayy->get('Lars.lastname');
        static::assertNull($result);

        // ---

        $arrayy = new A(['Lars' => ['lastname' => 'Moelleken']]);
        $result = $arrayy->get();
        static::assertSame($arrayy->getArray(), $result->getArray());
    }

    /**
     * @dataProvider hasProvider()
     *
     * @param mixed $expected
     * @param array $array
     * @param mixed $key
     */
    public function testHas($expected, $array, $key)
    {
        $arrayy = new A($array);
        static::assertSame($expected, $arrayy->has($key));
    }

    /**
     * @dataProvider implodeProvider()
     *
     * @param array  $array
     * @param string $result
     * @param string $with
     */
    public function testImplode($array, $result, $with = ',')
    {
        $string = A::create($array)->implode($with);

        static::assertSame($result, $string);
    }

    /**
     * @dataProvider implodeKeysProvider()
     *
     * @param array  $array
     * @param string $result
     * @param string $with
     */
    public function testImplodeKeys($array, $result, $with = ',')
    {
        $string = A::create($array)->implodeKeys($with);

        static::assertSame($result, $string);
    }

    public function testIndexByReturnEmpty()
    {
        $array = [
            ['name' => 'moe', 'age' => 40],
            ['name' => 'larry', 'age' => 50],
            ['name' => 'curly'],
        ];
        static::assertSame([], A::create($array)->indexBy('vaaaa')->getArray());
    }

    public function testIndexByReturnSome()
    {
        $array = [
            ['name' => 'moe', 'age' => 40],
            ['name' => 'larry', 'age' => 50],
            ['name' => 'curly'],
        ];
        $expected = [
            40 => ['name' => 'moe', 'age' => 40],
            50 => ['name' => 'larry', 'age' => 50],
        ];
        static::assertSame($expected, A::create($array)->indexBy('age')->getArray());
    }

    /**
     * @dataProvider initialProvider()
     *
     * @param array $array
     * @param array $result
     * @param int   $to
     */
    public function testInitial($array, $result, $to = 1)
    {
        $arrayy = A::create($array);

        static::assertSame($result, $arrayy->initial($to)->getArray());
    }

    public function testIntersectsBooleanFlag()
    {
        $a = ['foo', 'bar'];
        $b = ['bar', 'baz'];
        static::assertTrue(A::create($a)->intersects($b));

        $a = 'bar';
        /** @phpstan-ignore-next-line */
        static::assertTrue(A::create($a)->intersects($b));

        $a = 'foo';
        /** @phpstan-ignore-next-line */
        static::assertFalse(A::create($a)->intersects($b));
    }

    public function testInvoke()
    {
        $array = ['   foo  ', '   bar   '];
        $arrayy = A::create($array)->invoke('trim');
        static::assertSame(['foo', 'bar'], $arrayy->getArray());

        $array = ['_____foo', '____bar   '];
        $arrayy = A::create($array)->invoke('trim', ' _');
        static::assertSame(['foo', 'bar'], $arrayy->getArray());

        $array = ['_____foo  ', '__bar   '];
        $arrayy = A::create($array)->invoke('trim', ['_', ' ']);
        static::assertSame(['foo  ', '__bar'], $arrayy->getArray());
    }

    public function testIsArrayAssoc()
    {
        $array0 = [1 => [1]];
        $array1 = [
            1 => 1,
            2 => 2,
        ];
        $array2 = [
            1 => [1],
            2 => [2],
        ];
        $array3 = false;
        $array4 = '';
        $array5 = ' ';
        $array6 = [];
        $array7 = [
            'test',
            'lall',
        ];
        $array8 = [
            0 => 'test',
            1 => 'lall',
        ];
        $array9 = [
            'lall' => 'test',
            'test' => 'lall',
        ];
        $array10 = ['lall' => [0 => 'test']];

        static::assertFalse(A::create($array0)->isAssoc());
        static::assertFalse(A::create($array1)->isAssoc());
        static::assertFalse(A::create($array2)->isAssoc());
        /** @phpstan-ignore-next-line */
        static::assertFalse(A::create($array3)->isAssoc());
        /** @phpstan-ignore-next-line */
        static::assertFalse(A::create($array4)->isAssoc());
        /** @phpstan-ignore-next-line */
        static::assertFalse(A::create($array5)->isAssoc());
        static::assertFalse(A::create($array6)->isAssoc());
        static::assertFalse(A::create($array7)->isAssoc());
        static::assertFalse(A::create($array8)->isAssoc());
        static::assertTrue(A::create($array9)->isAssoc());

        // ---

        static::assertTrue(A::create($array10)->isAssoc());
        static::assertFalse(A::create($array10)->isAssoc(true));

        // ---

        static::assertTrue(
            A::create(
                [
                    'foo' => 'wibble',
                    'bar' => 'wubble',
                    'baz' => 'wobble',
                ]
            )->isAssoc()
        );

        static::assertFalse(
            A::create(
                [
                    'wibble',
                    'wubble',
                    'wobble',
                ]
            )->isAssoc()
        );
    }

    public function testIsArrayMultidim()
    {
        $testArrays = [
            [1 => [1]],
            [0, 1, 2, 3, 4],
            [
                1 => 1,
                2 => 2,
            ],
            [
                1 => [1],
                2 => [2],
            ],
            false,
            '',
            ' ',
            [],
        ];

        $expectedArrays = [
            true,
            false,
            false,
            true,
            false,
            false,
            false,
            false,
        ];

        foreach ($testArrays as $key => $testArray) {
            static::assertSame(
                $expectedArrays[$key],
                /** @phpstan-ignore-next-line */
                A::create($testArray)
                    ->isMultiArray(),
                'tested:' . \print_r($testArray, true)
            );
        }
    }

    /**
     * @dataProvider isAssocProvider()
     *
     * @param array $array
     * @param bool  $result
     */
    public function testIsAssoc($array, $result)
    {
        $resultTmp = A::create($array)->isAssoc();

        static::assertSame($result, $resultTmp);
    }

    public function testIsEmpty()
    {
        $testArrays = [
            [1 => [1]],
            [0, 1, 2, 3, 4],
            [
                1 => 1,
                2 => 2,
            ],
            [
                1 => [1],
                2 => [2],
            ],
            false,
            '',
            ' ',
            [],
        ];

        $expectedArrays = [
            false,
            false,
            false,
            false,
            true,
            true,
            false,
            true,
        ];

        foreach ($testArrays as $key => $testArray) {
            static::assertSame(
                $expectedArrays[$key],
                /** @phpstan-ignore-next-line */
                A::create($testArray)
                    ->isEmpty(),
                'tested:' . \print_r($testArray, true)
            );
        }
    }

    public function testIsNumeric()
    {
        $testArrays = [
            [1 => [1]],
            [0, 1, 2, 3, 4],
            [
                1 => 1,
                2 => 2,
            ],
            [
                1 => [1],
                2 => [2],
            ],
            false,
            '',
            ' ',
            [],
        ];

        $expectedArrays = [
            true,
            true,
            true,
            true,
            false,
            false,
            true,
            false,
        ];

        foreach ($testArrays as $key => $testArray) {
            static::assertSame(
                $expectedArrays[$key],
                /** @phpstan-ignore-next-line */
                A::create($testArray)
                    ->isNumeric(),
                'tested:' . \print_r($testArray, true)
            );
        }
    }

    public function testIsSequentialRecursive()
    {
        $testArrays = [
            [1 => [1]],
            [0, 1, 2, 3, 4],
            [
                1 => 1,
                2 => 2,
            ],
            [
                1 => [1],
                2 => [2],
            ],
            false,
            '',
            ' ',
            [],
            [0, 1, 2, 3, [4 => 3, 5 => 4]],
            [0, 1, 2, 3, [0 => 3, 1 => 4]],
        ];

        $expectedArrays = [
            false,
            true,
            false,
            false,
            false,
            false,
            true,
            false,
            false,
            true,
        ];

        foreach ($testArrays as $key => $testArray) {
            static::assertSame(
                $expectedArrays[$key],
                /** @phpstan-ignore-next-line */
                A::create($testArray)->isSequential(true),
                'tested:' . \print_r($testArray, true)
            );
        }
    }

    public function testIsSequential()
    {
        $testArrays = [
            [1 => [1]],
            [0, 1, 2, 3, 4],
            [
                1 => 1,
                2 => 2,
            ],
            [
                1 => [1],
                2 => [2],
            ],
            false,
            '',
            ' ',
            [],
        ];

        $expectedArrays = [
            false,
            true,
            false,
            false,
            false,
            false,
            true,
            false,
        ];

        foreach ($testArrays as $key => $testArray) {
            static::assertSame(
                $expectedArrays[$key],
                /** @phpstan-ignore-next-line */
                A::create($testArray)->isSequential(),
                'tested:' . \print_r($testArray, true)
            );
        }
    }

    public function testIsSet()
    {
        $arrayy = new A(['foo bar', 'öäü']);
        self::assertArrayy($arrayy);
        static::assertTrue(isset($arrayy[0]));

        // ---

        $arrayy = new A([true => 'foo bar', 'lall' => 'öäü']);
        self::assertArrayy($arrayy);
        /** @phpstan-ignore-next-line */
        static::assertTrue(isset($arrayy[true]));
    }

    public function testJsonSerializable()
    {
        $arrayy = new A();
        $arrayy['user'] = ['lastname' => 'Moelleken'];
        $arrayy['user.firstname'] = 'Lars';

        $json = $arrayy->toJson();
        $arrayyFromJson = \json_decode($json, true);

        static::assertSame(
            [
                'user' => [
                    'lastname'  => 'Moelleken',
                    'firstname' => 'Lars',
                ],
            ],
            $arrayyFromJson
        );
    }

    public function testKeys()
    {
        $arrayyTmp = A::create([1 => 'foo', 2 => 'foo2', 3 => 'bar']);
        $keys = $arrayyTmp->keys();

        $matcher = [1, 2, 3];
        static::assertSame($matcher, $keys->getArray());

        // ---

        $arrayyTmp = A::create([1 => 'foo', 2 => 'foo2', 3 => 'bar']);
        $keys = $arrayyTmp->keys(false, ['foo', 'foo2']);

        $matcher = [1, 2];
        static::assertSame($matcher, $keys->getArray());
        // ---

        $arrayyTmp = A::create([1 => 'foo', 2 => 'foo2', [3 => 'foo']]);
        $keys = $arrayyTmp->keys(false, 'foo');

        $matcher = [1];
        static::assertSame($matcher, $keys->getArray());

        // ---

        $arrayyTmp = A::create([1 => 'foo', 2 => 'foo2', [3 => 'foo']]);
        $keys = $arrayyTmp->keys(true, ['foo']);

        $matcher = [1, 3];
        static::assertSame($matcher, $keys->getArray());

        // ---

        $arrayyTmp = A::create([1 => 'foo', 2 => 'foo2', [3 => 'foo']]);
        $keys = $arrayyTmp->keys(true, 'foo');

        $matcher = [1, 3];
        static::assertSame($matcher, $keys->getArray());

        // ---

        $arrayyTmp = A::create([1 => 'foo', 2 => 'foo2', 3 => 'bar']);
        $keys = $arrayyTmp->getKeys();

        $matcher = [1, 2, 3];
        static::assertSame($matcher, $keys->getArray());
    }

    /**
     * @dataProvider lastProvider()
     *
     * @param array $array
     * @param array $result
     * @param null  $take
     */
    public function testLast($array, $result, $take = null)
    {
        $arrayy = A::create($array);
        $resultNew = $arrayy->lastsImmutable($take);
        static::assertSame($result, $resultNew->getArray());

        $arrayy = A::create($array);
        $resultNew = $arrayy->lastsMutable($take);
        static::assertSame($result, $resultNew->getArray());
    }

    public function testMagicGet()
    {
        $array = [
            'one'  => 1,
            'test' => 2,
            1      => 'one',
            2      => 2,
        ];

        $arrayy = new Arrayy($array);

        /** @phpstan-ignore-next-line */
        static::assertSame(1, $arrayy->one);
        /** @phpstan-ignore-next-line */
        static::assertSame(2, $arrayy->test);
    }

    public function testMagicInvoke()
    {
        $array = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new Arrayy($array);

        static::assertSame(1, $arrayy('one'));
        static::assertSame('one', $arrayy(1));
    }

    public function testMagicSetViaDotNotation()
    {
        $arrayy = new A();
        $arrayy['user'] = ['lastname' => 'Moelleken'];
        $arrayy['user.firstname'] = 'Lars';

        static::assertSame(['user' => ['lastname' => 'Moelleken', 'firstname' => 'Lars']], $arrayy->getArray());
        static::assertSame('Lars', $arrayy['user.firstname']);

        // ---

        $arrayy = new A();
        $arrayy['user'] = ['lastname' => 'Moelleken'];
        $arrayy['user.firstname'] = null;

        static::assertSame(['user' => ['lastname' => 'Moelleken', 'firstname' => null]], $arrayy->getArray());
        static::assertNull($arrayy['user.firstname']);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testMap(array $array)
    {
        $callable = static function ($value) {
            return \str_repeat($value, 2);
        };
        $arrayy = new A($array);
        $resultArrayy = $arrayy->map($callable);
        $resultArray = \array_map($callable, $array);
        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);

        // ---

        /** @noinspection MissUsingForeachInspection */
        foreach ($array as $key => $value) {
            $array[$key] = (string) $value;
        }

        $arrayy = new A($array);
        $resultArrayy = $arrayy->map('str_repeat', false, 2);
        $resultArray = \array_map($callable, $array);
        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
    }

    public function testMapSimpleExample()
    {
        $arrayy = new A(['foo', 'Foo']);
        $resultArrayy = $arrayy->map('strtoupper');
        static::assertSame(['FOO', 'FOO'], $resultArrayy->getArray());
    }

    /**
     * @dataProvider matchesProvider()
     *
     * @param array $array
     * @param mixed $search
     * @param bool  $result
     */
    public function testMatches($array, $search, $result)
    {
        $arrayy = A::create($array);

        $closure = static function ($a) use ($search) {
            return \in_array($a, $search, true);
        };

        $resultMatch = $arrayy->matches($closure);

        static::assertSame(
            $result,
            $resultMatch,
            'tested: ' . \print_r($array, true) . \print_r($search, true)
        );
    }

    /**
     * @dataProvider matchesAnyProvider()
     *
     * @param array $array
     * @param array $search
     * @param bool  $result
     */
    public function testMatchesAny($array, $search, $result)
    {
        $arrayy = A::create($array);

        $closure = static function ($a) use ($search) {
            return \in_array($a, $search, true);
        };

        $resultMatch = $arrayy->matchesAny($closure);

        static::assertSame($result, $resultMatch);
    }

    public function testMatchesAnySimple()
    {
        /**
         * @param int   $value
         * @param mixed $key
         *
         * @return bool
         */
        $closure = static function ($value, $key) {
            return $value % 2 === 0;
        };

        $testArray = [1, 4, 7];
        $result = A::create($testArray)->matchesAny($closure);
        static::assertTrue($result);

        $testArray = [1, 3, 7];
        $result = A::create($testArray)->matchesAny($closure);
        static::assertFalse($result);
    }

    public function testMatchesSimple()
    {
        /**
         * @param int   $value
         * @param mixed $key
         *
         * @return bool
         */
        $closure = static function ($value, $key) {
            return $value % 2 === 0;
        };

        $testArray = [2, 4, 8];
        $result = A::create($testArray)->matches($closure);
        static::assertTrue($result);

        $testArray = [2, 3, 8];
        $result = A::create($testArray)->matches($closure);
        static::assertFalse($result);
    }

    /**
     * @dataProvider maxProvider()
     *
     * @param array $array
     * @param mixed $expected
     */
    public function testMax($array, $expected)
    {
        $arrayy = new A($array);

        static::assertSame($expected, $arrayy->max(), 'tested: ' . \print_r($array, true));
    }

    public function testMergeMethods()
    {
        $array1 = [1 => 'one', 'foo' => 'bar1'];
        $array2 = ['foo' => 'bar2', 3 => 'three'];
        $array3 = Arrayy::create($array1)->mergePrependNewIndex($array2); // Arrayy['foo' => 'bar1', 0 => 'three', 1 => 'one']
        static::assertSame(['foo' => 'bar1', 0 => 'three', 1 => 'one'], $array3->toArray());

        $array1 = [1 => 'one', 'foo' => 'bar1'];
        $array2 = ['foo' => 'bar2', 3 => 'three'];
        $array3 = Arrayy::create($array1)->mergePrependKeepIndex($array2); // Arrayy['foo' => 'bar1', 3 => 'three', 1 => 'one']
        static::assertSame(['foo' => 'bar1', 3 => 'three', 1 => 'one'], $array3->toArray());

        $array1 = [1 => 'one', 'foo' => 'bar1'];
        $array2 = ['foo' => 'bar2', 3 => 'three'];
        $array3 = Arrayy::create($array1)->mergeAppendNewIndex($array2); // Arrayy[0 => 'one', 'foo' => 'bar2', 1 => 'three']
        static::assertSame([0 => 'one', 'foo' => 'bar2', 1 => 'three'], $array3->toArray());

        $array1 = [1 => 'one', 'foo' => 'bar1'];
        $array2 = ['foo' => 'bar2', 3 => 'three'];
        $array3 = Arrayy::create($array1)->mergeAppendKeepIndex($array2); // Arrayy[1 => 'one', 'foo' => 'bar2', 3 => 'three']
        static::assertSame([1 => 'one', 'foo' => 'bar2', 3 => 'three'], $array3->toArray());

        // ---

        $array1 = [0 => 'one', 1 => 'foo'];
        $array2 = [0 => 'foo', 1 => 'bar2'];
        $array3 = Arrayy::create($array1)->mergePrependNewIndex($array2); // Arrayy[0 => 'foo', 1 => 'bar2', 2 => 'one', 3 => 'foo']
        static::assertSame([0 => 'foo', 1 => 'bar2', 2 => 'one', 3 => 'foo'], $array3->toArray());

        $array1 = [0 => 'one', 1 => 'foo'];
        $array2 = [0 => 'foo', 1 => 'bar2'];
        $array3 = Arrayy::create($array1)->mergePrependKeepIndex($array2); // Arrayy[0 => 'one', 1 => 'foo']
        static::assertSame([0 => 'one', 1 => 'foo'], $array3->toArray());

        $array1 = [0 => 'one', 1 => 'foo'];
        $array2 = [0 => 'foo', 1 => 'bar2'];
        $array3 = Arrayy::create($array1)->mergeAppendNewIndex($array2); // Arrayy[0 => 'one', 1 => 'foo', 2 => 'foo', 3 => 'bar2']
        static::assertSame([0 => 'one', 1 => 'foo', 2 => 'foo', 3 => 'bar2'], $array3->toArray());

        $array1 = [0 => 'one', 1 => 'foo'];
        $array2 = [0 => 'foo', 1 => 'bar2'];
        $array3 = Arrayy::create($array1)->mergeAppendKeepIndex($array2); // Arrayy[0 => 'foo', 1 => 'bar2']
        static::assertSame([0 => 'foo', 1 => 'bar2'], $array3->toArray());
    }

    /**
     * @dataProvider mergeAppendKeepIndexProvider()
     *
     * @param array $array
     * @param array $arrayNew
     * @param array $result
     */
    public function testMergeAppendKeepIndex($array, $arrayNew, $result)
    {
        $arrayy = A::create($array)->mergeAppendKeepIndex($arrayNew);

        static::assertSame($result, $arrayy->getArray(), 'tested: ' . \print_r($array, true));
    }

    /**
     * @dataProvider mergeAppendNewIndexProvider()
     *
     * @param array $array
     * @param array $arrayNew
     * @param array $result
     */
    public function testMergeAppendNewIndex($array, $arrayNew, $result)
    {
        $arrayy = A::create($array)->mergeAppendNewIndex($arrayNew);

        static::assertSame($result, $arrayy->getArray());
    }

    /**
     * @dataProvider mergePrependKeepIndexProvider()
     *
     * @param array $array
     * @param array $arrayNew
     * @param array $result
     */
    public function testMergePrependKeepIndex($array, $arrayNew, $result)
    {
        $arrayy = A::create($array)->mergePrependKeepIndex($arrayNew);

        static::assertSame($result, $arrayy->getArray());
    }

    /**
     * @dataProvider mergePrependNewIndexProvider()
     *
     * @param array $array
     * @param array $arrayNew
     * @param array $result
     */
    public function testMergePrependNewIndex($array, $arrayNew, $result)
    {
        $arrayy = A::create($array)->mergePrependNewIndex($arrayNew);

        static::assertSame($result, $arrayy->getArray(), 'tested: ' . \print_r($array, true));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testMergePrependNewIndexV2(array $array)
    {
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($array);
        $resultArrayy = $arrayy->mergePrependNewIndex($secondArray);
        $resultArray = \array_merge($secondArray, $array);

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testMergeToRecursively(array $array)
    {
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($array);
        $resultArrayy = $arrayy->mergePrependNewIndex($secondArray, true);
        $resultArray = \array_merge_recursive($secondArray, $array);

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testMergeWith(array $array)
    {
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($array);
        $resultArrayy = $arrayy->mergeAppendNewIndex($secondArray);
        $resultArray = \array_merge($array, $secondArray);

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testMergeWithRecursively(array $array)
    {
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($array);
        $resultArrayy = $arrayy->mergeAppendNewIndex($secondArray, true);
        $resultArray = \array_merge_recursive($array, $secondArray);

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
    }

    /**
     * @dataProvider minProvider()
     *
     * @param array $array
     * @param mixed $expected
     */
    public function testMin($array, $expected)
    {
        $arrayy = new A($array);

        static::assertSame($expected, $arrayy->min(), 'tested:' . \print_r($array, true));
    }

    public function testMissingToString()
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @noinspection PhpExpressionResultUnusedInspection */
        (string) new A(new \stdClass());
        static::fail(
            'Expecting exception when the constructor is passed an ' .
            'object without a __toString method'
        );
    }

    public function testMoveElementToFirstPlace()
    {
        $arr1 = new A(['a', 'b', 'c', 'd', 'e']);
        $expected = [3 => 'd', 0 => 'a', 1 => 'b', 2 => 'c', 4 => 'e'];
        $newArr1 = $arr1->moveElementToFirstPlace(3);

        static::assertSame($expected, $newArr1->toArray());

        // ---

        $arr2 = new A(['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e']);
        $expected = ['D' => 'd', 'A' => 'a', 'B' => 'b', 'C' => 'c', 'E' => 'e'];
        $newArr2 = $arr2->moveElementToFirstPlace('D');

        static::assertSame($expected, $newArr2->toArray());
    }

    public function testMoveElementToLastPlace()
    {
        $arr1 = new A(['a', 'b', 'c', 'd', 'e']);
        $expected = [0 => 'a', 1 => 'b', 2 => 'c', 4 => 'e', 3 => 'd'];
        $newArr1 = $arr1->moveElementToLastPlace(3);

        static::assertSame($expected, $newArr1->toArray());

        // ---

        $arr2 = new A(['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e']);
        $expected = ['A' => 'a', 'B' => 'b', 'C' => 'c', 'E' => 'e', 'D' => 'd'];
        $newArr2 = $arr2->moveElementToLastPlace('D');

        static::assertSame($expected, $newArr2->toArray());
    }

    public function testMoveElement()
    {
        $arr1 = new A(['a', 'b', 'c', 'd', 'e']);
        $expected = ['a', 'd', 'b', 'c', 'e'];
        $newArr1 = $arr1->moveElement(3, 1);

        static::assertSame($expected, $newArr1->toArray());

        // ---

        $arr2 = new A(['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e']);
        $expected = ['A' => 'a', 'D' => 'd', 'B' => 'b', 'C' => 'c', 'E' => 'e'];
        $newArr2 = $arr2->moveElement('D', 1);

        static::assertSame($expected, $newArr2->toArray());
    }

    public function testNested()
    {
        $array = [
            'one' => 'yes_one',
            'two' => [
                'two_2' => 'yes_two_2',
                'two_3' => ['three' => 'yes_three'],
            ],
        ];

        $arrayzy = new A($array);
        $answer = $arrayzy->has('one');
        static::assertTrue($answer);

        $answer = $arrayzy->has('two.two_2');
        static::assertTrue($answer);

        $answer = $arrayzy->has('two.two_3');
        static::assertTrue($answer);

        $answer = $arrayzy->has('two.two_3.three');
        static::assertTrue($answer);

        $answer = $arrayzy->get('one');
        static::assertSame('yes_one', $answer);

        $answer = $arrayzy->get('two.two_2');
        static::assertSame($array['two']['two_2'], $answer);

        $answer = $arrayzy->get('two.two_3.three');
        static::assertSame($array['two']['two_3']['three'], $answer);

        $answer = $arrayzy->has('kin.dza');
        static::assertFalse($answer);

        $answer = $arrayzy->get('kin.dza', 'no');
        static::assertSame('no', $answer);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testOffsetNullSet(array $array)
    {
        $offset = null;
        $value = 'new';

        $arrayy = new A($array);
        $arrayy->offsetSet($offset, $value);
        $array[] = $value;

        static::assertSame($array, $arrayy->toArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testOffsetSet(array $array)
    {
        $offset = 1;
        $value = 'new';

        $arrayy = new A($array);
        $arrayy->offsetSet($offset, $value);
        $array[$offset] = $value;

        static::assertSame($array, $arrayy->toArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testOffsetUnset(array $array)
    {
        $arrayy = new A($array);
        $offset = 1;

        $arrayy->offsetUnset($offset);
        unset($array[$offset]);

        static::assertSame($array, $arrayy->toArray());
        static::assertFalse(isset($array[$offset]));
        static::assertFalse($arrayy->offsetExists($offset));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testDeleteKey(array $array)
    {
        $arrayy = new A($array);
        $offset = 1;

        $arrayy->delete($offset);
        unset($array[$offset]);

        static::assertSame($array, $arrayy->toArray());
        static::assertFalse(isset($array[$offset]));
        static::assertFalse($arrayy->offsetExists($offset));
    }

    public function testOffsetUnsetViaDotNotation()
    {
        $array = ['a', 'b' => [0 => 'c', 1 => null]];
        $arrayy = new A($array);

        // ---

        $offset = 'b.0';

        static::assertTrue($arrayy->offsetExists($offset));

        $arrayy->offsetUnset($offset);
        unset($array['b'][0]);

        static::assertSame($array, $arrayy->toArray());
        static::assertFalse(isset($array[$offset]));
        static::assertFalse($arrayy->offsetExists($offset));

        // ---

        $offset = 'b.1';

        static::assertTrue($arrayy->offsetExists($offset));

        $arrayy->offsetUnset($offset);
        unset($array['b'][1]);

        static::assertSame([0 => 'a', 'b' => []], $array);
        static::assertSame($array, $arrayy->toArray());
        static::assertFalse(isset($array[$offset]));
        static::assertFalse($arrayy->offsetExists($offset));
    }

    public function testOrderByKey()
    {
        $array = [
            99  => 'aaa',
            100 => 'bcd',
            101 => 123,
            1   => 'Bcde',
            3   => 'bcde',
            4   => 1.1,
            0   => 0,
        ];

        // ------

        $arrayy = A::create($array)->sortKeys(\SORT_DESC, \SORT_REGULAR);
        $result = $arrayy->getArray();

        $expected = [
            101 => 123,
            100 => 'bcd',
            99  => 'aaa',
            4   => 1.1,
            3   => 'bcde',
            1   => 'Bcde',
            0   => 0,
        ];

        static::assertSame($expected, $result);

        // ------

        $arrayy = A::create($array)->sortKeys(\SORT_ASC);
        $result = $arrayy->getArray();

        $expected = [
            0   => 0,
            1   => 'Bcde',
            3   => 'bcde',
            4   => 1.1,
            99  => 'aaa',
            100 => 'bcd',
            101 => 123,
        ];

        static::assertSame($expected, $result);
    }

    public function testOrderByValueKeepIndex()
    {
        $array = [
            100 => 'abc',
            99  => 'aaa',
            2   => 'bcd',
            1   => 'hcd',
            3   => 'bce',
        ];

        $arrayy = A::create($array)->sortValueKeepIndex(\SORT_DESC);
        $result = $arrayy->getArray();

        $expected = [
            1   => 'hcd',
            3   => 'bce',
            2   => 'bcd',
            100 => 'abc',
            99  => 'aaa',
        ];

        static::assertSame($expected, $result);
    }

    public function testOrderByValueNewIndex()
    {
        $array = [
            1   => 'hcd',
            3   => 'bce',
            2   => 'bcd',
            100 => 'abc',
            99  => 'aaa',
        ];

        $arrayy = A::create($array)->sortValueNewIndex(\SORT_ASC, \SORT_REGULAR);
        $result = $arrayy->getArray();

        $expected = [
            0 => 'aaa',
            1 => 'abc',
            2 => 'bcd',
            3 => 'bce',
            4 => 'hcd',
        ];

        static::assertSame($expected, $result);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testPad(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->pad(10, 5);
        $resultArray = \array_pad($array, 10, 5);

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testPop(array $array)
    {
        $arrayy = new A($array);
        $poppedValue = $arrayy->pop();
        $resultArray = $array;
        $poppedArrayValue = \array_pop($resultArray);

        static::assertSame($poppedArrayValue, $poppedValue);
        static::assertSame($resultArray, $arrayy->toArray());
    }

    /**
     * @dataProvider prependProvider()
     *
     * @param array $array
     * @param array $result
     * @param mixed $value
     */
    public function testPrepend($array, $result, $value)
    {
        $arrayy = A::create($array)->prepend($value);

        static::assertSame($result, $arrayy->getArray());
    }

    public function testPrependKey()
    {
        $arrayy = new A(['id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01']);
        $arrayyResult = new A(
            [
                'foo'   => 'lall',
                'id'    => 999,
                'name'  => 'flux',
                'group' => null,
                'value' => 6868,
                'when'  => '2015-01-01',
            ]
        );

        static::assertSame($arrayyResult->toString(), $arrayy->prepend('lall', 'foo')->toString());

        // ---

        $arrayy = new A(['id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01']);
        $arrayyResult = new A(
            [
                0       => 'lall',
                'id'    => 999,
                'name'  => 'flux',
                'group' => null,
                'value' => 6868,
                'when'  => '2015-01-01',
            ]
        );

        static::assertSame($arrayyResult->toString(), $arrayy->prepend('lall')->toString());
    }

    /**
     * @dataProvider prependToEachKeyProvider
     *
     * @param array $array
     * @param array $result
     */
    public function testPrependToEachKey($array, $result)
    {
        $resultTmp = A::create($array)->prependToEachKey('_foo');

        static::assertSame($result, $resultTmp->toArray());

        // ---

        $orig = A::create($array)->toArray();
        if ($orig !== [] && $result !== []) {
            static::assertNotSame($result, $orig);
        }
    }

    /**
     * @dataProvider prependToEachValueProvider
     *
     * @param array $array
     * @param array $result
     */
    public function testPrependToEachValue($array, $result)
    {
        $resultTmp = A::create($array)->prependToEachValue('_foo');

        static::assertSame($result, $resultTmp->toArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testPush(array $array)
    {
        $newElement1 = 5;
        $newElement2 = 10;

        $arrayy = new A($array);
        $resultArrayy = $arrayy->push($newElement1, $newElement2);
        $resultArray = $array;
        \array_push($resultArray, $newElement1, $newElement2);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);
    }

    /**
     * @dataProvider randomProvider()
     *
     * @param array    $array
     * @param int|null $take
     */
    public function testRandom($array, $take = null)
    {
        $arrayy = A::create($array);
        $result = $arrayy->randomMutable($take)->getArray();

        static::assertContains($result[0], $array);
    }

    public function testRandomKey()
    {
        $array = [1 => 'one', 2 => 'two'];
        $arrayy = A::create($array);
        $result = $arrayy->randomKey();

        static::assertArrayHasKey($result, $array);
    }

    public function testRandomKeys()
    {
        $array = [1 => 'one', 2 => 'two'];
        $arrayy = A::create($array);
        $result = $arrayy->randomKeys(2);

        static::assertArrayHasKey($result[0], $array);
        static::assertArrayHasKey($result[1], $array);
    }

    public function testRandomValue()
    {
        $array = [1 => 'one', 2 => 'two'];
        $arrayy = A::create($array);
        $result = $arrayy->randomValue();

        static::assertContains($result, $array);
    }

    public function testRandomValues()
    {
        $array = [1 => 'one', 2 => 'two'];
        $arrayy = A::create($array);
        $result = $arrayy->randomValues(2);

        static::assertContains($result[0], $array);
        static::assertContains($result[1], $array);
    }

    /**
     * @dataProvider randomWeightedProvider()
     *
     * @param array    $array
     * @param int|null $take
     */
    public function testRandomWeighted($array, $take = null)
    {
        $arrayy = A::create($array);
        $result = $arrayy->randomWeighted([0], $take)->getArray();

        static::assertContains($result[0], $array);
    }

    public function testReduce()
    {
        $testArray = ['foo', 2 => 'bar', 4 => 'lall'];

        $myReducer = static function ($resultArray, $value) {
            if ($value === 'foo') {
                $resultArray[] = $value;
            }

            return $resultArray;
        };

        $arrayy = A::create($testArray)->reduce($myReducer);

        $expected = ['foo'];
        static::assertSame($expected, $arrayy->getArray());
    }

    public function testReduceViaFunction()
    {
        $testArray = ['foo', 2 => 'bar', 4 => 'lall'];

        /**
         * @param array $resultArray
         * @param mixed $value
         *
         * @return array
         */
        $myReducer = static function ($resultArray, $value): array {
            if ($value === 'foo') {
                $resultArray[] = $value;
            }

            return $resultArray;
        };

        $arrayy = A::create($testArray)->reduce($myReducer);

        $expected = ['foo'];
        static::assertSame($expected, $arrayy->getArray());
    }

    /**
     * @dataProvider reduceDimensionProvider
     *
     * @param array $array
     * @param array $expected
     * @param bool  $unique
     */
    public function testReduceDimension(array $array, array $expected, bool $unique = false)
    {
        $arrayy = new A($array);
        $result = $arrayy->reduce_dimension($unique)->getArray();

        static::assertSame($expected, $result);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testTestgetValues(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->getValues()->getArray();
        $resultArray = \array_values($array);

        static::assertSame([], \array_diff($resultArrayy, $resultArray));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testTestgetValuesYield(array $array)
    {
        $arrayy = new A($array);
        $resultGenerator = $arrayy->getValuesYield();
        $result = [];
        foreach ($resultGenerator as $key => $value) {
            $result[$key] = $value;
        }
        $resultArray = \array_values($array);

        static::assertSame([], \array_diff($result, $resultArray));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testReindex(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->reindex()->getArray();
        $resultArray = \array_values($array);

        static::assertSame([], \array_diff($resultArrayy, $resultArray));
    }

    public function testReindexSimple()
    {
        $testArray = [2 => 1, 3 => 2];
        $arrayy = new A($testArray);
        $arrayy->reindex();

        $result = [0 => 1, 1 => 2];

        static::assertSame($result, $arrayy->getArray());
    }

    public function testPartition()
    {
        $array = [1, 2, 3, 4];
        $arrayy = A::create($array)->partition(
            static function ($value) {
                return $value % 2 !== 0;
            }
        );
        static::assertEquals([0 => 1, 2 => 3], $arrayy[0]->toArray());
        static::assertEquals([1 => 2, 3 => 4], $arrayy[1]->toArray());

        // ---

        $array = [1 => 'foo', 2 => 'bar', 3 => 'lall', 4 => '123'];
        $arrayy = A::create($array)->partition(
            static function ($value, $key) {
                return $key % 2 !== 0;
            }
        );
        static::assertEquals([1 => 'foo', 3 => 'lall'], $arrayy[0]->toArray());
        static::assertEquals([2 => 'bar', 4 => '123'], $arrayy[1]->toArray());
    }

    public function testReject()
    {
        $array = [1, 2, 3, 4];
        $arrayy = A::create($array)->reject(
            static function ($value) {
                return $value % 2 !== 0;
            }
        );
        static::assertSame([1 => 2, 3 => 4], $arrayy->getArray());

        // ---

        $array = [1 => 'foo', 2 => 'bar', 3 => 'lall', 4 => '123'];
        $arrayy = A::create($array)->reject(
            static function ($value, $key) {
                return $key % 2 !== 0;
            }
        );
        static::assertSame([2 => 'bar', 4 => '123'], $arrayy->getArray());
    }

    public function testValidate()
    {
        $array = [2, 4];
        $result = A::create($array)->validate(
            static function ($value) {
                return $value % 2 === 0;
            }
        );
        static::assertTrue($result);

        // ---

        $array = [1, 4];
        $result = A::create($array)->validate(
            static function ($value) {
                return $value % 2 === 0;
            }
        );
        static::assertFalse($result);

        // ---

        $array = [2 => 1, 4 => 3];
        $result = A::create($array)->validate(
            static function ($value, $key) {
                return $key % 2 === 0;
            }
        );
        static::assertTrue($result);
    }

    /**
     * @dataProvider removeProvider()
     *
     * @param array $array
     * @param mixed $key
     * @param array $result
     */
    public function testRemove($array, $key, $result)
    {
        $arrayy = new A($array);
        $resultTmp = $arrayy->remove($key)->getArray();
        static::assertSame($result, $resultTmp);
    }

    /**
     * @dataProvider removeFirstProvider()
     *
     * @param array $array
     * @param array $result
     */
    public function testRemoveFirst($array, $result)
    {
        $arrayy = A::create($array)->removeFirst();

        static::assertSame($result, $arrayy->getArray());
    }

    /**
     * @dataProvider removeLastProvider()
     *
     * @param array $array
     * @param array $result
     */
    public function testRemoveLast($array, $result)
    {
        $arrayy = A::create($array)->removeLast();

        static::assertSame($result, $arrayy->getArray());
    }

    /**
     * @dataProvider removeV2Provider()
     *
     * @param array $array
     * @param array $result
     * @param mixed $key
     */
    public function testRemoveV2($array, $result, $key)
    {
        $arrayy = A::create($array)->remove($key);

        static::assertSame($result, $arrayy->getArray());
    }

    /**
     * @dataProvider removeValueProvider()
     *
     * @param array $array
     * @param array $result
     * @param mixed $value
     */
    public function testRemoveValue($array, $result, $value)
    {
        $arrayy = A::create($array)->removeValue($value);

        static::assertSame($result, $arrayy->getArray());

        // --

        $arrayy = A::create($array)->removeElement($value);

        static::assertSame($result, $arrayy->getArray());
    }

    public function testRepeat()
    {
        $arrayTmp = ['lall'];
        $arrayExpected = [['lall'], ['lall'], ['lall']];

        $arrayyTmp = A::create($arrayTmp);
        $arrayyResult = $arrayyTmp->repeat(3);
        static::assertSame($arrayExpected, $arrayyResult->getArray());

        // --

        $arrayTmp = ['lall'];
        $arrayExpected = [];

        $arrayyTmp = A::create($arrayTmp);
        $arrayyResult = $arrayyTmp->repeat(0);
        static::assertSame($arrayExpected, $arrayyResult->getArray());

        // --

        $arrayTmp = ['foo', 'bar'];
        $arrayExpected = [['foo', 'bar'], ['foo', 'bar']];

        $arrayyTmp = A::create($arrayTmp);
        $arrayyResult = $arrayyTmp->repeat(2);
        static::assertSame($arrayExpected, $arrayyResult->getArray());
    }

    public function testReplace()
    {
        $arraySource = [1 => 'foo', 2 => 'foo2', 3 => 'bar'];
        $arrayyTmp = A::create($arraySource);

        $arrayy = $arrayyTmp->replace(1, 'notfoo', 'notbar');
        $matcher = [
            2        => 'foo2',
            3        => 'bar',
            'notfoo' => 'notbar',
        ];
        static::assertSame($matcher, $arrayy->getArray());
        static::assertSame($arraySource, $arrayyTmp->getArray());
    }

    public function testReplaceAllKeys()
    {
        $firstArray = [
            1 => 'one',
            2 => 'two',
            3 => 'three',
        ];
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($firstArray);
        $resultArrayy = $arrayy->replaceAllKeys($secondArray)->getArray();
        $resultArray = \array_combine($secondArray, $firstArray);

        static::assertSame($resultArray, $resultArrayy);
    }

    public function testReplaceAllKeysV2()
    {
        $firstArray = [
            1 => 'one',
            2 => 'two',
            3 => 'three',
        ];
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($firstArray);
        $resultArrayy = $arrayy->replaceAllKeys($secondArray)->getArray();

        $result = [
            1     => 'one',
            'one' => 'two',
            2     => 'three',
        ];
        static::assertSame($result, $resultArrayy);
    }

    public function testReplaceAllValues()
    {
        $firstArray = [
            1 => 'one',
            2 => 'two',
            3 => 'three',
        ];
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($firstArray);
        $resultArrayy = $arrayy->replaceAllValues($secondArray);
        $resultArray = (array) \array_combine($firstArray, $secondArray);

        self::assertImmutable($arrayy, $resultArrayy, $firstArray, $resultArray);
    }

    public function testReplaceAllValuesV2()
    {
        $firstArray = [
            1 => 'one',
            2 => 'two',
            3 => 'three',
        ];
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($firstArray);
        $resultArrayy = $arrayy->replaceAllValues($secondArray);

        $result = [
            'one'   => 1,
            'two'   => 'one',
            'three' => 2,
        ];

        static::assertSame($result, $resultArrayy->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testReplaceIn(array $array)
    {
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($array);
        $resultArrayy = $arrayy->mergePrependKeepIndex($secondArray)->getArray();
        $resultArray = \array_replace($secondArray, $array);

        static::assertSame([], \array_diff($resultArrayy, $resultArray));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testReplaceInRecursively(array $array)
    {
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($array);
        $resultArrayy = $arrayy->mergePrependKeepIndex($secondArray, true)->getArray();
        $resultArray = \array_replace_recursive($secondArray, $array);

        static::assertSame([], \array_diff($resultArrayy, $resultArray));
    }

    public function testReplaceKeys()
    {
        $arrayy = A::create([1 => 'bar', 'foo' => 'foo'])->replaceKeys([1 => 2, 'foo' => 'replaced']);

        static::assertSame('bar', $arrayy[2]);
        static::assertSame('foo', $arrayy['replaced']);

        $arrayy = A::create([1 => 'bar', 'foo' => 'foo'])->replaceKeys([1, 'foo' => 'replaced']);

        static::assertSame('bar', $arrayy[1]);
        static::assertSame('foo', $arrayy['replaced']);
    }

    public function testReplaceOneValue()
    {
        $testArray = ['bar', 'foo' => 'foo', 'foobar' => 'foobar'];
        $arrayy = A::create($testArray)->replaceOneValue('foo', 'replaced');

        static::assertSame('replaced', $arrayy['foo']);
        static::assertSame('foobar', $arrayy['foobar']);
    }

    public function testReplaceV2()
    {
        $arrayyTmp = A::create([1 => 'foo', 2 => 'foo2', 3 => 'bar']);

        $arrayy = $arrayyTmp->replace(2, 'notfoo', 'notbar');
        $matcher = [
            1        => 'foo',
            3        => 'bar',
            'notfoo' => 'notbar',
        ];

        static::assertSame($matcher, $arrayy->getArray());
        static::assertNotSame($arrayyTmp, $arrayy);
    }

    public function testReplaceValues()
    {
        $testArray = ['bar', 'foo' => 'foo', 'foobar' => 'foobar'];
        $arrayy = A::create($testArray)->replaceValues('foo', 'replaced');

        static::assertSame('replaced', $arrayy['foo']);
        static::assertSame('replacedbar', $arrayy['foobar']);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testReplaceWith(array $array)
    {
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($array);
        $resultArrayy = $arrayy->mergeAppendKeepIndex($secondArray)->getArray();
        $resultArray = \array_replace($array, $secondArray);

        static::assertSame([], \array_diff($resultArrayy, $resultArray));
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testReplaceWithRecursively(array $array)
    {
        $secondArray = [
            'one' => 1,
            1     => 'one',
            2     => 2,
        ];

        $arrayy = new A($array);
        $resultArrayy = $arrayy->mergeAppendKeepIndex($secondArray, true)->getArray();
        $resultArray = \array_replace_recursive($array, $secondArray);

        static::assertSame([], \array_diff($resultArrayy, $resultArray));
    }

    /**
     * @dataProvider restProvider()
     *
     * @param array $array
     * @param array $result
     * @param int   $from
     */
    public function testRest($array, $result, $from = 1)
    {
        $arrayy = A::create($array);

        static::assertSame($result, $arrayy->rest($from)->getArray(), 'tested:' . \print_r($array, true));
    }

    /**
     * @dataProvider reverseProvider()
     *
     * @param array $array
     * @param array $result
     */
    public function testReverse($array, $result)
    {
        $arrayy = A::create($array)->reverse();

        static::assertSame($result, $arrayy->getArray());
    }

    /**
     * @dataProvider searchIndexProvider()
     *
     * @param false|int|string $expected
     * @param array            $array
     * @param mixed            $value
     */
    public function testSearchIndex($expected, $array, $value)
    {
        $arrayy = new A($array);

        static::assertSame($expected, $arrayy->searchIndex($value));
    }

    /**
     * @dataProvider searchValueProvider()
     *
     * @param array $expected
     * @param array $array
     * @param mixed $value
     */
    public function testSearchValue($expected, $array, $value)
    {
        $arrayy = new A($array);

        static::assertSame($expected, $arrayy->searchValue($value)->getArray());
    }

    public function testSerialize()
    {
        $testArray = [1, 4, 7];
        $arrayy = A::create($testArray);
        $result = $arrayy->serialize();

        if (\method_exists(__CLASS__, 'assertStringContainsString')) {
            static::assertStringContainsString('a:3:{i:0;i:1;i:1;i:4;i:2;i:7;}', $result);
            static::assertStringContainsString($result, \serialize($arrayy));
        } else {
            static::assertContains('a:3:{i:0;i:1;i:1;i:4;i:2;i:7;}', $result);
            static::assertContains($result, \serialize($arrayy));
        }
        static::assertSame($arrayy->getArray(), \unserialize(\serialize($arrayy))->getArray());

        // create a object with an "arrayy"-property

        $object = new \stdClass();
        $object->arrayy = $arrayy;

        static::assertSame($object->arrayy, $arrayy);

        // serialize + tests
        if (\PHP_VERSION_ID < 70400) {
            if (\method_exists(__CLASS__, 'assertStringContainsString')) {
                static::assertStringContainsString('O:8:"stdClass":1:{s:6:"arrayy";C:13:"Arrayy\Arrayy":', \serialize($object));
            } else {
                static::assertContains('O:8:"stdClass":1:{s:6:"arrayy";C:13:"Arrayy\Arrayy":', \serialize($object));
            }
            static::assertNotSame($object, \unserialize(\serialize($object)));
        } else {
            if (\method_exists(__CLASS__, 'assertStringContainsString')) {
                static::assertStringContainsString('O:8:"stdClass":1:{s:6:"arrayy";O:13:"Arrayy\\Arrayy":', \serialize($object));
            } else {
                static::assertContains('O:8:"stdClass":1:{s:6:"arrayy";O:13:"Arrayy\\Arrayy":', \serialize($object));
            }
            static::assertNotSame($object, \unserialize(\serialize($object)));
        }

        $arrayy = new A([1 => 1, 2 => 2, 3 => 3]);
        $serialized = $arrayy->serialize();
        $arrayy = new A();
        $result = $arrayy->unserialize($serialized);

        static::assertSame([1 => 1, 2 => 2, 3 => 3], $result->getArray());

        // ---

        $modelMeta = CityData::meta();
        $model = new CityData(
            [
                $modelMeta->name  => 'Düsseldorf',
                $modelMeta->plz   => null,
                $modelMeta->infos => ['foo'],
            ]
        );

        // serialize + tests
        if (\PHP_VERSION_ID < 70400) {
            static::assertInstanceOf(CityData::class, $model);
            if (\method_exists(__CLASS__, 'assertStringContainsString')) {
                static::assertStringContainsString('C:21:"Arrayy\tests\CityData":', \serialize($model));
            } else {
                static::assertContains('C:21:"Arrayy\tests\CityData":', \serialize($model));
            }
            static::assertNotSame($model, \unserialize(\serialize($model)));
            static::assertInstanceOf(CityData::class, $model);
        } else {
            static::assertInstanceOf(CityData::class, $model);
            if (\method_exists(__CLASS__, 'assertStringContainsString')) {
                static::assertStringContainsString('O:21:"Arrayy\tests\CityData":', \serialize($model));
            } else {
                static::assertContains('O:21:"Arrayy\tests\CityData":', \serialize($model));
            }
            static::assertNotSame($model, \unserialize(\serialize($model)));
            static::assertInstanceOf(CityData::class, $model);
        }
    }

    public function testSerializeSimple()
    {
        $arrayy = A::create([1, 'a', 4.4]);

        static::assertSame($arrayy->getArray(), \unserialize(\serialize($arrayy))->getArray());
    }

    /**
     * @dataProvider setProvider()
     *
     * @param array $array
     * @param mixed $key
     * @param mixed $value
     */
    public function testSet($array, $key, $value)
    {
        $arrayy = new A($array);
        $arrayy = $arrayy->set($key, $value)->getArray();
        static::assertSame($value, $arrayy[$key]);
    }

    /**
     * @dataProvider setAndGetProvider()
     *
     * @param array $array
     * @param mixed $key
     * @param mixed $value
     */
    public function testSetAndGet($array, $key, $value)
    {
        $arrayy = new A($array);
        $result = $arrayy->setAndGet($key, $value);
        static::assertSame($value, $result);
    }

    public function testSetAndGetSimple()
    {
        $arrayy = new A([1, 2, 3]);
        $result = $arrayy->setAndGet(0, 4);

        $expected = 1;
        static::assertSame($expected, $result);

        // ---

        $arrayy = new A([1 => 1, 2 => 2, 3 => 3]);
        $result = $arrayy->setAndGet(0, 4);

        $expected = 4;
        static::assertSame($expected, $result);
    }

    public function testSetV2()
    {
        $arrayy = new A(['foo bar', 'UTF-8']);
        $arrayy[1] = 'öäü';
        self::assertArrayy($arrayy);
        static::assertSame('foo bar,öäü', (string) $arrayy);
    }

    public function testSetValueViaMagicSet()
    {
        $arrayy = new A(['Lars' => ['lastname' => 'Mueller2']]);

        /** @phpstan-ignore-next-line */
        $arrayy->Lars = ['lastname' => 'Moelleken'];
        /** @phpstan-ignore-next-line */
        $arrayy->Sven = ['lastname' => 'Moelleken'];
        /** @phpstan-ignore-next-line */
        $arrayy->foo = ['lastname' => null];

        $resultTmp = $arrayy->get('Lars');
        static::assertSame(['lastname' => 'Moelleken'], $resultTmp->getArray());

        $resultTmp = $arrayy->get('Sven');
        static::assertSame(['lastname' => 'Moelleken'], $resultTmp->getArray());

        $resultTmp = $arrayy->get('foo');
        static::assertSame(['lastname' => null], $resultTmp->getArray());

        $resultTmp = $arrayy->get('Lars.lastname');
        static::assertSame('Moelleken', $resultTmp);

        $resultTmp = $arrayy->get('Sven.lastname');
        static::assertSame('Moelleken', $resultTmp);

        $resultTmp = $arrayy->get('foo.lastname');
        static::assertNull($resultTmp);
    }

    public function testSetViaDotNotation()
    {
        $arrayy = new A(['Lars' => ['lastname' => 'Moelleken']]);

        static::assertSame(['lastname' => 'Moelleken'], $arrayy['Lars']->getArray());

        $result = $arrayy->get('Lars.lastname');
        static::assertSame('Moelleken', $result);

        static::assertSame(['lastname' => 'Moelleken'], $arrayy['Lars']->getArray());

        /** @phpstan-ignore-next-line */
        static::assertSame(['lastname' => 'Moelleken'], $arrayy->Lars->getArray());

        /** @phpstan-ignore-next-line */
        static::assertSame('Moelleken', $arrayy->Lars->lastname);

        static::assertSame('Moelleken', $arrayy['Lars']['lastname']);

        $tmp = $arrayy['Lars'];
        static::assertSame('Moelleken', $tmp['lastname']);

        // set an new value - via dot-notation
        $result = $arrayy->set('Lars.lastname', 'Müller');

        $resultTmp = $result->get('Lars');
        static::assertSame(['lastname' => 'Müller'], $resultTmp->getArray());
        $resultTmp = $result->get('Lars.lastname');
        static::assertSame('Müller', $resultTmp);

        // set an new value, again - via array-syntax - multi-dim set isn't working :/
        $arrayyLars = new A($arrayy['Lars']);
        $arrayyLars['lastname'] = 'Mueller';
        $arrayy['Lars'] = $arrayyLars;

        $resultTmp = $arrayy->get('Lars');
        static::assertSame(['lastname' => 'Mueller'], $resultTmp->getArray());
        $resultTmp = $arrayy->get('Lars.lastname');
        static::assertSame('Mueller', $resultTmp);

        // set an new value, again - via array-syntax - multi-dim set isn't working :/
        $arrayy['Lars'] = ['lastname' => 'Mueller'];

        $resultTmp = $arrayy->get('Lars');
        static::assertSame(['lastname' => 'Mueller'], $resultTmp->getArray());
        $resultTmp = $arrayy->get('Lars.lastname');
        static::assertSame('Mueller', $resultTmp);

        // set an new value, again - via object-syntax
        /** @phpstan-ignore-next-line */
        $arrayy->Lars = ['lastname' => 'Mueller2'];

        $resultTmp = $arrayy->get('Lars');
        static::assertSame(['lastname' => 'Mueller2'], $resultTmp->getArray());
        $resultTmp = $arrayy->get('Lars.lastname');
        static::assertSame('Mueller2', $resultTmp);

        // set an new "null"-value, again - via object-syntax
        $arrayy->Lars = ['lastname' => null];

        $resultTmp = $arrayy->get('Lars');
        static::assertSame(['lastname' => null], $resultTmp->getArray());
        $resultTmp = $arrayy->get('Lars.lastname');
        static::assertNull($resultTmp);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testShift(array $array)
    {
        $arrayy = new A($array);
        $shiftedValue = $arrayy->shift();
        $resultArray = $array;
        $shiftedArrayValue = \array_shift($resultArray);

        static::assertSame($shiftedArrayValue, $shiftedValue);
        static::assertSame($resultArray, $arrayy->toArray());
    }

    public function testShuffle()
    {
        $arrayy = A::create([1 => 'bar', 'foo' => 'foo'])->shuffle();

        static::assertContains('bar', $arrayy->getArray());
        static::assertContains('foo', $arrayy->getArray());

        // ---

        $arrayy = A::create([1 => 'bar', 'foo' => 'foo'])->shuffle(true);

        static::assertContains('bar', $arrayy->getArray());
        static::assertContains('foo', $arrayy->getArray());
    }

    public function testSimpleAt()
    {
        $result = A::create();
        $closure = static function ($value, $key) use ($result) {
            $result[$key] = ':' . $value . ':';
        };

        A::create(['foo', 'bar' => 'bis'])->at($closure);
        static::assertSame(A::create([':foo:', 'bar' => ':bis:'])->getArray(), $result->getArray());
    }

    public function testSizeIs()
    {
        $a = A::create([1, 2]);

        static::assertTrue($a->sizeIs(2));
        static::assertFalse($a->sizeIs(3));
        static::assertFalse($a->sizeIs(0));
    }

    public function testSizeIsLessThen()
    {
        $a = A::create([1, 2]);

        static::assertFalse($a->sizeIsLessThan(0));
        static::assertFalse($a->sizeIsLessThan(2));
        static::assertTrue($a->sizeIsLessThan(3));
    }

    public function testSizeIsGreaterThan()
    {
        $a = A::create([1, 2]);

        static::assertFalse($a->sizeIsGreaterThan(2));
        static::assertTrue($a->sizeIsGreaterThan(1));
        static::assertTrue($a->sizeIsGreaterThan(0));
    }

    public function testSizeIsBetween()
    {
        $a = A::create([1, 2]);

        static::assertTrue($a->sizeIsBetween(1, 3));
        static::assertFalse($a->sizeIsBetween(3, 4));
        static::assertFalse($a->sizeIsBetween(0, 0));
        static::assertTrue($a->sizeIsBetween(3, 1));
    }

    public function testSimpleEach()
    {
        $closure = static function ($value) {
            return ':' . $value . ':';
        };

        $result = A::create(['foo', 'bar' => 'bis'])->each($closure);
        static::assertSame([':foo:', 'bar' => ':bis:'], $result->getArray());
    }

    public function testSimpleRandom()
    {
        $testArray = [-8 => -9, 1, 2 => false];
        $arrayy = A::create($testArray);
        $result = $arrayy->randomMutable(3);
        static::assertSame($arrayy, $result);
        static::assertSame($arrayy, $result);
        static::assertCount(3, $result);

        $testArray = [-8 => -9, 1, 2 => false];
        $arrayy = A::create($testArray);
        $result = $arrayy->randomMutable();
        static::assertSame($arrayy, $result);
        static::assertSame($arrayy, $result);
        static::assertCount(1, $result);

        $testArray = [-8 => -9, 1, 2 => false];
        $arrayy = A::create($testArray);
        $result = $arrayy->randomImmutable(3);
        static::assertNotSame($arrayy->getArray(), $result->getArray());
        static::assertSame($arrayy->sort()->getArray(), $result->sort()->getArray());
        static::assertCount(3, $result);

        $testArray = [-8 => -9, 1, 2 => false];
        $arrayy = A::create($testArray);
        $result = $arrayy->randomImmutable(3);
        static::assertNotSame($arrayy, $result);
        static::assertSame($arrayy->sort()->getArray(), $result->sort()->getArray());
        static::assertCount(3, $result);
    }

    public function testSimpleRandomWeighted()
    {
        $testArray = ['foo', 'bar'];
        $result = A::create($testArray)->randomWeighted(['bar' => 2]);
        static::assertCount(1, $result);

        $testArray = ['foo', 'bar', 'foobar'];
        $result = A::create($testArray)->randomWeighted(['foobar' => 3], 2);
        static::assertCount(2, $result);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testSlice(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->slice(1, 1);
        $resultArray = \array_slice($array, 1, 1);

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);
    }

    public function testSort()
    {
        $testArray = [5, 3, 1, 2, 4];
        $under = A::create($testArray)->sorter(null, 'desc');
        static::assertSame([5, 4, 3, 2, 1], $under->getArray());

        // ---

        $testArray = [5, 3, 1, 2, 4];
        $under = A::create($testArray)->sorter(null, 'asc');
        static::assertSame([1, 2, 3, 4, 5], $under->getArray());

        // ---

        $testArray = ['foo' => 5, 'bar' => 3, 'lll' => 1, 2, 4];
        $under = A::create($testArray)->sorter('lll', 'desc');
        static::assertSame(['lll' => 1, 0 => 2, 'bar' => 3, 1 => 4, 'foo' => 5], $under->getArray());

        // ---

        $testArray = ['foo' => 5, 'bar' => 3, 'lll' => 1, 2, 4];
        $under = A::create($testArray)->sorter(3, 'desc');
        static::assertSame(['bar' => 3, 'lll' => 1, 0 => 2, 1 => 4, 'foo' => 5], $under->getArray());

        // ---

        $testArray = ['foo' => 5, 'bar' => 3, 'lll' => 1, 2, 4];
        $under = A::create($testArray)->sorter(3, 'asc');
        static::assertSame(['lll' => 1, 0 => 2, 1 => 4, 'foo' => 5, 'bar' => 3], $under->getArray());

        // ---

        $testArray = \range(1, 5);
        $under = A::create($testArray)->sorter(
            static function ($value) {
                if ($value % 2 === 0) {
                    return -1;
                }

                return 1;
            }
        );
        static::assertSame([2, 4, 1, 3, 5], $under->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testSortAscWithPreserveKeys(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->sort(\SORT_ASC, \SORT_REGULAR, true);
        $resultArray = $array;
        \asort($resultArray, \SORT_REGULAR);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sort(\SORT_ASC, \SORT_REGULAR, true);
        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->asort();

        self::assertMutable($arrayy, $resultArrayy, $resultArrayV2->getArray());

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sortImmutable(\SORT_ASC, \SORT_REGULAR, true);
        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->asortImmutable();

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArrayV2->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testSortAscWithoutPreserveKeys(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->sort(\SORT_ASC, \SORT_REGULAR, false);
        $resultArray = $array;
        \sort($resultArray, \SORT_REGULAR);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sort();
        $resultArray = $array;
        \sort($resultArray, \SORT_REGULAR);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testSortDescWithPreserveKeys(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->sort(\SORT_DESC, \SORT_REGULAR, true);
        $resultArray = $array;
        \arsort($resultArray, \SORT_REGULAR);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sort(\SORT_DESC, \SORT_REGULAR, true);
        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->arsort();

        self::assertMutable($arrayy, $resultArrayy, $resultArrayV2->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testSortImmutableDescWithPreserveKeys(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->sortImmutable(\SORT_DESC, \SORT_REGULAR, true);
        $resultArray = $array;
        \arsort($resultArray, \SORT_REGULAR);

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArray);

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sortImmutable(\SORT_DESC, \SORT_REGULAR, true);
        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->arsortImmutable();

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArrayV2->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testSortDescWithoutPreserveKeys(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->sort(\SORT_DESC, \SORT_REGULAR, false);
        $resultArray = $array;
        \rsort($resultArray, \SORT_REGULAR);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sort(\SORT_DESC, \SORT_REGULAR, false);
        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->rsort();

        self::assertMutable($arrayy, $resultArrayy, $resultArrayV2->getArray());

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sortImmutable(\SORT_DESC, \SORT_REGULAR, false);
        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->rsortImmutable();

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArrayV2->getArray());
    }

    /**
     * @dataProvider sortKeysProvider()
     *
     * @param array  $array
     * @param array  $result
     * @param string $direction
     */
    public function testSortKeys($array, $result, $direction = 'ASC')
    {
        $arrayy = A::create($array)->sortKeys($direction);

        static::assertSame($result, $arrayy->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testSortKeysAsc(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->sortKeys(\SORT_ASC, \SORT_REGULAR);
        $resultArray = $array;
        \ksort($resultArray, \SORT_REGULAR);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sortKeys(\SORT_ASC, \SORT_REGULAR);
        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->ksort();

        self::assertMutable($arrayy, $resultArrayy, $resultArrayV2->getArray());

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sortKeysImmutable(\SORT_ASC, \SORT_REGULAR);
        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->ksortImmutable();

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArrayV2->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testNatcasesort(array $array)
    {
        \natcasesort($array);
        $arrayy = new A($array);
        $arrayyResult = $arrayy->natcasesort();
        $arrayResult = $arrayyResult->getArray();

        static::assertSame($array, $arrayResult);
        self::assertMutable($arrayy, $arrayyResult, $arrayResult);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testNatsortImmutable(array $array)
    {
        \natsort($array);
        $arrayy = new A($array);
        $arrayyResult = $arrayy->natsortImmutable();
        $arrayResult = $arrayyResult->getArray();

        static::assertSame($array, $arrayResult);
        self::assertImmutable($arrayy, $arrayyResult, $array, $arrayResult);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testNatsort(array $array)
    {
        \natsort($array);
        $arrayy = new A($array);
        $arrayyResult = $arrayy->natsort();
        $arrayResult = $arrayyResult->getArray();

        static::assertSame($array, $arrayResult);
        self::assertMutable($arrayy, $arrayyResult, $arrayResult);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testNatcasesortImmutable(array $array)
    {
        \natcasesort($array);
        $arrayy = new A($array);
        $arrayyResult = $arrayy->natcasesortImmutable();
        $arrayResult = $arrayyResult->getArray();

        static::assertSame($array, $arrayResult);
        self::assertImmutable($arrayy, $arrayyResult, $array, $arrayResult);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testUasort(array $array)
    {
        $function = static function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        };
        \uasort($array, $function);
        $arrayy = new A($array);
        $arrayyResult = $arrayy->uasort($function);
        $arrayResult = $arrayyResult->getArray();

        static::assertSame($array, $arrayResult);
        self::assertMutable($arrayy, $arrayyResult, $arrayResult);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testUasortImmutable(array $array)
    {
        $function = static function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        };
        \uasort($array, $function);
        $arrayy = new A($array);
        $arrayyResult = $arrayy->uasortImmutable($function);
        $arrayResult = $arrayyResult->getArray();

        static::assertSame($array, $arrayResult);
        self::assertImmutable($arrayy, $arrayyResult, $array, $arrayResult);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testSortKeysDesc(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = $arrayy->sortKeys(\SORT_DESC, \SORT_REGULAR);
        $resultArray = $array;
        \krsort($resultArray, \SORT_REGULAR);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sortKeys(\SORT_DESC, \SORT_REGULAR);
        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->krsort();

        self::assertMutable($arrayy, $resultArrayy, $resultArrayV2->getArray());

        // ---

        $arrayy = new A($array);
        $resultArrayy = $arrayy->sortKeysImmutable(\SORT_DESC, \SORT_REGULAR);
        $arrayV2 = new A($array);
        $resultArrayV2 = $arrayV2->krsortImmutable();

        self::assertImmutable($arrayy, $resultArrayy, $array, $resultArrayV2->getArray());
    }

    public function testSortV2()
    {
        $array = [
            1   => 'hcd',
            3   => 'bce',
            2   => 'bcd',
            100 => 'abc',
            99  => 'aaa',
        ];

        $arrayy = A::create($array)->sort(\SORT_ASC, \SORT_REGULAR, false);
        $result = $arrayy->getArray();

        $expected = [
            0 => 'aaa',
            1 => 'abc',
            2 => 'bcd',
            3 => 'bce',
            4 => 'hcd',
        ];

        static::assertSame($expected, $result);
    }

    public function testSplit()
    {
        self::assertArrayy(A::create()->split());

        static::assertSame(
            A::create([['a', 'b'], ['c', 'd']])->getArray(),
            A::create(['a', 'b', 'c', 'd'])->split()->getArray()
        );

        static::assertSame(
            A::create([['a' => 1, 'b' => 2], ['c' => 3, 'd' => 4]])->getArray(),
            A::create(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4])->split(2, true)->getArray()
        );

        static::assertSame(
            A::create([[1, 2], [3]])->getArray(),
            A::create(['a' => 1, 'b' => 2, 'c' => 3])->split(2, false)->getArray()
        );

        static::assertSame(
            A::create([['a' => 1, 'b' => 2], ['c' => 3]])->getArray(),
            A::create(['a' => 1, 'b' => 2, 'c' => 3])->split(2, true)->getArray()
        );

        static::assertSame(
            A::create(
                [
                    0 => [
                        0 => 1,
                        1 => 2,
                    ],
                    1 => [
                        0 => 3,
                    ],
                ]
            )->getArray(),
            A::create(
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ]
            )->split(2, false)->getArray()
        );
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testStaticCreate(array $array)
    {
        $arrayy = new A($array);
        $resultArrayy = A::create($array);

        self::assertImmutable($arrayy, $resultArrayy, $array, $array);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testStaticCreateFromGeneratorImmutableFromArray(array $array)
    {
        $arrayy = A::create($array);
        $generator = $arrayy->getGenerator();
        $resultArrayy = A::createFromGeneratorImmutable($generator);

        self::assertImmutable($arrayy, $resultArrayy, $array, $array);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     * @param int   $count
     */
    public function testStaticCreateFromGeneratorFunctionFromArray(array $array, int $count)
    {
        $arrayy = A::create($array);
        $resultArrayy = A::createFromGeneratorFunction(
            static function () use ($arrayy) {
                yield from $arrayy->getArray();
            }
        );

        static::assertSame($count, $resultArrayy->count());

        self::assertImmutable($arrayy, $resultArrayy, $array, $array);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testStaticCreateFromJson(array $array)
    {
        $json = (string) \json_encode($array);

        $arrayy = A::create($array);
        $resultArrayy = A::createFromJson($json);

        self::assertImmutable($arrayy, $resultArrayy, $array, $array);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testStaticCreateFromObject(array $array)
    {
        $arrayy = A::create($array);
        $resultArrayy = A::createFromObject($arrayy);

        self::assertImmutable($arrayy, $resultArrayy, $array, $array);
    }

    public function testCustomMerge()
    {
        $arr = [
            0 => ['title' => 'A', 'quantity' => 2],
            1 => ['title' => 'B', 'quantity' => 3],
            2 => ['title' => 'A', 'quantity' => 4],
        ];

        $resultArrayy = A::create();
        $callable = static function ($value, $key) use (&$resultArrayy) {
            if (!isset($resultArrayy[$value['title']])) {
                $resultArrayy[$value['title']] = $value;
            } else {
                $resultArrayy[$value['title']]['quantity'] += $value['quantity'];
            }
        };
        (new Arrayy($arr))->at($callable);

        static::assertSame(
            [
                0 => ['title' => 'A', 'quantity' => 6],
                1 => ['title' => 'B', 'quantity' => 3],
            ],
            \array_values($resultArrayy->getArray())
        );
    }

    public function testCreateFromObjectAndDot()
    {
        $s = new \stdClass();
        $s->foo = 'bar';

        $a = new \stdClass();
        $a->number = 22;
        $a->protocol = 'tcp';
        $a->service = $s;

        $b = new \stdClass();
        $b->number = 8080;
        $b->protocol = 'udp';
        $b->service = $s;

        $arrayy = new A();
        $arrayy->add($a);
        $arrayy->add($b);
        $arrayy->add($a);

        $services = $arrayy->get('*.service');

        $expected = [
            $s,
            $s,
            $s,
        ];

        static::assertSame($expected, $services->getArray());
    }

    public function testStaticCreateFromObjectVars()
    {
        $a = new \stdClass();
        $a->x = 42;
        $a->y = ['lall', 'foo'];
        $a->z = 'bar';

        $resultArrayy = A::createFromObjectVars($a);

        static::assertSame((array) $a, $resultArrayy->getArray());

        // ---

        $object = new \stdClass();
        $object->x = 42;
        $arrayy = A::createFromObjectVars($object);
        static::assertSame(['x' => 42], $arrayy->getArray());
    }

    /**
     * @dataProvider stringWithSeparatorProvider
     *
     * @param string      $string
     * @param string|null $separator
     */
    public function testStaticCreateFromString($string, $separator)
    {
        if ($separator !== null) {
            $array = \explode($separator, $string);
        } else {
            $array = [$string];
        }
        \assert(\is_array($array));

        $arrayy = A::create($array);
        $resultArrayy = A::createFromString($string, $separator);

        self::assertImmutable($arrayy, $resultArrayy, $array, $array);
    }

    public function testStripEmpty()
    {
        $arrayy = new A(['id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01']);
        $arrayyResult = new A(['id' => 999, 'name' => 'flux', 'value' => 6868, 'when' => '2015-01-01']);

        static::assertSame($arrayyResult->toString(), $arrayy->stripEmpty()->toString());
    }

    public function testSwap()
    {
        $arrayy = new A(['id' => 999, 'name' => 'flux', 'group' => null, 'value' => 6868, 'when' => '2015-01-01']);
        $arrayyResult = new A(
            [
                'id'    => 999,
                'name'  => 'flux',
                'group' => null,
                'value' => '2015-01-01',
                'when'  => 6868,
            ]
        );

        static::assertSame($arrayyResult->toString(), $arrayy->swap('value', 'when')->toString());
    }

    /**
     * @dataProvider diffReverseProvider()
     *
     * @param array $array
     * @param array $arrayNew
     * @param array $result
     */
    public function testTestdiffReverse($array, $arrayNew, $result)
    {
        $arrayy = A::create($array)->diffReverse($arrayNew);

        static::assertSame($result, $arrayy->getArray(), 'tested:' . \print_r($array, true));
    }

    /**
     * @dataProvider isMultiArrayProvider()
     *
     * @param array $array
     * @param bool  $result
     */
    public function testTestisMultiArray($array, $result)
    {
        $resultTmp = A::create($array)->isMultiArray();

        static::assertSame($result, $resultTmp);
    }

    /**
     * @dataProvider toStringProvider()
     *
     * @param string $expected
     * @param array  $array
     */
    public function testToString($expected, $array)
    {
        static::assertSame($expected, (string) new A($array));
    }

    /**
     * @dataProvider uniqueProvider()
     *
     * @param array $array
     * @param array $result
     */
    public function testUnique($array, $result)
    {
        $arrayy = A::create($array)->unique();
        static::assertSame($result, $arrayy->getArray());

        $arrayy = A::create($array)->uniqueNewIndex();
        static::assertSame($result, $arrayy->getArray());
    }

    /**
     * @dataProvider uniqueProviderKeepIndex()
     *
     * @param array $array
     * @param array $result
     */
    public function testUniqueKeepIndex($array, $result)
    {
        $arrayy = A::create($array)->uniqueKeepIndex();
        static::assertSame($result, $arrayy->getArray());
    }

    public function testUnset()
    {
        $arrayy = new A(['foo bar', 'öäü']);
        unset($arrayy[1]);
        self::assertArrayy($arrayy);
        static::assertSame('foo bar', $arrayy[0]);
        static::assertNull($arrayy[1]);
    }

    public function testUnsetSimple()
    {
        $arrayy = new A([1 => 1, 2 => 2, 3 => 3]);
        unset($arrayy[2]);
        static::assertSame([1 => 1, 3 => 3], $arrayy->getArray());

        // ---

        $arrayy = new A(['Lars' => ['lastname' => 'Moelleken', 'status' => 'foo']]);
        unset($arrayy['Lars.status']);
        static::assertSame(['Lars' => ['lastname' => 'Moelleken']], $arrayy->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testUnshift(array $array)
    {
        $newElement1 = 5;
        $newElement2 = 10;

        $arrayy = new A($array);
        $resultArrayy = $arrayy->unshift($newElement1, $newElement2);
        $resultArray = $array;
        \array_unshift($resultArray, $newElement1, $newElement2);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);
    }

    public function testValues()
    {
        $arrayyTmp = A::create([1 => 'foo', 2 => 'foo2', 3 => 'bar']);
        $values = $arrayyTmp->values();

        $matcher = [0 => 'foo', 1 => 'foo2', 2 => 'bar'];
        static::assertSame($matcher, $values->getArray());
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testWalk(array $array)
    {
        $callable = static function (&$value, $key) {
            $value = $key;
        };

        $arrayy = new A($array);
        $resultArrayy = $arrayy->walk($callable);
        $resultArray = $array;
        \array_walk($resultArray, $callable);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);
    }

    /**
     * @dataProvider simpleArrayProvider
     *
     * @param array $array
     */
    public function testWalkRecursively(array $array)
    {
        $callable = static function (&$value, $key) {
            $value = $key;
        };

        $arrayy = new A($array);
        $resultArrayy = $arrayy->walk($callable, true);
        $resultArray = $array;
        \array_walk_recursive($resultArray, $callable);

        self::assertMutable($arrayy, $resultArrayy, $resultArray);
    }

    public function testWalkSimple()
    {
        $callable = static function (&$value, $key) {
            $value = $key;
        };

        $array = [1, 2, [3, 4, [5, 6]]];
        $arrayy = new A($array);
        $resultArrayy = $arrayy->walk($callable);

        $expected = [0, 1, 2];
        static::assertSame($expected, $resultArrayy->getArray());
    }

    public function testWalkSimpleRecursively()
    {
        $callable = static function (&$value, $key) {
            $value = $key;
        };

        $array = [1, 2, [3, 4, [5, 6]]];
        $arrayy = new A($array);
        $resultArrayy = $arrayy->walk($callable, true);

        $expected = [0, 1, [0, 1, [0, 1]]];
        static::assertSame($expected, $resultArrayy->getArray());
    }

    /**
     * @return array
     */
    public function toStringProvider(): array
    {
        return [
            ['', [0 => null]],
            ['', [0 => false]],
            ['1', [0 => true]],
            ['-9,1,0,', [0 => -9, 1 => 1, 2 => 0, false]],
            ['1.18', [0 => 1.18]],
            [' string  ,foo', [0 => ' string  ', 1 => 'foo']],
        ];
    }

    /**
     * @return array
     */
    public function uniqueProvider(): array
    {
        $a = new \stdClass();
        $a->x = 42;

        $b = new \stdClass();
        $b->y = 42;

        $c = new \stdClass();
        $c->x = 43;

        return [
            [[], []],
            [[0 => false], [false]],
            [[0 => true], [true]],
            [[0 => -9, 1 => -9], [-9]],
            [[0 => null, 1 => 0], [null, 0]],
            [[0 => -9, 1 => 1, 2 => 2], [0 => -9, 1 => 1, 2 => 2]],
            [[0 => 1.18, 1 => 1.5], [0 => 1.18, 1 => 1.5]],
            [
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                ],
                [
                    0 => 'string',
                    1 => 'foo',
                    2 => 'lall',
                ],
            ],
            [
                [2 => 1, 3 => 2, 4 => 2],
                [0 => 1, 1 => 2],
            ],
            [
                [
                    $a,
                    $a,
                    $b,
                    $b,
                    $c,
                    $c,
                ],
                [
                    $a,
                    $b,
                    $c,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function uniqueProviderKeepIndex(): array
    {
        $a = new \stdClass();
        $a->x = 42;

        $b = new \stdClass();
        $b->y = 42;

        $c = new \stdClass();
        $c->x = 43;

        return [
            [[], []],
            [[0 => false], [false]],
            [[0 => true], [true]],
            [[0 => -9, 1 => -9], [-9]],
            [[0 => null, 1 => 0], [null, 0]],
            [[0 => -9, 1 => 1, 2 => 2], [0 => -9, 1 => 1, 2 => 2]],
            [[0 => 1.18, 1 => 1.5], [0 => 1.18, 1 => 1.5]],
            [
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                    6 => 'foo',
                ],
                [
                    3 => 'string',
                    4 => 'foo',
                    5 => 'lall',
                ],
            ],
            [
                [2 => 1, 3 => 2, 4 => 2],
                [2 => 1, 3 => 2],
            ],
            [
                [
                    $a,
                    $a,
                    $b,
                    $b,
                    $c,
                    $c,
                ],
                [
                    0 => $a,
                    2 => $b,
                    4 => $c,
                ],
            ],
        ];
    }

    /**
     * @param A<int|string,mixed>                            $arrayzy
     * @param A<int,mixed>|A<int,string>|A<int|string,mixed> $resultArrayzy
     * @param array                                          $array
     * @param array                                          $resultArray
     */
    protected static function assertImmutable(A $arrayzy, A $resultArrayzy, array $array, array $resultArray)
    {
        static::assertNotSame($arrayzy, $resultArrayzy);
        static::assertSame($array, $arrayzy->toArray());
        static::assertSame($resultArray, $resultArrayzy->toArray());
    }

    /**
     * @param A<array-key,mixed> $arrayzy
     * @param A<array-key,mixed> $resultArrayzy
     * @param array              $resultArray
     */
    protected static function assertMutable(A $arrayzy, A $resultArrayzy, array $resultArray)
    {
        static::assertSame($arrayzy, $resultArrayzy);
        static::assertSame($resultArray, $arrayzy->toArray());
        static::assertSame($resultArray, $resultArrayzy->toArray());
    }
}
