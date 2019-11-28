<?php

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
     * @return static[]
     */
    public function getCollection(): array
    {
        return $this->array;
    }

    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string|string[]|TypeCheckArray|TypeCheckInterface[]
     */
    abstract public function getType();

    /**
     * Merge current items and items of given collections into a new one.
     *
     * @param CollectionInterface ...$collections The collections to merge.
     *
     * @throws \InvalidArgumentException if any of the given collections are not of the same type
     *
     * @return static
     */
    public function merge(CollectionInterface ...$collections): CollectionInterface
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
     * Assigns a value to the specified offset + check the type.
     *
     * @param int|string|null $offset
     * @param mixed           $value
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
     * Prepend a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with the prepended value.</p>
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

        return parent::prepend($value, $key);
    }

    /**
     * Append a (key) + value to the current array.
     *
     * @param mixed $value
     * @param mixed $key
     *
     * @return static
     *                <p>(Mutable) Return this Arrayy object, with the appended values.</p>
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

        return parent::append($value, $key);
    }

    /**
     * Returns the values from given property or method.
     *
     * @param string $keyOrPropertyOrMethod the property or method name to filter by
     *
     * @throws \InvalidArgumentException if property or method is not defined
     *
     * @return array
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
     * Returns a collection of matching items.
     *
     * @param string $keyOrPropertyOrMethod the property or method to evaluate
     * @param mixed  $value                 the value to match
     *
     * @throws \InvalidArgumentException if property or method is not defined
     *
     * @return static
     */
    public function where(string $keyOrPropertyOrMethod, $value): CollectionInterface
    {
        return $this->filter(
            function ($item) use ($keyOrPropertyOrMethod, $value) {
                $accessorValue = $this->extractValue(
                    $item,
                    $keyOrPropertyOrMethod
                );

                return $accessorValue === $value;
            }
        );
    }

    /**
     * Internal mechanic of set method.
     *
     * @param string|null $key
     * @param mixed       $value
     * @param bool        $checkPropertiesAndType
     *
     * @return bool
     */
    protected function internalSet($key, &$value, $checkPropertiesAndType = true): bool
    {
        if (
            $value instanceof self
            &&
            !$value instanceof TypeInterface
        ) {
            foreach ($value as $valueTmp) {
                parent::internalSet(
                    $key,
                    $valueTmp,
                    $checkPropertiesAndType
                );
            }

            return true;
        }

        return parent::internalSet(
            $key,
            $value,
            $checkPropertiesAndType
        );
    }

    /**
     * @param mixed $type
     *
     * @return TypeCheckArray
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

    /**
     * Extracts the value of the given property or method from the object.
     *
     * @param Arrayy $object                the object to extract the value from
     * @param string $keyOrPropertyOrMethod the property or method for which the
     *                                      value should be extracted
     *
     * @throws \InvalidArgumentException if the method or property is not defined
     *
     * @return mixed the value extracted from the specified property or method
     */
    private function extractValue(Arrayy $object, string $keyOrPropertyOrMethod)
    {
        if (isset($object[$keyOrPropertyOrMethod])) {
            $return = $object->get($keyOrPropertyOrMethod);

            if ($return instanceof Arrayy) {
                return $return->getArray();
            }

            return $return;
        }

        if (\property_exists($object, $keyOrPropertyOrMethod)) {
            return $object->{$keyOrPropertyOrMethod};
        }

        if (\method_exists($object, $keyOrPropertyOrMethod)) {
            return $object->{$keyOrPropertyOrMethod}();
        }

        throw new \InvalidArgumentException(\sprintf('array-key & property & method "%s" not defined in %s', $keyOrPropertyOrMethod, \gettype($object)));
    }
}
