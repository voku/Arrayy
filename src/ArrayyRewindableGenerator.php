<?php

declare(strict_types=1);

namespace Arrayy;

/**
 * @template   XKey as array-key
 * @template   X
 * @extends  \ArrayIterator<XKey,X>
 *
 * @internal
 */
class ArrayyRewindableGenerator extends \ArrayIterator
{
    /**
     * @var string
     *
     * @phpstan-var string|class-string<\Arrayy\Arrayy<XKey,X>>
     */
    protected $class;

    /**
     * @var callable
     */
    protected $generatorFunction;

    /**
     * @var \Generator
     *
     * @phpstan-var \Generator<XKey,X>
     */
    protected $generator;

    /**
     * @var callable|null
     */
    protected $onRewind;

    /**
     * @param callable      $generatorConstructionFunction
     *                                                     <p>A callable that should return a Generator.</p>
     * @param callable|null $onRewind
     *                                                     <p>Callable that gets invoked with 0 arguments after the iterator
     *                                                     was rewinded.</p>
     * @param string        $class
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        callable $generatorConstructionFunction,
        callable $onRewind = null,
        string $class = ''
    ) {
        $this->class = $class;
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
     * @phpstan-return X
     */
    #[\ReturnTypeWillChange]
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
     * @phpstan-return XKey
     */
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
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
