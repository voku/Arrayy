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
use Arrayy\TypeCheck\TypeCheckSimple;

/**
 * This class provides a full implementation of `CollectionInterface`, to
 * minimize the effort required to implement this interface.
 *
 * INFO: this collection thingy is inspired by https://github.com/ramsey/collection/
 *
 * @template   T
 * @extends    Arrayy<T>
 * @implements CollectionInterface<T>
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
     * @psalm-var \Arrayy\ArrayyRewindableGenerator<T>|null
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
     * @psalm-param array<T> $data
     * @psalm-param class-string<\ArrayIterator> $iteratorClass
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function where(string $keyOrPropertyOrMethod, $value): self
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
     * @param \Arrayy\Arrayy $object                <p>The object to extract the value from.</p>
     * @param string         $keyOrPropertyOrMethod <p>The property or method for which the
     *                                              value should be extracted.</p>
     *
     * @throws \InvalidArgumentException if the method or property is not defined
     *
     * @return mixed
     *               <p>The value extracted from the specified property or method.</p>
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
