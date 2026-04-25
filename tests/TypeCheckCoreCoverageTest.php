<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckCallback;
use Arrayy\TypeCheck\TypeCheckPhpDoc;
use Arrayy\TypeCheck\TypeCheckSimple;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPUnit\Framework\TestCase;

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
        try {
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
        } finally {
            \fclose($resource);
        }
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
     * Risk: `parseDocTypeObject()` has a Nullable branch that wraps the inner type in
     * ['innerType', 'null']. If this branch is removed or mis-ordered relative to
     * Compound handling, nullable @property types (e.g. `?City`) stop being accepted.
     */
    public function testFromDocTypeObjectNullableProducesNullableChecker(): void
    {
        $docBlock = DocBlockFactory::createInstance()->create(<<<'DOC'
/**
 * @property ?\ArrayObject $city
 */
DOC);
        $tag = $docBlock->getTagsByName('property')[0];

        $checker = TypeCheckPhpDoc::fromDocTypeObject('city', $tag->getType());

        static::assertSame(['\\ArrayObject', 'null'], $checker->getTypes());

        $nullValue = null;
        $objectValue = new \ArrayObject();
        static::assertTrue($checker->checkType($nullValue));
        static::assertTrue($checker->checkType($objectValue));
    }

    /**
     * Risk: `parseDocTypeObject()` has an ArrayShape branch that returns 'array' when a
     * shape value type is itself a nested array-shape. If this branch is removed, nesting
     * a shape inside a shape raises a "no branch matched" case and the type is silently
     * returned as the raw toString representation instead of 'array'.
     */
    public function testFromDocTypeObjectNestedArrayShapeYieldsArrayType(): void
    {
        $docBlock = DocBlockFactory::createInstance()->create(<<<'DOC'
/**
 * @template T of array{data: array{x: int}}
 */
DOC);
        $bound = $docBlock->getTagsByName('template')[0]->getBound();
        $nestedShapeType = $bound->getItems()[0]->getValue(); // array{x: int}

        $checker = TypeCheckPhpDoc::fromDocTypeObject('data', $nestedShapeType);

        // A nested array shape is collapsed to 'array'; only structural shape keys of
        // the *model* are registered as properties.
        static::assertSame(['array'], $checker->getTypes());
        $anyArray = ['x' => 42, 'extra' => true];
        static::assertTrue($checker->checkType($anyArray));
    }

    /**
     * Risk: happy path for a valid nested model value in an optional shape key. This
     * exercises the `Object_` branch of parseDocTypeObject for nullable class types and
     * ensures that a correctly-typed value for the `city` key is accepted at construction.
     */
    public function testArrayShapeOptionalKeyAcceptsValidObjectValue(): void
    {
        $meta = TypeCheckArrayShapeUserData::meta();
        $city = new \Arrayy\tests\CityData([
            'plz'   => null,
            'name'  => 'Düsseldorf',
            'infos' => ['lall'],
        ]);

        $model = new TypeCheckArrayShapeUserData([
            $meta->id        => 1,
            $meta->firstName => 'Lars',
            $meta->lastName  => 'Moelleken',
            $meta->infos     => ['a'],
            $meta->city      => $city,
        ]);

        static::assertInstanceOf(\Arrayy\tests\CityData::class, $model[$meta->city]);
        static::assertSame('Düsseldorf', $model[$meta->city]['name']);
    }

    /**
     * Risk: `city?: CityData|null` encodes two independent branches — the key may be omitted,
     * and when present the value may be either a CityData instance or null. Coverage must prove
     * explicit null is accepted, not only omission and object input.
     */
    public function testArrayShapeOptionalNullableKeyAcceptsExplicitNull(): void
    {
        $meta = TypeCheckArrayShapeUserData::meta();

        $model = new TypeCheckArrayShapeUserData([
            $meta->id        => 1,
            $meta->firstName => 'Lars',
            $meta->lastName  => 'Moelleken',
            $meta->infos     => ['a'],
            $meta->city      => null,
        ]);

        static::assertArrayHasKey($meta->city, $model->getArray());
        static::assertNull($model[$meta->city]);
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

    public function testArrayShapeTemplateProvidesPropertyDefinitions(): void
    {
        $meta = TypeCheckArrayShapeUserData::meta();
        $model = new TypeCheckArrayShapeUserData([
            $meta->id        => 1,
            $meta->firstName => 'Lars',
            $meta->lastName  => 'Moelleken',
            $meta->infos     => ['foo'],
        ]);

        static::assertSame('id', $meta->id);
        static::assertSame('city', $meta->city);
        static::assertSame('Lars', $model[$meta->firstName]);
    }

    public function testArrayShapeTemplateRejectsInvalidPropertyTypes(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type: expected "infos" to be of type {string[]}');

        $meta = TypeCheckArrayShapeUserData::meta();
        new TypeCheckArrayShapeUserData([
            $meta->id        => 1,
            $meta->firstName => 'Lars',
            $meta->lastName  => 'Moelleken',
            $meta->infos     => [1],
        ]);
    }

    public function testArrayShapeTemplateRejectsUnknownProperties(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('The key "unknown" does not exist');

        $meta = TypeCheckArrayShapeUserData::meta();
        new TypeCheckArrayShapeUserData([
            $meta->id        => 1,
            $meta->firstName => 'Lars',
            $meta->lastName  => 'Moelleken',
            $meta->infos     => ['foo'],
            'unknown'        => 'value',
        ]);
    }

    public function testPropertyTagsAndArrayShapeTemplateCannotBeMixed(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Use either @property tags or array-shape annotations');

        new TypeCheckMixedPropertyAnnotationsData(['id' => 1]);
    }

    public function testPropertyTagsAndArrayShapeAnnotationsCannotBeMixedAcrossInheritance(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Use either @property tags or array-shape annotations');

        new TypeCheckMixedPropertyAnnotationsInheritanceData(['id' => 1]);
    }

    /**
     * Risk: optional shape keys skip the constructor-mismatch check but must still go
     * through `checkType()` when a value is actually supplied; removing the per-key
     * `$this->properties[$key]->checkType($value)` call would silently accept any value.
     */
    public function testArrayShapeOptionalKeyIsTypeCheckedWhenPresent(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Invalid type');

        $meta = TypeCheckArrayShapeUserData::meta();
        new TypeCheckArrayShapeUserData([
            $meta->id        => 1,
            $meta->firstName => 'Lars',
            $meta->lastName  => 'Moelleken',
            $meta->infos     => ['foo'],
            $meta->city      => new \stdClass(), // wrong type – must throw
        ]);
    }

    /**
     * Risk: "optional" means the array key may be absent from the input, not that null
     * is a valid value. If the `isOptional` flag were incorrectly widened to imply
     * nullable, the type check would be silently skipped for null values.
     *
     * This exercises the post-construction (offsetSet) path which always runs checkType.
     */
    public function testArrayShapeOptionalNonNullableKeyRejectsNull(): void
    {
        $this->expectException(\TypeError::class);

        // TypeCheckArrayShapeScoreData defines score?: int (optional, not nullable)
        $model = new TypeCheckArrayShapeScoreData([]);
        $model['score'] = null; // offsetSet always calls checkType; null ≠ int → throws
    }

    /**
     * Risk: if the `$requiredProperties = array_diff_key(…)` split were removed and all
     * shape properties were treated as required, constructing the same model a second
     * time (the cached path) would fail to exclude optional keys from the mismatch check.
     *
     * Uses a dedicated fixture so the cache state is deterministic within the test.
     */
    public function testArrayShapeCachingPreservesOptionalPropertiesOnSecondInstantiation(): void
    {
        // First construction hits the uncached path.
        $first = new TypeCheckArrayShapeCacheTestModel(['name' => 'Alice']);
        // Second construction hits the CACHED path – optionalProperties must be restored.
        $second = new TypeCheckArrayShapeCacheTestModel(['name' => 'Bob']);

        static::assertSame('Alice', $first['name']);
        static::assertSame('Bob', $second['name']);
        // Neither construction should have thrown a "Property mismatch" error because
        // 'tag' is optional; if the cache fix is missing, the second would throw.
    }

    /**
     * Risk: the guard `$tag->getTemplateName() === 'T'` ensures that only the canonical
     * `@template T of array{…}` form is used as a property map. If the check were
     * removed, ANY template bound to an array shape would be treated as property metadata.
     */
    public function testArrayShapeTemplateNameOtherThanTIsIgnored(): void
    {
        // TypeCheckArrayShapeWrongTemplateName uses @template Data of array{id: int}.
        // The name guard must skip it, so no properties are registered and any key is
        // accepted without type checking.
        $model = new TypeCheckArrayShapeWrongTemplateName(['id' => 'not-an-int']);
        static::assertSame('not-an-int', $model['id']);
    }

    /**
     * Risk: the @extends inline-shape form (`@extends Arrayy<array{…}, mixed>`) must be
     * parsed even without a preceding @template T preamble. Removing that branch of
     * `getArrayShapeItemsFromDocBlock()` would silently drop this annotation style.
     */
    public function testArrayShapeInlineExtendsFormParsesShapeProperties(): void
    {
        $meta = TypeCheckArrayShapeExtendsOnlyData::meta();
        static::assertSame('score', $meta->score);

        $model = new TypeCheckArrayShapeExtendsOnlyData([$meta->score => 42]);
        static::assertSame(42, $model[$meta->score]);
    }

    public function testIntermediateArrayyExtendsShapeIsExposedViaMeta(): void
    {
        $meta = TypeCheckArrayShapeViaIntermediateBaseData::meta();

        static::assertSame('id', $meta->id);
    }

    public function testIntermediateArrayyExtendsShapeRejectsInvalidPropertyTypes(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('#Invalid type: expected "id" to be of type \{int\}#');

        new TypeCheckArrayShapeViaIntermediateBaseData(['id' => 'not-an-int']);
    }

    /**
     * Risk: the @extends FQCN guard (`in_array(…, [Arrayy::class, ArrayyStrict::class])`)
     * must prevent shapes on unrelated classes from being parsed as property metadata.
     * Removing the guard would inject spurious property definitions.
     */
    public function testNonArrayyExtendsShapeIsNotParsed(): void
    {
        // TypeCheckNonArrayyExtendsData has @extends stdClass<array{id: int}, mixed>.
        // The FQCN guard skips stdClass, so no shape properties should be registered;
        // any key/value combination must be accepted without type checking.
        $model = new TypeCheckNonArrayyExtendsData(['id' => 'not-an-int', 'extra' => true]);
        static::assertSame('not-an-int', $model['id']);
        static::assertSame(true, $model['extra']);
    }

    /**
     * Risk: `fromDocTypeObject()` is called with $type = null when a shape item carries
     * no explicit type annotation. If the null guard (`if ($type) { … }`) were removed,
     * the function would attempt `parseDocTypeObject(null)` and crash. The checker
     * it returns must have an empty types list.
     *
     * A checker with empty types is maximally restrictive (it throws for all values),
     * which is intentional; callers must not treat "no type annotation" as "accept all".
     */
    public function testFromDocTypeObjectWithNullTypeReturnsCheckerWithEmptyTypes(): void
    {
        $checker = TypeCheckPhpDoc::fromDocTypeObject('myProp', null);

        static::assertSame([], $checker->getTypes());

        // An empty types list means there is no declared type that can match, so
        // checkType() throws for every value – verify the production-safe null path.
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('expected "myProp" to be of type {}');
        $nullValue = null;
        $checker->checkType($nullValue);
    }

    public function testFromDocTypeObjectWithParsedTypeKeepsPropertyNameInErrors(): void
    {
        $docBlock = DocBlockFactory::createInstance()->create(<<<'DOC'
/**
 * @property int $myProp
 */
DOC);
        $tag = $docBlock->getTagsByName('property')[0];

        $checker = TypeCheckPhpDoc::fromDocTypeObject('myProp', $tag->getType());

        static::assertSame(['int'], $checker->getTypes());

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('expected "myProp" to be of type {int}');
        $value = 'not-an-int';
        $checker->checkType($value);
    }

    /**
     * Risk: all shape keys, including optional ones, must be surfaced by meta() so that
     * callers can use `$meta->city` as a type-safe key reference even when city may be
     * absent from the constructor input. If optional keys were excluded from the property
     * map, meta() would return empty strings for them.
     */
    public function testArrayShapeMetaIncludesAllKeysIncludingOptional(): void
    {
        $meta = TypeCheckArrayShapeUserData::meta();

        static::assertSame('id', $meta->id);
        static::assertSame('firstName', $meta->firstName);
        static::assertSame('lastName', $meta->lastName);
        static::assertSame('city', $meta->city);   // optional key
        static::assertSame('infos', $meta->infos);
    }

    /**
     * Risk: post-construction writes via offsetSet must continue to be type-checked;
     * removing checkType() from internalSet() would allow any value after construction.
     */
    public function testArrayShapePostConstructionTypeCheckEnforced(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('#Invalid type: expected "id" to be of type \{int\}#');

        $meta = TypeCheckArrayShapeUserData::meta();
        $model = new TypeCheckArrayShapeUserData([
            $meta->id        => 1,
            $meta->firstName => 'Lars',
            $meta->lastName  => 'M',
            $meta->infos     => [],
        ]);
        $model[$meta->id] = 'not-an-int'; // offsetSet → internalSet(…, true) → checkType
    }

    /**
     * Risk: post-construction writes of unknown keys must be rejected when
     * checkPropertiesMismatch is true; removing the key-existence guard in checkType()
     * would allow new keys to be silently injected into a shape-typed model.
     */
    public function testArrayShapePostConstructionMismatchEnforced(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('The key "ghost" does not exist');

        $meta = TypeCheckArrayShapeUserData::meta();
        $model = new TypeCheckArrayShapeUserData([
            $meta->id        => 1,
            $meta->firstName => 'Lars',
            $meta->lastName  => 'M',
            $meta->infos     => [],
        ]);
        $model['ghost'] = 'injected'; // must throw – 'ghost' is not in the shape
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

/**
 * @template T of array{id: int, firstName: int|string, lastName: string, city?: \Arrayy\tests\CityData|null, infos: string[]}
 * @extends \Arrayy\Arrayy<key-of<T>, value-of<T>>
 */
final class TypeCheckArrayShapeUserData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkForMissingPropertiesInConstructor = true;

    protected $checkPropertiesMismatchInConstructor = true;
}

/**
 * @property int $legacyId
 * @template T of array{id: int}
 * @extends \Arrayy\Arrayy<key-of<T>, value-of<T>>
 */
final class TypeCheckMixedPropertyAnnotationsData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;
}

