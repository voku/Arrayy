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
final class InstanceCollection extends Collection implements TypeInterface
{
    /**
     * @param array<object> $data
     * @param string|null   $iteratorClass
     * @param bool|null     $checkPropertiesInConstructor
     * @param string|null   $className
     *
     * @phpstan-param array<TKey,T> $data
     * @phpstan-param class-string<\Arrayy\ArrayyIterator>|null $iteratorClass
     * @phpstan-param class-string<T>|null $className
     */
    public function __construct(
        array $data = [],
        string $iteratorClass = null,
        bool $checkPropertiesInConstructor = null,
        $className = null
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
