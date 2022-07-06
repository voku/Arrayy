<?php

namespace Arrayy\tests\Collection;

require_once __DIR__ . '/../../.phpUnitAndStanFix.php';

/**
 * @internal
 */
final class AnalyseTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerics()
    {
        $json = '[{"id":1,"firstName":"Lars","lastName":"Moelleken","city":{"name":"Düsseldorf","plz":null,"infos":["lall"]}}, {"id":1,"firstName":"Sven","lastName":"Moelleken","city":{"name":"Köln","plz":null,"infos":["foo"]}}]';
        $userDataCollection = UserDataCollection::createFromJsonMapper($json);

        foreach ($userDataCollection as $user) {
            \PHPStan\Testing\assertType(\Arrayy\tests\UserData::class, $user);
            static::assertInstanceOf(\Arrayy\tests\UserData::class, $user);

            \PHPStan\Testing\assertType('Arrayy\tests\CityData|null', $user->city);
            static::assertTrue($user->city === null || $user->city instanceof \Arrayy\tests\CityData); /* @phpstan-ignore-line | always true */

            \PHPStan\Testing\assertType('string|null', $user->city->name ?? null);
            static::assertTrue(($user->city->name ?? null) === null || is_string($user->city->name ?? null)); /* @phpstan-ignore-line | always true */
        }

        // -------------------------------------------------------------------------

        $set = new \Arrayy\Type\StringCollection(['A', 'B', 'C', 'D', 'E']);
        $newSet = $set->chunk(2);

        foreach ($newSet as $chunk) {
            \PHPStan\Testing\assertType('Arrayy\Arrayy<(int|string), string>', $chunk);
            static::assertTrue($chunk->getArray() === ['A', 'B'] || $chunk->getArray() === ['C', 'D'] || $chunk->getArray() === ['E']);
        }

        // -------------------------------------------------------------------------

        $set = new \Arrayy\Type\StringCollection(['A', 'B', 'C', 'D', 'E']);
        $set[] = 'F';
        $set[] = 'G';
        /* @phpstan-ignore-next-line | not accept */
        $set[] = 2;
        /* @phpstan-ignore-next-line | not accept */
        $set[] = 3;
        /* @phpstan-ignore-next-line | not accept */
        $set[] = false;

        \PHPStan\Testing\assertType('Arrayy\Type\StringCollection', $set);

        // -------------------------------------------------------------------------

        $set = new \Arrayy\Type\DetectFirstValueTypeCollection([1, 2, 3, 4]);

        foreach ($set as $item) {
            \PHPStan\Testing\assertType('int', $item);
            static::assertTrue($item === 1 || $item === 2 || $item === 3 || $item === 4);
        }

        // -------------------------------------------------------------------------

        $set = new \Arrayy\Type\DetectFirstValueTypeCollection([new \stdClass(), new \stdClass()]);

        foreach ($set as $item) {
            \PHPStan\Testing\assertType(\stdClass::class, $item);
            static::assertInstanceOf(\stdClass::class, $item);
        }

        // -------------------------------------------------------------------------

        $set = new \Arrayy\Type\NonEmptyStringCollection(['foo', '1', 'bar']);

        foreach ($set as $item) {
            \PHPStan\Testing\assertType('non-empty-string', $item);
            static::assertTrue(strlen($item) > 0);/* @phpstan-ignore-line | always true */
        }

        // -------------------------------------------------------------------------

        $set = new \Arrayy\Type\NumericCollection(['1.0', 1.2, 4]);

        foreach ($set as $item) {
            \PHPStan\Testing\assertType('float|int|numeric-string', $item);
            static::assertTrue($item === '1.0' || $item === 1.2 || $item === 4);
        }

        // -------------------------------------------------------------------------

        $set = new \Arrayy\Type\ScalarCollection(['4', 5.0, 7, true, false, '6', '7']);

        $search = '6';
        $closure = function ($value) use ($search) {
            return $value === $search;
        };
        \PHPStan\Testing\assertType('bool|float|int|string', $set->find($closure));
        static::assertTrue(is_scalar($set->find($closure)));

        // -------------------------------------------------------------------------
    }
}
