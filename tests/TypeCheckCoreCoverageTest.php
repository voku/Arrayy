<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\TypeCheck\TypeCheckCallback;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckPhpDoc;
use Arrayy\TypeCheck\TypeCheckSimple;
use PHPUnit\Framework\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Property;

/**
 * @internal
 */
final class TypeCheckCoreCoverageTest extends TestCase
{
    /**
     * Risk: `string[]` validation previously returned true after the first matching
     * element, so mixed invalid arrays silently passed as long as one element matched.
     *
     * @dataProvider invalidStringArrayProvider
     *
     * @param array<int, mixed> $infos
     */
    public function testNativeStringArrayPropertyRejectsPartiallyInvalidArrays(array $infos): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected "infos" to be of type {string[]}');

        new NativeCityData([
            'name' => 'Düsseldorf',
            'plz' => null,
            'infos' => $infos,
        ]);
    }

    /**
     * Risk: the same generic-array helper also drives phpdoc-backed property checks,
     * so mixed invalid arrays in docblock-only models would silently pass too.
     *
     * @dataProvider invalidStringArrayProvider
     *
     * @param array<int, mixed> $infos
     */
    public function testPhpDocStringArrayPropertyRejectsPartiallyInvalidArrays(array $infos): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected "infos" to be of type {string[]}');

        new CityData([
            'name' => 'Düsseldorf',
            'plz' => null,
            'infos' => $infos,
        ]);
    }

    /**
     * Risk: an empty `T[]` is still a valid array of `T`; rejecting it forces callers
     * to invent dummy values just to satisfy the checker.
     */
    public function testNativeStringArrayPropertyAcceptsEmptyArrays(): void
    {
        $model = new NativeCityData([
            'name' => 'Düsseldorf',
            'plz' => null,
            'infos' => [],
        ]);

        static::assertCount(0, $model['infos']);
    }

    /**
     * Risk: docblock-backed `T[]` should follow the same empty-array rule as native properties.
     */
    public function testPhpDocStringArrayPropertyAcceptsEmptyArrays(): void
    {
        $model = new CityData([
            'name' => 'Düsseldorf',
            'plz' => null,
            'infos' => [],
        ]);

        static::assertCount(0, $model['infos']);
    }

    /**
     * Risk: trimming only inside `assertTypeEquals()` leaves `getTypes()` polluted
     * with whitespace, which breaks callers that inspect normalized type metadata.
     */
    public function testTypeCheckSimpleNormalizesTrimmedTypes(): void
    {
        $typeCheck = new TypeCheckSimple(' string | int ');

        static::assertSame(['string', 'int'], $typeCheck->getTypes());
    }

    /**
     * Risk: direct `TypeCheckSimple` callers should get the same mixed-array rejection
     * as model property checks, regardless of where the invalid value appears.
     *
     * @dataProvider invalidStringArrayProvider
     *
     * @param array<int, mixed> $infos
     */
    public function testTypeCheckSimpleRejectsPartiallyInvalidGenericArrays(array $infos): void
    {
        $this->expectException(\TypeError::class);

        $typeCheck = new TypeCheckSimple('string[]');
        $typeCheck->checkType($infos);
    }

    public function testTypeCheckSimpleAcceptsEmptyGenericArrays(): void
    {
        $infos = [];
        $typeCheck = new TypeCheckSimple('string[]');

        static::assertTrue($typeCheck->checkType($infos));
    }

    /**
     * Risk: this library uses `float[]|int[]` as an element-union shorthand in collections; the
     * generic-array fix must not regress valid mixed numeric arrays while rejecting wrong types.
     */
    public function testTypeCheckSimpleSupportsUnionOfGenericArrayElementTypes(): void
    {
        $infos = [2.3, 2];
        $typeCheck = new TypeCheckSimple('float[]|int[]');

        static::assertTrue($typeCheck->checkType($infos));
    }

    /**
     * Risk: an untyped reflected property must remain effectively `mixed|null`; tightening it
     * would break classes that intentionally rely on loose assignment.
     */
    public function testFromReflectionPropertyWithoutTypeDefaultsToMixedNullable(): void
    {
        $typeCheck = TypeCheckPhpDoc::fromReflectionProperty(
            new \ReflectionProperty(TypeCheckNoTypeFixture::class, 'value')
        );

        $nullValue = null;
        $objectValue = new \stdClass();

        static::assertSame(['mixed'], $typeCheck->getTypes());
        static::assertTrue($typeCheck->checkType($nullValue));
        static::assertTrue($typeCheck->checkType($objectValue));
    }

    /**
     * Risk: if `@var` parsing regresses, docblock-declared pseudo-types and collection types
     * stop producing the normalized internal strings the checker relies on.
     */
    public function testFromReflectionPropertyParsesVarDocTypes(): void
    {
        $scalarTypeCheck = TypeCheckPhpDoc::fromReflectionProperty(
            new \ReflectionProperty(TypeCheckDocTypesFixture::class, 'scalarValue')
        );
        $objectTypeCheck = TypeCheckPhpDoc::fromReflectionProperty(
            new \ReflectionProperty(TypeCheckDocTypesFixture::class, 'objectValue')
        );
        $arrayTypeCheck = TypeCheckPhpDoc::fromReflectionProperty(
            new \ReflectionProperty(TypeCheckDocTypesFixture::class, 'stringArrayValue')
        );
        $resourceTypeCheck = TypeCheckPhpDoc::fromReflectionProperty(
            new \ReflectionProperty(TypeCheckDocTypesFixture::class, 'resourceValue')
        );

        $resource = \fopen('php://memory', 'rb');

        static::assertSame(['string|int|float|bool'], $scalarTypeCheck->getTypes());
        static::assertSame(['\\ArrayObject'], $objectTypeCheck->getTypes());
        static::assertSame(['string[]'], $arrayTypeCheck->getTypes());
        static::assertSame(['resource'], $resourceTypeCheck->getTypes());
        $scalarValue = 'foo';
        $arrayObject = new \ArrayObject();
        $stringArray = ['foo', 'bar'];

        static::assertTrue($scalarTypeCheck->checkType($scalarValue));
        static::assertTrue($objectTypeCheck->checkType($arrayObject));
        static::assertTrue($arrayTypeCheck->checkType($stringArray));
        static::assertTrue($resourceTypeCheck->checkType($resource));

        \fclose($resource);
    }

    /**
     * Risk: class-level `@property` tags are used heavily by `Arrayy`; if parsing one of the
     * supported pseudo-types regresses, runtime property validation becomes inconsistent.
     */
    public function testFromPhpDocumentorPropertyParsesSupportedPseudoTypes(): void
    {
        $docBlock = DocBlockFactory::createInstance()->create(<<<'DOC'
/**
 * @property scalar $scalarValue
 * @property callable $callableValue
 * @property \ArrayObject $objectValue
 * @property string[] $stringArrayValue
 */
DOC);

        $tags = $docBlock->getTagsByName('property');

        static::assertContainsOnlyInstancesOf(Property::class, $tags);

        $scalarTypeCheck = TypeCheckPhpDoc::fromPhpDocumentorProperty($tags[0]);
        $callableTypeCheck = TypeCheckPhpDoc::fromPhpDocumentorProperty($tags[1]);
        $objectTypeCheck = TypeCheckPhpDoc::fromPhpDocumentorProperty($tags[2]);
        $arrayTypeCheck = TypeCheckPhpDoc::fromPhpDocumentorProperty($tags[3]);

        static::assertSame(['string|int|float|bool'], $scalarTypeCheck->getTypes());
        static::assertSame(['callable'], $callableTypeCheck->getTypes());
        static::assertSame(['\\ArrayObject'], $objectTypeCheck->getTypes());
        static::assertSame(['string[]'], $arrayTypeCheck->getTypes());
    }

    /**
     * Risk: these scalar/null/mixed docblock tokens are mapped manually; if any branch drifts,
     * `Arrayy` starts rejecting valid values or formatting the wrong expected type.
     */
    public function testFromPhpDocumentorPropertyParsesScalarNullAndMixedKeywords(): void
    {
        $docBlock = DocBlockFactory::createInstance()->create(<<<'DOC'
/**
 * @property bool $boolValue
 * @property float $floatValue
 * @property string $stringValue
 * @property int $intValue
 * @property mixed $mixedValue
 * @property null $nullValue
 */
DOC);

        $tags = $docBlock->getTagsByName('property');

        $boolTypeCheck = TypeCheckPhpDoc::fromPhpDocumentorProperty($tags[0]);
        $floatTypeCheck = TypeCheckPhpDoc::fromPhpDocumentorProperty($tags[1]);
        $stringTypeCheck = TypeCheckPhpDoc::fromPhpDocumentorProperty($tags[2]);
        $intTypeCheck = TypeCheckPhpDoc::fromPhpDocumentorProperty($tags[3]);
        $mixedTypeCheck = TypeCheckPhpDoc::fromPhpDocumentorProperty($tags[4]);
        $nullTypeCheck = TypeCheckPhpDoc::fromPhpDocumentorProperty($tags[5]);

        $boolValue = true;
        $floatValue = 1.5;
        $stringValue = 'foo';
        $intValue = 42;
        $mixedValue = ['foo' => 'bar'];
        $nullValue = null;

        static::assertSame(['bool'], $boolTypeCheck->getTypes());
        static::assertSame(['float'], $floatTypeCheck->getTypes());
        static::assertSame(['string'], $stringTypeCheck->getTypes());
        static::assertSame(['int'], $intTypeCheck->getTypes());
        static::assertSame(['mixed'], $mixedTypeCheck->getTypes());
        static::assertSame(['null'], $nullTypeCheck->getTypes());
        static::assertTrue($boolTypeCheck->checkType($boolValue));
        static::assertTrue($floatTypeCheck->checkType($floatValue));
        static::assertTrue($stringTypeCheck->checkType($stringValue));
        static::assertTrue($intTypeCheck->checkType($intValue));
        static::assertTrue($mixedTypeCheck->checkType($mixedValue));
        static::assertTrue($nullTypeCheck->checkType($nullValue));
    }

    /**
     * Risk: `@var` metadata must continue to override native declarations because that is how
     * array element types like `string[]` and widened unions are represented today.
     */
    public function testFromReflectionPropertyPrefersVarDocOverNativeType(): void
    {
        $typeCheck = TypeCheckPhpDoc::fromReflectionProperty(
            new \ReflectionProperty(TypeCheckDocOverridesNativeFixture::class, 'value')
        );

        $value = 42;

        static::assertSame(['int', 'string'], $typeCheck->getTypes());
        static::assertTrue($typeCheck->checkType($value));
    }

    /**
     * Risk: native reflection parsing drives all typed-property enforcement; if named, union,
     * or nullable class types are parsed incorrectly, runtime checks drift from PHP's signatures.
     */
    public function testFromReflectionPropertyParsesNativeBuiltinUnionAndNullableClassTypes(): void
    {
        $idTypeCheck = TypeCheckPhpDoc::fromReflectionProperty(
            new \ReflectionProperty(NativeUserData::class, 'id')
        );
        $unionTypeCheck = TypeCheckPhpDoc::fromReflectionProperty(
            new \ReflectionProperty(NativeUserData::class, 'firstName')
        );
        $nullableClassTypeCheck = TypeCheckPhpDoc::fromReflectionProperty(
            new \ReflectionProperty(NativeUserData::class, 'city')
        );

        $intValue = 1;
        $stringValue = 'Lars';
        $nullValue = null;

        static::assertSame(['int'], $idTypeCheck->getTypes());
        static::assertSame(['string', 'int'], $unionTypeCheck->getTypes());
        static::assertSame(['\\Arrayy\\tests\\NativeCityData', 'null'], $nullableClassTypeCheck->getTypes());
        static::assertTrue($idTypeCheck->checkType($intValue));
        static::assertTrue($unionTypeCheck->checkType($stringValue));
        static::assertTrue($nullableClassTypeCheck->checkType($nullValue));
    }

    /**
     * Risk: `TypeCheckArray` bootstraps the meta map used by collection property checks; if its
     * constructor stops enforcing `TypeCheckInterface`, invalid entries slip into the registry.
     */
    public function testTypeCheckArrayRequiresTypeCheckEntries(): void
    {
        $typeChecks = new TypeCheckArray([
            'foo' => new TypeCheckSimple('int'),
        ]);

        static::assertSame(['foo'], $typeChecks->keys()->getArray());
        static::assertInstanceOf(TypeCheckSimple::class, $typeChecks['foo']);
    }

    /**
     * Risk: callable validators are used as an escape hatch for custom rules; both the happy
     * path and the failure path need explicit coverage so they do not silently change.
     */
    public function testTypeCheckCallbackValidatesAndSupportsNullableValues(): void
    {
        $nullableCheck = new TypeCheckCallback(static fn ($value): bool => \is_string($value), true);
        $failingCheck = new TypeCheckCallback(static fn ($value): bool => \is_string($value));

        $nullValue = null;
        $validValue = 'foo';

        static::assertSame([], $nullableCheck->getTypes());
        static::assertTrue($nullableCheck->checkType($nullValue));
        static::assertTrue($nullableCheck->checkType($validValue));

        $invalidValue = 123;

        $this->expectException(\TypeError::class);
        $failingCheck->checkType($invalidValue);
    }

    /**
     * @return iterable<string, array{0: array<int, mixed>}>
     */
    public static function invalidStringArrayProvider(): iterable
    {
        yield 'invalid-first' => [[1, 'valid']];
        yield 'invalid-last' => [['valid', 1]];
        yield 'invalid-middle' => [['first', 1, 'last']];
    }
}

final class TypeCheckNoTypeFixture
{
    public $value;
}

final class TypeCheckDocTypesFixture
{
    /**
     * @var scalar
     */
    public $scalarValue;

    /**
     * @var \ArrayObject
     */
    public $objectValue;

    /**
     * @var string[]
     */
    public $stringArrayValue;

    /**
     * @var resource
     */
    public $resourceValue;
}

final class TypeCheckDocOverridesNativeFixture
{
    /**
     * @var int|string
     */
    public string $value = '';
}
