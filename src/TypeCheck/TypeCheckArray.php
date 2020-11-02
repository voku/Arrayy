<?php

namespace Arrayy\TypeCheck;

use Arrayy\Arrayy;
use Arrayy\ArrayyIterator;

/**
 * @template TKey of array-key
 * @template T
 * @extends  \Arrayy\ArrayyStrict<TKey,T>
 */
class TypeCheckArray extends \Arrayy\ArrayyStrict
{
    /**
     * Initializes
     *
     * @param mixed  $data                         <p>
     *                                             Should be an array or a generator, otherwise it will try
     *                                             to convert it into an array.
     *                                             </p>
     * @param string $iteratorClass                optional <p>
     *                                             You can overwrite the ArrayyIterator, but mostly you don't
     *                                             need this option.
     *                                             </p>
     * @param bool   $checkPropertiesInConstructor optional <p>
     *                                             You need to extend the "Arrayy"-class and you need to set
     *                                             the $checkPropertiesMismatchInConstructor class property
     *                                             to
     *                                             true, otherwise this option didn't not work anyway.
     *                                             </p>
     *
     * @phpstan-param class-string<\Arrayy\ArrayyIterator> $iteratorClass
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
