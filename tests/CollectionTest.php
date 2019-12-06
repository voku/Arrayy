<?php

namespace Arrayy\tests;

use function Arrayy\collection;
use Arrayy\Collection\Collection;

/**
 * @internal
 */
final class CollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testSimpleGenericCollection()
    {
        $pets = new \stdClass();
        $pets->foo = 1;

        $colors = new \stdClass();
        $colors->color = 'red';

        $collection = collection('mixed', [$pets, $colors]);

        static::assertSame([$pets, $colors], $collection->getCollection());
    }

    public function testSimpleBaseGenericCollection()
    {
        $pets = new \stdClass();
        $pets->foo = 1;

        $colors = new \stdClass();
        $colors->color = 'red';

        $collection = StdBaseClassCollection::construct(
            \stdClass::class,
            [$pets, $colors]
        );

        static::assertSame([$pets, $colors], $collection->getCollection());

        $baseCollection = $collection->toBase();

        /** @noinspection UnnecessaryAssertionInspection */
        static::assertInstanceOf(Collection::class, $baseCollection);
    }

    public function testSimpleGenericFailCollection()
    {
        $this->expectException(\TypeError::class);

        $pets = new \stdClass();
        $pets->foo = 1;

        collection(ModelInterface::class, $pets);
    }

    public function testSimpleCollection()
    {
        $pets = new \stdClass();
        $pets->foo = 'fooooo';

        $colors = new \stdClass();
        $colors->color = 'red';

        $stdClassCollection = new StdClassCollection([123 => $pets, 555 => $colors]);

        static::assertSame(\stdClass::class, $stdClassCollection->getType());

        static::assertSame([123 => $pets, 555 => $colors], $stdClassCollection->getCollection());

        static::assertSame('fooooo', $stdClassCollection->get('123.foo'));
    }

    public function testBasic()
    {
        $pets = new \stdClass();
        $pets->foo = 'fooooo';

        $colors = new \stdClass();
        $colors->foo = 'red';

        $stdClassCollection = new StdClassCollection([123 => $pets, 555 => $colors]);

        static::assertSame([123 => $pets, 555 => $colors], $stdClassCollection->getCollection());
        static::assertSame([999 => $colors, 123 => $pets, 555 => $colors], $stdClassCollection->prepend($colors, 999)->getCollection());
        static::assertSame([999 => $colors, 123 => $pets, 555 => $colors, 1000 => $colors], $stdClassCollection->append(new StdClassCollection($colors))->getCollection());
    }

    public function testModelCollection()
    {
        $pets = new ModelA(['cat', 'dog', 'bird']);
        $colors = new ModelB(['red', 'yellow', 'green', 'white']);

        $modelCollection = new ModelsCollection([$pets]);

        $modelCollection->add($colors);

        static::assertSame(ModelInterface::class, $modelCollection->getType());

        static::assertSame([$pets, $colors], $modelCollection->getCollection());
    }

    public function testConstructorException()
    {
        $this->expectException(\TypeError::class);

        $cityData = new CityData(
            [
                CityData::meta()->name  => 'Düsseldorf',
                CityData::meta()->plz   => null,
                CityData::meta()->infos => ['foo'],
            ]
        );

        $modelCollection = new ModelsCollection([$cityData]);

        static::assertSame(ModelInterface::class, $modelCollection->getType());
    }

    public function testAddExceptionV1()
    {
        $this->expectException(\TypeError::class);

        $pets = new ModelA(['cat', 'dog', 'bird']);
        $colors = new ModelB(['red', 'yellow', 'green', 'white']);

        $cityData = new CityData(
            [
                CityData::meta()->name  => 'Düsseldorf',
                CityData::meta()->plz   => null,
                CityData::meta()->infos => ['foo'],
            ]
        );

        $modelCollection = new ModelsCollection([$pets, $colors]);

        $modelCollection[] = $cityData;

        static::assertSame(ModelInterface::class, $modelCollection->getType());
    }

    public function testAddExceptionV2()
    {
        $this->expectException(\TypeError::class);

        $pets = new ModelA(['cat', 'dog', 'bird']);
        $colors = new ModelB(['red', 'yellow', 'green', 'white']);

        $cityData = new CityData(
            [
                CityData::meta()->name  => 'Düsseldorf',
                CityData::meta()->plz   => null,
                CityData::meta()->infos => ['foo'],
            ]
        );

        $modelCollection = new ModelsCollection([$pets, $colors]);

        $modelCollection->add($cityData);

        static::assertSame(ModelInterface::class, $modelCollection->getType());
    }

    public function testWhere()
    {
        $pet1 = new ModelA(['pet' => ['cat']]);
        $pet2 = new ModelB(['pet' => ['dog', 'bird']]);

        $modelCollection = new ModelsCollection([$pet1, $pet2]);

        static::assertSame(ModelInterface::class, $modelCollection->getType());

        $newCollection = $modelCollection->where('pet', ['cat']);

        $modelCollectionExpected = new ModelsCollection([$pet1]);
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        /** @noinspection PhpUnitTestsInspection */
        static::assertTrue($modelCollectionExpected == $newCollection);
    }

    public function testClear()
    {
        $pet1 = new ModelA(['pet' => ['cat']]);
        $pet2 = new ModelB(['pet' => ['dog', 'bird']]);

        $modelCollection = new ModelsCollection([$pet1, $pet2]);

        static::assertSame(ModelInterface::class, $modelCollection->getType());
        static::assertTrue($modelCollection->count() > 0);

        $modelCollection->clear();

        static::assertSame(0, $modelCollection->count());
    }

    public function testTypesForAllProperties()
    {
        $model = new ModelC(['test', 'foo']);

        static::assertSame(['test', 'foo'], $model->getArray());
    }

    public function testTypesForAllPropertiesFail()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected to be of type {string}, instead got value `1` with type {integer}.');

        new ModelC(['test', 1]);
    }

    public function testTypesForOneProperties()
    {
        $model = new ModelC(['test', 'foo']);

        static::assertSame(['test', 'foo'], $model->getArray());
    }

    public function testTypesForOnePropertiesFail()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected to be of type {int}, instead got value `a` with type {string}.');

        new ModelD(['foo' => 'a']);
    }

    public function testTypesForOnePropertiesCorrect()
    {
        $d = new ModelD(['foo' => 1]);

        static::assertSame(['foo' => 1], $d->getArray());
    }

    public function testPrependExceptionV1()
    {
        $this->expectException(\TypeError::class);

        $pets = new ModelA(['cat', 'dog', 'bird']);
        $colors = new ModelB(['red', 'yellow', 'green', 'white']);

        $cityData = new CityData(
            [
                CityData::meta()->name  => 'Düsseldorf',
                CityData::meta()->plz   => null,
                CityData::meta()->infos => ['foo'],
            ]
        );

        $modelCollection = new ModelsCollection([$pets, $colors]);

        $modelCollection->prepend($cityData);

        static::assertSame(ModelInterface::class, $modelCollection->getType());
    }

    public function testPrependExceptionV2()
    {
        $this->expectException(\TypeError::class);

        $pets = new ModelA(['cat', 'dog', 'bird']);
        $colors = new ModelB(['red', 'yellow', 'green', 'white']);

        $cityData = new CityData(
            [
                CityData::meta()->name  => 'Düsseldorf',
                CityData::meta()->plz   => null,
                CityData::meta()->infos => ['foo'],
            ]
        );

        $modelCollection = new ModelsCollection([$pets, $colors]);

        $rand = $modelCollection->randomImmutable(1);
        static::assertInstanceOf(ModelInterface::class, $rand->first());

        $modelCollection->prepend($cityData, 0);

        static::assertSame(ModelInterface::class, $modelCollection->getType());
    }

    public function testColumnByArrayKey()
    {
        $bar1 = new ModelA();
        $bar1['name'] = 'a';
        $bar1['foo'] = 'bar';

        $bar2 = new ModelA();
        $bar2['name'] = 'b';
        $bar2['foo'] = 'bar';

        $bar3 = new ModelB();
        $bar3['name'] = 'c';
        $bar3['foo'] = 'bar';

        $barCollection = new ModelsCollection([$bar1, $bar2, $bar3]);

        static::assertSame(
            ['a', 'b', 'c'],
            $barCollection->column('name')
        );

        foreach ($barCollection as $item) {
            static::assertInstanceOf(ModelInterface::class, $item);
        }
    }

    public function testWithGeneratorsV1()
    {
        $arrayyFunction = static function () {
            $bar1 = new ModelA();
            $bar1['name'] = 'a';
            $bar1['foo'] = 'bar';
            yield $bar1;

            $bar2 = new ModelA();
            $bar2['name'] = 'b';
            $bar2['foo'] = 'bar';
            yield $bar2;

            $bar3 = new ModelB();
            $bar3['name'] = 'c';
            $bar3['foo'] = 'bar';
            yield $bar3;
        };

        $barCollection = new ModelsCollection($arrayyFunction);

        foreach ($barCollection as $item) {
            static::assertInstanceOf(ModelInterface::class, $item);

            if ($item instanceof ModelInterface) {
                static::assertStringStartsWith('foo', $item->getFoo());
            }
        }
    }

    public function testMerge()
    {
        $bar1 = new ModelA();
        $bar1['name'] = 'a';
        $bar1['foo'] = 'bar';

        $bar2 = new ModelA();
        $bar2['name'] = 'b';
        $bar2['foo'] = 'bar';

        $bar3 = new ModelB();
        $bar3['name'] = 'c';
        $bar3['foo'] = 'bar';

        $barCollection1 = new ModelsCollection([$bar1, $bar3]);

        $barCollection2 = new ModelsCollection([$bar1, $bar2]);

        static::assertSame(
            [$bar1, $bar3, $bar1, $bar2],
            $barCollection1->merge($barCollection2)->getCollection()
        );
    }
}