/**
 * @property int $legacyId
 * @extends \Arrayy\Arrayy<array-key, mixed>
 */
abstract class TypeCheckPropertyTagParentData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;
}

/**
 * @template T of array{id: int}
 */
final class TypeCheckMixedPropertyAnnotationsInheritanceData extends TypeCheckPropertyTagParentData
{
}

/**
 * Optional key whose value type does NOT include null. "Optional" means the key
 * may be absent; null must still be rejected when the key is present.
 *
 * @template T of array{score?: int}
 * @extends \Arrayy\Arrayy<key-of<T>, value-of<T>>
 */
final class TypeCheckArrayShapeScoreData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatch = true;
}

/**
 * Used exclusively by testArrayShapeCachingPreservesOptionalPropertiesOnSecondInstantiation
 * to exercise the static-cache restore of $optionalProperties.
 *
 * @template T of array{name: string, tag?: string}
 * @extends \Arrayy\Arrayy<key-of<T>, value-of<T>>
 */
final class TypeCheckArrayShapeCacheTestModel extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatch = true;

    protected $checkPropertiesMismatchInConstructor = true;

    protected $checkForMissingPropertiesInConstructor = true;
}

/**
 * Uses the inline @extends form to define the shape, with no @template T preamble.
 * Both `@template T of array{…}` and `@extends Arrayy<array{…}, …>` must be supported.
 *
 * @extends \Arrayy\Arrayy<array{score: int}, mixed>
 */
