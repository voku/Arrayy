<?php

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
    public function __construct($array = [], $iteratorClass = '\\Arrayy\\ArrayyIterator')
    {
        parent::__construct($array, $iteratorClass);

        $this->changeSeparator('^');
    }
}
