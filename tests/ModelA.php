<?php

namespace Arrayy\tests;

use Arrayy\ArrayyIterator;

/**
 * Class ModelA
 */
class ModelA extends \Arrayy\Arrayy
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
