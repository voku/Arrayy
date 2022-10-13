<?php

declare(strict_types=1);

namespace Arrayy;

/**
 * @template TKey of array-key
 * @template T
 * @extends \ArrayIterator<TKey,T>
 */
class ArrayyIterator extends \ArrayIterator
{
    /**
     * @var string
     *
     * @phpstan-var string|class-string<\Arrayy\Arrayy<TKey,T>>
     */
    private $class;

    /**
     * @param array<int|string,mixed> $array
     * @param int                     $flags
     * @param string                  $class
     *
     * @phpstan-param array<TKey,T> $array
     */
    public function __construct(array $array = [], int $flags = 0, string $class = '')
    {
        $this->class = $class;

        parent::__construct($array, $flags);
    }

    /**
     * @return Arrayy|mixed will return a "Arrayy"-object instead of an array
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $value = parent::current();

        if (\is_array($value)) {
            $value = \call_user_func([$this->class, 'create'], $value, static::class, false);
        }

        return $value;
    }

    /**
     * @param string $offset
     *
     * @return Arrayy|mixed
     *                      <p>Will return a "Arrayy"-object instead of an array.</p>
     *
     * @phpstan-param TKey $offset
     * @param-return Arrayy<TKey,T>|mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $value = parent::offsetGet($offset);

        if (\is_array($value)) {
            $value = \call_user_func([$this->class, 'create'], $value, static::class, false);
        }

        return $value;
    }
}
