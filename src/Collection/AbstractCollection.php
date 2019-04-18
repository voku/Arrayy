<?php

declare(strict_types=1);

namespace Arrayy\Collection;

use Arrayy\Arrayy;

/**
 * This class provides a full implementation of `CollectionInterface`, to
 * minimize the effort required to implement this interface.
 */
abstract class AbstractCollection extends Arrayy implements CollectionInterface
{
    /**
     * The type of elements stored in this collection.
     *
     * @var string
     */
    private $collectionType;

    /**
     * Constructs a collection object of the specified type, optionally with the
     * specified data.
     *
     * @param mixed $data
     *                     <p>
     *                     The initial items to store in the collection.
     *                     </p>
     *                     <p>
     *                     Should be an array or a generator, otherwise it will try
     *                     to convert it into an array.
     *                     </p>
     */
    public function __construct($data = [])
    {
        $this->collectionType = $this->getType();
        parent::__construct($data);
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
     * @return string
     */
    abstract public function getType(): string;

    /**
     * Merge current items and items of given collections into a new one.
     *
     * @param CollectionInterface ...$collections The collections to merge.
     *
     * @throws \InvalidArgumentException if any of the given collections are not of the same type
     *
     * @return CollectionInterface
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
        if ($this->checkType($this->collectionType, $value) === false) {
            throw new \InvalidArgumentException(
                'Value must be of type ' . $this->collectionType . '; value is ' . $this->valueToString($value)
            );
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
        if ($this->checkType($this->collectionType, $value) === false) {
            throw new \InvalidArgumentException(
                'Value must be of type ' . $this->collectionType . '; value is ' . $this->valueToString($value)
            );
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
        if ($this->checkType($this->collectionType, $value) === false) {
            throw new \InvalidArgumentException(
                'Value must be of type ' . $this->collectionType . '; value is ' . $this->valueToString($value)
            );
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

        foreach ($this->array as $item) {
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
     * @return CollectionInterface
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
     * @param bool        $checkProperties
     *
     * @return bool
     */
    protected function internalSet($key, $value, $checkProperties = true): bool
    {
        if ($this->checkType($this->collectionType, $value) === false) {
            throw new \InvalidArgumentException(
                'Value must be of type ' . $this->collectionType . '; value is ' . $this->valueToString($value)
            );
        }

        return parent::internalSet($key, $value, $checkProperties);
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

        throw new \InvalidArgumentException(
            \sprintf('array-key & property & method "%s" not defined in %s', $keyOrPropertyOrMethod, \gettype($object))
        );
    }

    /**
     * Returns `true` if value is of the specified type.
     *
     * @param string $type  the type to check the value against
     * @param mixed  $value the value to check
     *
     * @return bool
     */
    private function checkType(string $type, $value): bool
    {
        switch ($type) {
            case 'array':
                return \is_array($value);
            case 'bool':
            case 'boolean':
                return \is_bool($value);
            case 'callable':
                return \is_callable($value);
            case 'float':
            case 'double':
                return \is_float($value);
            case 'int':
            case 'integer':
                return \is_int($value);
            case 'null':
                return $value === null;
            case 'numeric':
                return \is_numeric($value);
            case 'object':
                return \is_object($value);
            case 'resource':
                return \is_resource($value);
            case 'scalar':
                return \is_scalar($value);
            case 'string':
                return \is_string($value);
            case 'mixed':
                return true;
            default:
                return $value instanceof $type;
        }
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    private function valueToString($value): string
    {
        // null
        if ($value === null) {
            return 'NULL';
        }

        // bool
        if (\is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        // array
        if (\is_array($value)) {
            return 'Array';
        }

        // scalar types (integer, float, string)
        if (\is_scalar($value)) {
            return (string) $value;
        }

        // resource
        if (\is_resource($value)) {
            return \get_resource_type($value) . ' resource #' . (int) $value;
        }

        // object
        return \get_class($value) . ' Object';
    }
}
