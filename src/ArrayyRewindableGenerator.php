<?php

declare(strict_types=1);

namespace Arrayy;

/**
 * @template   XKey of array-key
 * @template   X
 * @implements \Iterator<XKey,X>
 *
 * @internal
 */
final class ArrayyRewindableGenerator implements \Iterator
{
    /**
     * @var callable
     */
    private $generatorFunction;

    /**
     * @var \Generator
     *
     * @psalm-var \Generator<XKey,X>
     */
    private $generator;

    /**
     * @var callable|null
     */
    private $onRewind;

    /**
     * @param callable $generatorConstructionFunction A callable that should return a Generator
     * @param callable $onRewind                      callable that gets invoked with 0 arguments after the iterator
     *                                                was rewinded
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(callable $generatorConstructionFunction, callable $onRewind = null)
    {
        $this->generatorFunction = $generatorConstructionFunction;
        $this->onRewind = $onRewind;
        $this->generateGenerator();
    }

    /**
     * Return the current element.
     *
     * @return mixed
     *
     * @see  http://php.net/manual/en/iterator.current.php
     * @see  Iterator::current
     *
     * @psalm-return X
     */
    public function current()
    {
        return $this->generator->current();
    }

    /**
     * Return the key of the current element.
     *
     * @return mixed scalar on success, or null on failure
     *
     * @see  http://php.net/manual/en/iterator.key.php
     * @see  Iterator::key
     *
     * @psalm-return XKey
     */
    public function key()
    {
        return $this->generator->key();
    }

    /**
     * Move forward to next element.
     *
     * @return void
     *
     * @see  http://php.net/manual/en/iterator.next.php
     * @see  Iterator::next
     */
    public function next()
    {
        $this->generator->next();
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void
     *
     * @see  http://php.net/manual/en/iterator.rewind.php
     * @see  Iterator::rewind
     */
    public function rewind()
    {
        $this->generateGenerator();

        if (\is_callable($this->onRewind)) {
            \call_user_func($this->onRewind);
        }
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     *
     * @see  http://php.net/manual/en/iterator.valid.php
     * @see  Iterator::rewind
     */
    public function valid(): bool
    {
        return $this->generator->valid();
    }

    /**
     * @return void
     */
    private function generateGenerator()
    {
        $this->generator = \call_user_func($this->generatorFunction);

        if (!($this->generator instanceof \Generator)) {
            throw new \InvalidArgumentException('The callable needs to return a Generator');
        }
    }
}
