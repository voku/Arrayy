<?php

namespace Arrayy\tests;

use Arrayy\ArrayyIterator;
use Arrayy\TypeCheck\TypeCheckSimple;

class ModelD extends \Arrayy\ArrayyStrict implements ModelInterface
{
    /**
     * @var bool
     */
    protected $checkForMissingPropertiesInConstructor = true;

    public function __construct($data = [], string $iteratorClass = ArrayyIterator::class, bool $checkPropertiesInConstructor = true)
    {
        $this->properties['foo'] = new TypeCheckSimple('int');

        parent::__construct($data, $iteratorClass, $checkPropertiesInConstructor);
    }

    public function getFoo(): string
    {
        return 'foo_D';
    }
}
