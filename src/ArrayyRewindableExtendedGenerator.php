<?php

declare(strict_types=1);

namespace Arrayy;

/**
 * @template   XKey of array-key
 * @template   X
 * @extends    ArrayyRewindableGenerator<XKey,X>
 *
 * @internal
 */
class ArrayyRewindableExtendedGenerator extends ArrayyRewindableGenerator
{
    public function __construct(
        callable $generatorConstructionFunction,
        callable $onRewind = null,
        string $class = ''
    ) {
        parent::__construct(
            $generatorConstructionFunction,
            $onRewind,
            $class
        );
    }

    /**
     * Return the current element.
     *
     * @return mixed
     *
     * @see  http://php.net/manual/en/iterator.current.php
     * @see  Iterator::current
     *
     * @phpstan-return X
     */
    public function current()
    {
        $value = $this->generator->current();

        if (\is_array($value)) {
            $value = \call_user_func([$this->class, 'create'], $value, static::class, false);
        }

        return $value;
    }
}
