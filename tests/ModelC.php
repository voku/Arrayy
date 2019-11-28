<?php

namespace Arrayy\tests;

use Arrayy\ArrayyIterator;
use Arrayy\TypeCheck\TypeCheckSimple;

class ModelC extends \Arrayy\ArrayyStrict implements ModelInterface
{
    public function __construct($data = [], string $iteratorClass = ArrayyIterator::class, bool $checkPropertiesInConstructor = true)
    {
        $this->properties[self::ARRAYY_HELPER_TYPES_FOR_ALL_PROPERTIES] = new TypeCheckSimple('string');

        parent::__construct($data, $iteratorClass, $checkPropertiesInConstructor);
    }

    public function getFoo(): string
    {
        return 'foo_C';
    }
}
