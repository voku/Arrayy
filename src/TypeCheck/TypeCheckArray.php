<?php

namespace Arrayy\TypeCheck;

use Arrayy\Arrayy;
use Arrayy\ArrayyIterator;

class TypeCheckArray extends \Arrayy\ArrayyStrict
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        $data = [],
        string $iteratorClass = ArrayyIterator::class,
        bool $checkPropertiesInConstructor = true
    ) {
        $this->properties[Arrayy::ARRAYY_HELPER_TYPES_FOR_ALL_PROPERTIES] = new TypeCheckSimple(TypeCheckInterface::class);

        parent::__construct($data, $iteratorClass, $checkPropertiesInConstructor);
    }
}
