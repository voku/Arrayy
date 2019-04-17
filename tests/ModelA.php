<?php

namespace Arrayy\tests;

use Arrayy\ArrayyIterator;

class ModelA extends \Arrayy\Arrayy implements ModelInterface
{
    /**
     * ModelA constructor.
     *
     * @param array  $array
     * @param string $iteratorClass
     */
    public function __construct($array = [], $iteratorClass = ArrayyIterator::class)
    {
        parent::__construct($array, $iteratorClass);

        $this->changeSeparator('^');
    }
}
