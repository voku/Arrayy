<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\Arrayy;
use Arrayy\Mapper\Json;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class JsonMapperCoverageTest extends TestCase
{
    public function testMapRejectsNonObjectTargets(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JsonMapper::map() requires second argument to be an object, integer given.');

        (new Json())->map([], 123);
    }

    public function testMapInvokesUndefinedPropertyHandlerWithSafeName(): void
    {
        $mapper = new Json();
        $captured = [];
        $target = new \stdClass();

        $mapper->undefinedPropertyHandler = static function ($object, string $key, $value) use (&$captured): void {
            $captured = [$object, $key, $value];
        };

        $result = $mapper->map(['unknown-key' => 'value'], $target);

        static::assertSame($target, $result);
        static::assertSame([$target, 'UnknownKey', 'value'], $captured);
    }

    public function testMapSkipsPrivatePropertiesWithoutSetters(): void
    {
        $mapper = new Json();
        $target = new JsonMapperPrivatePropertyFixture();

        $result = $mapper->map(['secret' => 'changed'], $target);

        static::assertSame($target, $result);
        static::assertSame('keep', $target->getSecret());
    }

    public function testMapTreatsDocOnlyPropertiesAsMixed(): void
    {
        $mapper = new Json();
        $payload = (object) ['id' => 1];
        $target = new JsonMapperDocOnlyFixture();

        $result = $mapper->map(['payload' => $payload], $target);

        static::assertSame($target, $result);
        static::assertSame($payload, $target->payload);
    }

    public function testMapAcceptsExplicitNullForNullableProperties(): void
    {
        $target = (new Json())->map(['name' => null], new JsonMapperNullableFixture());

        static::assertNull($target->name);
    }

    public function testMapRejectsExplicitNullForNonNullableProperties(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON property "name" in class "Arrayy\\tests\\JsonMapperStringFixture" must not be NULL');

        (new Json())->map(['name' => null], new JsonMapperStringFixture());
    }

    public function testMapRejectsObjectsForStringProperties(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON property "name" in class "Arrayy\\tests\\JsonMapperStringFixture" is an object and cannot be converted to a string');

        (new Json())->map(['name' => (object) ['value' => 'foo']], new JsonMapperStringFixture());
    }

    public function testMapDirectlyAssignsObjectsThatAlreadyMatchTheDeclaredType(): void
    {
        $account = new Account('Foo');
        $target = (new Json())->map(['account' => $account], new JsonMapperAccountHolderFixture());

        static::assertSame($account, $target->account);
    }

    public function testMapCreatesTypedObjectsFromScalarAndObjectInput(): void
    {
        $mapper = new Json();

        $fromScalar = $mapper->map(['account' => 'Foo'], new JsonMapperAccountHolderFixture());
        static::assertInstanceOf(Account::class, $fromScalar->account);
        static::assertSame('Foo', $fromScalar->account->accountName);

        $fromObject = $mapper->map(['account' => (object) ['accountName' => 'Bar']], new JsonMapperAccountHolderFixture());
        static::assertInstanceOf(Account::class, $fromObject->account);
        static::assertSame('Bar', $fromObject->account->accountName);
    }

    public function testMapRejectsEmptyDocblockTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty type at property "Arrayy\\tests\\JsonMapperEmptyTypeFixture::$broken"');

        (new Json())->map(['broken' => 'value'], new JsonMapperEmptyTypeFixture());
    }

    public function testMapSupportsBracketArraySyntaxAndRejectsScalarInputForArrayProperties(): void
    {
        $mapper = new Json();

        $target = $mapper->map(['ids' => ['1', '2']], new JsonMapperBracketArrayFixture());
        static::assertSame([1, 2], $target->ids);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON property "ids" must be an array, integer given');
        $mapper->map(['ids' => 1], new JsonMapperBracketArrayFixture());
    }

    public function testMapSupportsBracketClassArraySyntax(): void
    {
        $target = (new Json())->map(['names' => ['foo', 'bar']], new JsonMapperArrayObjectFixture());

        static::assertInstanceOf(\ArrayObject::class, $target->names);
        static::assertSame(['foo', 'bar'], $target->names->getArrayCopy());
    }

    public function testMapArrayHandlesNestedArraysScalarCastingAndClassInstantiation(): void
    {
        $mapper = new Json();

        static::assertSame([[1, 2]], $mapper->mapArray([['1', '2']], [], 'int[]'));
        static::assertSame([null, 2], $mapper->mapArray([null, '2'], [], 'int'));

        $objects = $mapper->mapArray([(object) ['accountName' => 'Baz']], [], Account::class);
        static::assertInstanceOf(Account::class, $objects[0]);
        static::assertSame('Baz', $objects[0]->accountName);
    }

    public function testMapArrayRejectsNestedArraysForScalarTypes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON property "items" is an array of type "int" but contained a value of type "array"');

        (new Json())->mapArray([['x']], [], 'int', 'items');
    }

    public function testMapArraySupportsArrayObjectTargets(): void
    {
        $mapped = (new Json())->mapArray([['foo' => 'bar']], [], \ArrayObject::class);

        static::assertInstanceOf(\ArrayObject::class, $mapped[0]);
        static::assertSame(['foo' => 'bar'], $mapped[0]->getArrayCopy());
    }

    public function testMapArrayCreatesNestedArrayyObjectsFromPhpDocTypes(): void
    {
        $mapped = (new Json())->mapArray(
            [
                'city' => (object) [
                    'name' => 'Düsseldorf',
                    'plz' => null,
                    'infos' => ['foo'],
                ],
            ],
            new JsonMapperArrayyCityHolderFixture()
        );

        static::assertInstanceOf(CityData::class, $mapped['city']);
        static::assertSame('Düsseldorf', $mapped['city']['name']);
    }
}

final class JsonMapperPrivatePropertyFixture
{
    private string $secret = 'keep';

    public function getSecret(): string
    {
        return $this->secret;
    }
}

final class JsonMapperDocOnlyFixture
{
    /**
     * Plain documentation without type metadata.
     */
    public $payload;
}

final class JsonMapperNullableFixture
{
    /**
     * @var string|null
     */
    public $name;
}

final class JsonMapperStringFixture
{
    /**
     * @var string
     */
    public $name;
}

final class JsonMapperAccountHolderFixture
{
    /**
     * @var \Arrayy\tests\Account
     */
    public $account;
}

final class JsonMapperEmptyTypeFixture
{
    /**
     * @var
     */
    public $broken;
}

final class JsonMapperBracketArrayFixture
{
    /**
     * @var array[int]
     */
    public $ids;
}

final class JsonMapperArrayObjectFixture
{
    /**
     * @var \ArrayObject[string]
     */
    public $names;
}

/**
 * @property \Arrayy\tests\CityData $city
 * @extends \Arrayy\Arrayy<array-key, mixed>
 */
final class JsonMapperArrayyCityHolderFixture extends Arrayy
{
}
