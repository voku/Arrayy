<?php

namespace Arrayy\tests;

use Arrayy\ArrayyIterator;

/**
 * @extends    \Arrayy\Arrayy<array-key,mixed>
 */
class ModelA extends \Arrayy\Arrayy implements ModelInterface
{
    /**
     * ModelA constructor.
     *
     * @param array  $array
     * @param string $iteratorClass
     *
     * @phpstan-param class-string<\Arrayy\ArrayyIterator> $iteratorClass
     */
    public function __construct($array = [], $iteratorClass = ArrayyIterator::class)
    {
        parent::__construct($array, $iteratorClass);

        $this->changeSeparator('^');
    }

    public function getFoo(): string
    {
        return 'foo_A';
    }
}
