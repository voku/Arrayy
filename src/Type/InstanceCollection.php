<?php

declare(strict_types=1);

namespace Arrayy\Type;

use Arrayy\ArrayyIterator;
use Arrayy\Collection\Collection;

final class InstanceCollection extends Collection implements \Arrayy\Type\TypeInterface
{
    /**
     * InstanceType constructor.
     *
     * @param array       $data
     * @param string|null $iteratorClass
     * @param bool|null   $checkPropertiesInConstructor
     * @param string      $className
     */
    public function __construct(
        $data = [],
        string $iteratorClass = null,
        bool $checkPropertiesInConstructor = null,
        $className = ''
    ) {
        // fallback
        if ($iteratorClass === null) {
            $iteratorClass = ArrayyIterator::class;
        }
        if ($checkPropertiesInConstructor === null) {
            $checkPropertiesInConstructor = true;
        }

        parent::__construct(
            $data,
            $iteratorClass,
            $checkPropertiesInConstructor,
            self::convertIntoTypeCheckArray($className)
        );
    }
}
