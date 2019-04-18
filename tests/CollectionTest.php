<?php

namespace Arrayy\tests;

use Arrayy\Arrayy;

/**
 * @internal
 */
final class CollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testSimpleCollection()
    {
        $pets = new \stdClass();
        $pets->foo = 1;

        $colors = new \stdClass();
        $colors->color = 'red';

        $stdClassCollection = new StdClassCollection([$pets, $colors]);

        static::assertSame(\stdClass::class, $stdClassCollection->getType());

        /** @noinspection PhpUnitTestsInspection */
        static::assertTrue([$pets, $colors] === $stdClassCollection->getCollection());
    }

    public function testModelCollection()
    {
        $pets = new ModelA(['cat', 'dog', 'bird']);
        $colors = new ModelB(['red', 'yellow', 'green', 'white']);

        $modelCollection = new ModelsCollection([$pets]);

        $modelCollection->add($colors);

        static::assertSame(ModelInterface::class, $modelCollection->getType());

        /** @noinspection PhpUnitTestsInspection */
        static::assertTrue([$pets, $colors] === $modelCollection->getCollection());
    }

    public function testConstructorException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be of type Arrayy\tests\ModelInterface; value is Arrayy\tests\CityData Object');

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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be of type Arrayy\tests\ModelInterface; value is Arrayy\tests\CityData Object');

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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be of type Arrayy\tests\ModelInterface; value is Arrayy\tests\CityData Object');

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

    public function testPrependExceptionV1()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be of type Arrayy\tests\ModelInterface; value is Arrayy\tests\CityData Object');

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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be of type Arrayy\tests\ModelInterface; value is Arrayy\tests\CityData Object');

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
            self::assertInstanceOf(ModelInterface::class, $item);
        }
    }
}
