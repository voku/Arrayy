<?php

declare(strict_types=1);

namespace Arrayy\Type;

use function Arrayy\array_first;
use Arrayy\Arrayy;
use Arrayy\ArrayyIterator;
use Arrayy\Collection\Collection;

/**
 * @template T
 * @extends Collection<T>
 */
final class DetectFirstValueTypeCollection extends Collection implements TypeInterface
{
    /**
     * @var string
     */
    private $getTypeHelper;

    /**
     * @param array|Arrayy $data
     * @param string       $iteratorClass
     * @param bool         $checkPropertiesInConstructor
     *
     * @psalm-param array<T>|Arrayy $data
     * @psalm-param class-string<\ArrayIterator> $iteratorClass
     */
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
            $data = [$data];
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
     * @return string
     */
    public function getType()
    {
        return $this->getTypeHelper;
    }

    /**
     * @param mixed $value
     *
     * @return string
     *
     * @psalm-param T $value
     */
    private function getTypeFromFirstValue($value): string
    {
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }
}
