<?php

declare(strict_types=1);

namespace Arrayy\Collection;

use Arrayy\ArrayyIterator;
use Arrayy\Type\TypeInterface;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckInterface;

/**
 * A collection represents a group of objects.
 *
 * Each object in the collection is of a specific, defined type.
 *
 * This is a direct implementation of `CollectionSetTypeInterface`,
 * which provides a simple api for your collections.
 *
 * Example usage:
 *
 * ``` php
 * $collection = new \Arrayy\Collection\Collection(\My\FooInterface::class);
 * $collection->add(new \My\Foo());
 * $collection->add(new \My\Foo());
 *
 * foreach ($collection as $foo) {
 *     if ($foo instanceof \My\FooInterface) {
 *         // Do something with $foo
 *     }
 * }
 * ```
 *
 * It is preferable to subclass `AbstractCollection` to create your own typed
 * collections. For example:
 *
 * ``` php
 * namespace My;
 * /**
 *  * @extends \Arrayy\Collection\AbstractCollection<array-key,FooInterface>
 *  *\/
 * class FooCollection extends \Arrayy\Collection\AbstractCollection
 * {
 *     public function getType()
 *     {
 *         return FooInterface::class;
 *     }
 * }
 * ```
 *
 * And then use it similarly to the earlier example:
 *
 * ``` php
 * namespace My;
 *
 * $fooCollection = new \My\FooCollection();
 * $fooCollection->add(new \My\Foo());
 * $fooCollection->add(new \My\Foo());
 *
 * foreach ($fooCollection as $foo) {
 *     if ($foo instanceof \My\FooInterface) {
 *         // Do something with $foo
 *     }
 * }
 * ```
 *
 * INFO: this collection thingy is inspired by https://github.com/ramsey/collection/
 *
 * @template TKey of array-key
 * @template T
 * @extends  AbstractCollection<TKey,T>
 */
class Collection extends AbstractCollection
{
    /**
     * Constructs a collection object of the specified type, optionally with the
     * specified data.
     *
     * @param mixed              $data
     *                                                         <p>
     *                                                         The initial items to store in the
     *                                                         collection.
     *                                                         </p>
     * @param string|null        $iteratorClass                optional <p>
     *                                                         You can overwrite the
     *                                                         ArrayyIterator, but mostly you
     *                                                         don't need this option.
     *                                                         </p>
     * @param bool               $checkPropertiesInConstructor optional <p>
     *                                                         You need to extend the
     *                                                         "Arrayy"-class and you need to set
     *                                                         the
     *                                                         $checkPropertiesMismatchInConstructor
     *                                                         class property to true, otherwise
     *                                                         this option didn't not work
     *                                                         anyway.
     *                                                         </p>
     * @param TypeInterface|null $type
     *
     * @phpstan-param array<array-key,T>|array<TKey,T>|\Arrayy\Arrayy<TKey,T> $data
     * @phpstan-param class-string<\Arrayy\ArrayyIterator> $iteratorClass
     */
    public function __construct(
        $data = [],
        string $iteratorClass = null,
        bool $checkPropertiesInConstructor = null,
        TypeInterface $type = null
    ) {
        // fallback
        if ($iteratorClass === null) {
            $iteratorClass = ArrayyIterator::class;
        }
        if ($checkPropertiesInConstructor === null) {
            $checkPropertiesInConstructor = true;
        }

        if ($type !== null) {
            /** @phpstan-ignore-next-line - we use the "TypeInterface" only as base */
            $this->properties = $type;
        }

        parent::__construct(
            $data,
            $iteratorClass,
            $checkPropertiesInConstructor
        );
    }

    /**
     * @param string|TypeCheckArray|TypeCheckInterface[] $type
     * @param array<mixed>                               $data
     * @param bool                                       $checkPropertiesInConstructorAndType
     *
     * @return static
     *
     * @template       TConstruct
     * @phpstan-param  string|class-string|class-string<TConstruct>|TypeInterface|TypeCheckArray<array-key,TypeCheckInterface>|array<TypeCheckInterface> $type
     * @phpstan-param  array<array-key,TConstruct> $data
     * @phpstan-return static<array-key,TConstruct>
     */
    public static function construct(
        $type,
        $data = [],
        bool $checkPropertiesInConstructorAndType = true
    ): self {
        $type = self::convertIntoTypeCheckArray($type);

        return new static(
            $data,
            ArrayyIterator::class,
            $checkPropertiesInConstructorAndType,
            $type
        );
    }

    /**
     * Returns a new iterator, thus implementing the \Iterator interface.
     *
     * @return \Iterator
     *                   <p>An iterator for the values in the array.</p>
     *
     * @phpstan-return \Iterator<T>
     *
     * @noinspection SenselessProxyMethodInspection
     */
    public function getIterator(): \Iterator
    {
        return parent::getIterator();
    }

    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string|TypeCheckArray|TypeCheckInterface[]
     *
     * @phpstan-return string|class-string|class-string<T>|TypeInterface|TypeCheckArray<TKey,T>|TypeCheckArray<int|string,mixed>|array<TypeCheckInterface>|array<array-key,TypeCheckInterface>
     */
    public function getType()
    {
        return $this->properties;
    }

    /**
     * Get a base Collection instance from this Collection.
     *
     * @return self
     *
     * @phpstan-return self<array-key,mixed>
     */
    public function toBase(): self
    {
        return self::construct(
            $this->getType(),
            $this->getArray()
        );
    }
}
