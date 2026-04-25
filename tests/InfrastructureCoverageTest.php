<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\Arrayy;
use Arrayy\ArrayyIterator;
use Arrayy\ArrayyMeta;
use Arrayy\ArrayyRewindableGenerator;
use Arrayy\tests\Collection\StdClassCollection;
use Arrayy\Type\DetectFirstValueTypeCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class InfrastructureCoverageTest extends TestCase
{
    public function testArrayyIteratorOffsetGetWrapsNestedArrays(): void
    {
        $iterator = new ArrayyIterator([['foo' => 'bar']], 0, Arrayy::class);

        $current = $iterator->offsetGet(0);

        static::assertInstanceOf(Arrayy::class, $current);
        static::assertSame('bar', $current->get('foo'));
    }

    public function testArrayyMetaReturnsEmptyStringForUnknownProperties(): void
    {
        static::assertSame('', (new ArrayyMeta())->__get('missing'));
    }

    public function testArrayyRewindableGeneratorInvokesRewindCallback(): void
    {
        $rewinds = 0;
        $iterator = new ArrayyRewindableGenerator(
            static function (): \Generator {
                yield 'foo' => 'bar';
            },
            static function () use (&$rewinds): void {
                ++$rewinds;
            }
        );

        $iterator->rewind();

        static::assertSame(1, $rewinds);
        static::assertTrue($iterator->valid());
        static::assertSame('foo', $iterator->key());
        static::assertSame('bar', $iterator->current());
    }

    public function testArrayyRewindableGeneratorRejectsNonGeneratorFactories(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The callable needs to return a Generator');

        new ArrayyRewindableGenerator(static fn (): array => []);
    }

    public function testDetectFirstValueTypeCollectionWrapsScalarInput(): void
    {
        $collection = new DetectFirstValueTypeCollection($this->mixedValue('A'));

        static::assertSame(['A'], $collection->toArray());
        static::assertSame('string', $collection->getType());
    }

    public function testTypeStdClassCollectionReturnsStdClassType(): void
    {
        $collection = new \Arrayy\Type\StdClassCollection([new \stdClass()]);

        static::assertSame(\stdClass::class, $collection->getType());
    }

    public function testAbstractCollectionExpandsNestedCollectionsForOffsetSetPrependAndSet(): void
    {
        $first = (object) ['name' => 'first'];
        $second = (object) ['name' => 'second'];

        $collection = new StdClassCollection();
        $collection[] = $this->mixedValue(new StdClassCollection([$first]));
        $collection->prepend($this->mixedValue(new StdClassCollection([$second])), 'lead');
        $collection->set('tail', $this->mixedValue(new StdClassCollection([$first, $second])));

        static::assertSame($second, $collection['lead']);
        static::assertSame($first, $collection[0]);
        static::assertSame($second, $collection['tail']);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function mixedValue($value)
    {
        return $value;
    }
}
