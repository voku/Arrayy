<?php

declare(strict_types=1);

namespace Arrayy\Type;

use Arrayy\ArrayyIterator;
use Arrayy\Collection\Collection;

/**
 * @template TKey of array-key
 * @template T of object
 * @extends Collection<TKey,T>
 */
final class InstancesCollection extends Collection implements TypeInterface
{
    /**
     * @param array<object> $data
     * @param string|null   $iteratorClass
     * @param bool|null     $checkPropertiesInConstructor
     * @param string[]|null $classNames
     *
     * @phpstan-param array<TKey,T> $data
     * @phpstan-param class-string<\Arrayy\ArrayyIterator>|null $iteratorClass
     * @phpstan-param array<class-string<T>>|null $classNames
     */
    public function __construct(
        array $data = [],
        string $iteratorClass = null,
        bool $checkPropertiesInConstructor = null,
        array $classNames = null
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
            self::convertIntoTypeCheckArray($classNames)
        );
    }
}
