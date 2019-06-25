<?php

namespace Arrayy\Collection;

use Arrayy\ArrayyIterator;

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
 *
 * class FooCollection extends \Arrayy\Collection\AbstractCollection
 * {
 *     public function getType(): string
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
 */
class Collection extends AbstractCollection
{
    /**
     * The type of elements stored in this collection.
     *
     * @var string
     */
    private $collectionTypeTmp;

    /**
     * Constructs a collection object of the specified type, optionally with the
     * specified data.
     *
     * @param string $type
     * @param mixed  $data
     *                                                              <p>
     *                                                              The initial items to store in the collection.
     *                                                              </p>
     * @param bool   $checkForMissingPropertiesInConstructorAndType
     */
    public function __construct(string $type, $data = [], $checkForMissingPropertiesInConstructorAndType = true)
    {
        $this->collectionTypeTmp = $type;

        parent::__construct($data, ArrayyIterator::class, $checkForMissingPropertiesInConstructorAndType);
    }

    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->collectionTypeTmp;
    }

    /**
     * Get a base Collection instance from this Collection.
     *
     * @return self
     */
    public function toBase(): self
    {
        return new self($this);
    }
}
