<?php

declare(strict_types=1);

namespace Arrayy;

/**
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
     * @see http://php.net/manual/en/iterator.current.php
     * @see  Iterator::current
     */
    public function current()
    {
        return $this->generator->current();
    }

    /**
     * Move forward to next element.
     *
     * @see  Iterator::next
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        $this->generator->next();
    }

    /**
     * Return the key of the current element.
     *
     * @return mixed scalar on success, or null on failure
     *
     * @see http://php.net/manual/en/iterator.key.php
     * @see  Iterator::key
     */
    public function key()
    {
        return $this->generator->key();
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     *
     * @see http://php.net/manual/en/iterator.valid.php
     * @see  Iterator::rewind
     */
    public function valid(): bool
    {
        return $this->generator->valid();
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @see  Iterator::rewind
     * @see http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        $this->generateGenerator();

        if (\is_callable($this->onRewind)) {
            \call_user_func($this->onRewind);
        }
    }

    private function generateGenerator()
    {
        $this->generator = \call_user_func($this->generatorFunction);

        if (!($this->generator instanceof \Generator)) {
            throw new \InvalidArgumentException('The callable needs to return a Generator');
        }
    }
}
