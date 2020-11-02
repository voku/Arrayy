<?php

declare(strict_types=1);

namespace Arrayy\Type;

use function Arrayy\array_first;
use Arrayy\Arrayy;
use Arrayy\ArrayyIterator;
use Arrayy\Collection\Collection;

/**
 * @template TKey of array-key
 * @template T
 * @extends Collection<TKey,T>
 */
final class DetectFirstValueTypeCollection extends Collection implements TypeInterface
{
    /**
     * @var string
     */
    private $getTypeHelper;

    /**
     * @param array|Arrayy|mixed $data
     * @param string             $iteratorClass
     * @param bool               $checkPropertiesInConstructor
     *
     * @phpstan-param array<TKey,T>|Arrayy<TKey,T> $data
     * @phpstan-param class-string<\Arrayy\ArrayyIterator> $iteratorClass
     */
    public function __construct(
        $data = [],
        string $iteratorClass = ArrayyIterator::class,
        bool $checkPropertiesInConstructor = true
    ) {
        /**
         * @psalm-suppress RedundantConditionGivenDocblockType - We also allow other types here,
         * but I don't know how to tell psalm about that. :/
         */
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
     */
    private function getTypeFromFirstValue($value): string
    {
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }
}
