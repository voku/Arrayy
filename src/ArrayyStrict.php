<?php

declare(strict_types=1);

namespace Arrayy;

class ArrayyStrict extends Arrayy implements \Arrayy\Type\TypeInterface
{
    /**
     * @var bool
     */
    protected $checkPropertyTypes = true;

    /**
     * @var bool
     */
    protected $checkPropertiesMismatch = false;

    /**
     * @var bool
     */
    protected $checkForMissingPropertiesInConstructor = true;

    /**
     * @var bool
     */
    protected $checkPropertiesMismatchInConstructor = false;
}
