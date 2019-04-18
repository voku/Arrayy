<?php

declare(strict_types=1);

namespace Arrayy;

class ArrayyIterator extends \ArrayIterator
{
    /**
     * @var string
     */
    private $class;

    /**
     * @param array  $array
     * @param int    $flags
     * @param string $class
     */
    public function __construct(array $array = [], int $flags = 0, string $class = '')
    {
        $this->class = $class;

        parent::__construct($array, $flags);
    }

    /**
     * @return Arrayy|mixed will return a "Arrayy"-object instead of an array
     */
    public function current()
    {
        $value = parent::current();

        if (\is_array($value)) {
            return \call_user_func([$this->class, 'create'], $value);
        }

        return $value;
    }

    /**
     * @param string $offset
     *
     * @return Arrayy|mixed will return a "Arrayy"-object instead of an array
     */
    public function offsetGet($offset)
    {
        $value = parent::offsetGet($offset);

        if (\is_array($value)) {
            $value = \call_user_func([$this->class, 'create'], $value);
        }

        return $value;
    }
}
