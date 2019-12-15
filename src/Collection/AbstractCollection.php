<?php

/** @noinspection ClassOverridesFieldOfSuperClassInspection */
/** @noinspection PropertyInitializationFlawsInspection */
/** @noinspection PhpSuperClassIncompatibleWithInterfaceInspection */

declare(strict_types=1);

namespace Arrayy\Collection;

use Arrayy\Arrayy;
use Arrayy\ArrayyIterator;
use Arrayy\ArrayyRewindableGenerator;
use Arrayy\Type\TypeInterface;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckInterface;
use Arrayy\TypeCheck\TypeCheckSimple;

/**
 * This class provides a full implementation of `CollectionInterface`, to
 * minimize the effort required to implement this interface.
 *
 * INFO: this collection thingy is inspired by https://github.com/ramsey/collection/
 *
 * @template   TKey of array-key
 * @template   T
 * @extends    Arrayy<TKey,T>
 * @implements CollectionInterface<TKey,T>
 */
abstract class AbstractCollection extends Arrayy implements CollectionInterface
{
    /**
     * @var array
     * @psalm-var array<T>
     */
    protected $array = [];

    /**
     * @var ArrayyRewindableGenerator|null
     * @psalm-var \Arrayy\ArrayyRewindableGenerator<TKey,T>|null
     */
    protected $generator;

    /**
     * @var bool
     */
    protected $checkPropertyTypes = true;

    /**
     * @var bool
     */
    protected $checkPropertiesMismatch = false;

    /**
     * @var bool
     */
    protected $checkForMissingPropertiesInConstructor = true;

    /**
     * Constructs a collection object of the specified type, optionally with the
     * specified data.
     *
     * @param mixed  $data
     *                                             <p>
     *                                             The initial items to store in the collection.
     *                                             </p>
     * @param string $iteratorClass                optional <p>
     *                                             You can overwrite the ArrayyIterator, but mostly you don't
     *                                             need this option.
     *                                             </p>
     * @param bool   $checkPropertiesInConstructor optional <p>
     *                                             You need to extend the "Arrayy"-class and you need to set
     *                                             the $checkPropertiesMismatchInConstructor class property
     *                                             to
     *                                             true, otherwise this option didn't not work anyway.
     *                                             </p>
     *
     * @psalm-param array<TKey,T>|\Arrayy\Arrayy<TKey,T>|\Closure():array<TKey,T>|mixed $data
     * @psalm-param class-string<\Arrayy\ArrayyIterator> $iteratorClass
     */
    public function __construct(
        $data = [],
        string $iteratorClass = ArrayyIterator::class,
        bool $checkPropertiesInConstructor = true
    ) {
        $type = $this->getType();

        $type = self::convertIntoTypeCheckArray($type);

        $this->properties = $type;

        // cast into array, if needed
        if (
            !\is_array($data)
            &&
            !($data instanceof \Traversable)
            &&
            !($data instanceof \Closure)
        ) {
            $data = [$data];
        }

        parent::__construct(
            $data,
            $iteratorClass,
            $checkPropertiesInConstructor
        );
    }

    /**
     * {@inheritdoc}
     */
    public function append($value, $key = null): Arrayy
    {
        if (
            $value instanceof self
            &&
            !$value instanceof TypeInterface
        ) {
            foreach ($value as $valueTmp) {
                parent::append($valueTmp, $key);
            }

            return $this;
        }

        $return = parent::append($value, $key);
        $this->array = $return->array;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (
            $value instanceof self
            &&
            !$value instanceof TypeInterface
        ) {
            foreach ($value as $valueTmp) {
                parent::offsetSet($offset, $valueTmp);
            }

            return;
        }

        parent::offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend($value, $key = null): Arrayy
    {
        if (
            $value instanceof self
            &&
            !$value instanceof TypeInterface
        ) {
            foreach ($value as $valueTmp) {
                parent::prepend($valueTmp, $key);
            }

            return $this;
        }

        $return = parent::prepend($value, $key);
        $this->array = $return->array;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function column(string $keyOrPropertyOrMethod): array
    {
        // init
        $temp = [];

        foreach ($this->getGenerator() as $item) {
            $temp[] = $this->extractValue($item, $keyOrPropertyOrMethod);
        }

        return $temp;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(): array
    {
        return $this->array;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getType();

    /**
     * Merge current items and items of given collections into a new one.
     *
     * @param CollectionInterface|static ...$collections The collections to merge.
     *
     * @throws \InvalidArgumentException if any of the given collections are not of the same type
     *
     * @return $this
     *
     * @psalm-param  array<CollectionInterface<TKey,T>> ...$collections
     * @psalm-return $this<TKey,T>
     */
    public function merge(CollectionInterface ...$collections): self
    {
        foreach ($collections as $collection) {
            if ($collection instanceof Arrayy) {
                foreach ($collection as $item) {
                    $this->append($item);
                }
            }
        }

        return $this;
    }

    /**
     * Creates an CollectionInterface object.
     *
     * @param mixed  $data
     * @param string $iteratorClass
     * @param bool   $checkPropertiesInConstructor
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the CollectionInterface object.</p>
     *
     * @psalm-param  array<T> $data
     * @psalm-param  class-string<\Arrayy\ArrayyIterator> $iteratorClass
     * @psalm-return static<TKey,T>
     */
    public static function create(
        $data = [],
        string $iteratorClass = ArrayyIterator::class,
        bool $checkPropertiesInConstructor = true
    ) {
        return new static(
            $data,
            $iteratorClass,
            $checkPropertiesInConstructor
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function internalSet(
        $key,
        &$value,
        bool $checkProperties = true
    ): bool {
        if (
            $value instanceof self
            &&
            !$value instanceof TypeInterface
        ) {
            foreach ($value as $valueTmp) {
                parent::internalSet(
                    $key,
                    $valueTmp,
                    $checkProperties
                );
            }

            return true;
        }

        return parent::internalSet(
            $key,
            $value,
            $checkProperties
        );
    }

    /**
     * @param string|string[]|TypeCheckArray|TypeCheckInterface[]|null $type
     *
     * @return TypeCheckArray
     *
     * @psalm-param null|string|string[]|class-string|class-string[]|TypeCheckArray<TypeCheckInterface>|array<TypeCheckInterface>|mixed $type
     * @psalm-return TypeCheckArray<int,TypeCheckInterface>
     */
    protected static function convertIntoTypeCheckArray($type): TypeCheckArray
    {
        $is_array = false;
        if (
            \is_scalar($type)
            ||
            $is_array = \is_array($type)
        ) {
            $type = TypeCheckArray::create(
                [
                    Arrayy::ARRAYY_HELPER_TYPES_FOR_ALL_PROPERTIES => new TypeCheckSimple($is_array ? $type : (string) $type),
                ]
            );
        }

        return $type;
    }
}