final class TypeCheckArrayShapeExtendsOnlyData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatch = true;
}

/**
 * @template TShape
 * @template TValue
 * @extends \Arrayy\Arrayy<array-key, mixed>
 */
abstract class TypeCheckCustomArrayyBase extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatch = true;

    protected $checkForMissingPropertiesInConstructor = true;
}

/**
 * @extends \Arrayy\tests\TypeCheckCustomArrayyBase<array{id: int}, mixed>
 */
final class TypeCheckArrayShapeViaIntermediateBaseData extends TypeCheckCustomArrayyBase
{
}

/**
 * Uses @template with a name other than T – must be silently ignored by the
 * `getTemplateName() === 'T'` guard in getArrayShapeItemsFromDocBlock().
 *
 * @template Data of array{id: int}
 * @extends \Arrayy\Arrayy<array-key, mixed>
 */
final class TypeCheckArrayShapeWrongTemplateName extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;
}

/**
 * Test fixture: the @extends annotation intentionally names a non-Arrayy class (stdClass)
 * to verify that the FQCN guard in getArrayShapeItemsFromDocBlock() ignores the shape.
 * The class itself still extends \Arrayy\Arrayy as normal.
 *
 * @extends stdClass<array{id: int}, mixed>
 */
final class TypeCheckNonArrayyExtendsData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;
}
