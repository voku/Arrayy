<?php

declare(strict_types=1);

namespace Arrayy\Type;

use function Arrayy\array_first;
use Arrayy\Arrayy;
use Arrayy\ArrayyIterator;
use Arrayy\Collection\Collection;
use Arrayy\TypeCheck\TypeCheckArray;
use Arrayy\TypeCheck\TypeCheckInterface;

final class DetectTypeCollection extends Collection implements TypeInterface
{
    /**
     * @var string
     */
    private $getTypeHelper;

    public function __construct(
        $data = [],
        string $iteratorClass = ArrayyIterator::class,
        bool $checkPropertiesInConstructor = true
    ) {
        if ($data instanceof Arrayy) {
            $firstValue = $data->first();
        } elseif (\is_array($data)) {
            $firstValue = array_first($data);
        } else {
            $firstValue = $data;
        }

        $this->getTypeHelper = $this->getTypeFromFirstValue($firstValue);

        parent::__construct(
            $data,
            $iteratorClass,
            $checkPropertiesInConstructor
        );
    }

    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string|string[]|TypeCheckArray|TypeCheckInterface[]
     */
    public function getType()
    {
        return $this->getTypeHelper;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    private function getTypeFromFirstValue($value): string
    {
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }
}
