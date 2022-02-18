<?php

/** @noinspection ClassOverridesFieldOfSuperClassInspection */
/** @noinspection PropertyInitializationFlawsInspection */
/** @noinspection PhpSuperClassIncompatibleWithInterfaceInspection */

declare(strict_types=1);

namespace Arrayy\Collection;

use Arrayy\Arrayy;
use Arrayy\ArrayyIterator;
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
     * @phpstan-param array<TKey,T>|\Arrayy\Arrayy<TKey,T>|\Closure():array<TKey,T>|mixed $data
     * @phpstan-param class-string<\Arrayy\ArrayyIterator> $iteratorClass
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
     * Append a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return $this
     *               <p>(Mutable) Return this CollectionInterface object, with the appended values.</p>
     *
     * @phpstan-param T|static $value
     * @phpstan-param TKey|null $key
     * @phpstan-return static<TKey,T>
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

        /** @phpstan-ignore-next-line | special? */
        $return = parent::append($value, $key);
        $this->array = $return->array;
        $this->generator = null;

        return $this;
    }

    /**
     * Assigns a value to the specified offset + check the type.
     *
     * @param int|string|null $offset
     * @param mixed           $value
     *
     * @return void
     *
     * @phpstan-param TKey $offset
     * @phpstan-param T $value
     */
    #[\ReturnTypeWillChange]
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
     * Prepend a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return $this
     *               <p>(Mutable) Return this CollectionInterface object, with the prepended value.</p>
     *
     * @phpstan-param T|static $value
     * @phpstan-param TKey|null $key
     * @phpstan-return static<TKey,T>
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

        /** @phpstan-ignore-next-line | special? */
        $return = parent::prepend($value, $key);
        $this->array = $return->array;
        $this->generator = null;

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
     * @return array
     *
     * @phpstan-return array<T>
     */
    public function getCollection(): array
    {
        return $this->getArray();
    }

    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string|string[]|TypeCheckArray|TypeCheckInterface[]
     *
     * @phpstan-return string|string[]|class-string|class-string[]|TypeCheckArray<array-key,TypeCheckInterface>|TypeCheckInterface[]
     */
    abstract public function getType();

    /**
     * Merge current items and items of given collections into a new one.
     *
     * @param CollectionInterface|static ...$collections
     *                                                   <p>The collections to merge.</p>
     *
     *@throws \InvalidArgumentException if any of the given collections are not of the same type
     *
     * @return $this
     *
     * @phpstan-param CollectionInterface<TKey,T> ...$collections
     * @phpstan-return static<TKey,T>
     */
    public function merge(CollectionInterface ...$collections): self
    {
        foreach ($collections as $collection) {
            foreach ($collection as $item) {
                $this->append($item);
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
     * @template     TKeyCreate as int|string
     * @template     TCreate
     *
     * @phpstan-param  array<TKeyCreate,TCreate> $data
     * @phpstan-param  class-string<\Arrayy\ArrayyIterator> $iteratorClass
     * @phpstan-return static<TKeyCreate,TCreate>
     *
     * @psalm-mutation-free
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
     * @param string $json
     *
     * @return static
     *                <p>(Immutable) Returns an new instance of the CollectionInterface object.</p>
     *
     * @phpstan-return static<int,T>
     *
     * @psalm-mutation-free
     */
    public static function createFromJsonMapper(string $json)
    {
        // init
        $return = static::create();
        $jsonObject = \json_decode($json, false);
        $mapper = new \Arrayy\Mapper\Json();
        $mapper->undefinedPropertyHandler = static function ($object, $key, $jsonValue) use ($return) {
            if ($return->checkForMissingPropertiesInConstructor) {
                throw new \TypeError('Property mismatch - input: ' . \print_r(['key' => $key, 'jsonValue' => $jsonValue], true) . ' for object: ' . \get_class($object));
            }
        };

        $type = $return->getType();

        if (
            \is_string($type)
            &&
            \class_exists($type)
        ) {
            if (\is_array($jsonObject)) {
                foreach ($jsonObject as $jsonObjectSingle) {
                    $collectionData = $mapper->map($jsonObjectSingle, $type);
                    $return->add($collectionData);
                }
            } else {
                $collectionData = $mapper->map($jsonObject, $type);
                $return->add($collectionData);
            }
        } else {
            foreach ($jsonObject as $key => $jsonValue) {
                $return->add($jsonValue, $key);
            }
        }

        /** @phpstan-var static<int,T> */
        return $return;
    }

    /**
     * Internal mechanic of set method.
     *
     * @param int|string|null $key
     * @param mixed           $value
     * @param bool            $checkProperties
     *
     * @return bool
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
     * @phpstan-param null|string|string[]|class-string|class-string[]|TypeCheckArray<array-key,TypeCheckInterface>|array<array-key,TypeCheckInterface>|mixed $type
     * @phpstan-return TypeCheckArray<array-key,TypeCheckInterface>
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
