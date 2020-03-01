<?php

declare(strict_types=1);

namespace Arrayy\TypeCheck;

abstract class AbstractTypeCheck implements TypeCheckInterface
{
    /**
     * @var bool
     */
    protected $isNullable = false;

    /**
     * @var string[]
     */
    protected $types = [];

    /**
     * @var array<string, string>
     */
    private static $typeMapping = [
        'int'   => 'integer',
        'bool'  => 'boolean',
        'float' => 'double',
    ];

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function checkType(&$value): bool
    {
        if ($this->isNullable && $value === null) {
            return true;
        }

        foreach ($this->types as $currentType) {
            $isValidType = $this->assertTypeEquals($currentType, $value);

            if ($isValidType) {
                return true;
            }
        }

        $type = \gettype($value);

        $expectedTypes = \implode('|', $this->types);

        $this->throwException($expectedTypes, $value, $type);

        return false;
    }

    /**
     * @param string $type
     * @param mixed  $value
     *
     * @return bool
     */
    protected function assertTypeEquals(string $type, &$value): bool
    {
        if (\strpos($type, '[]') !== false) {
            return $this->isValidGenericCollection($type, $value);
        }

        if ($type === 'mixed' && $value !== null) {
            return true;
        }

        return $value instanceof $type
               ||
               \gettype($value) === (self::$typeMapping[$type] ?? $type)
               ||
               (
                   $type === 'scalar'
                    &&
                    \is_scalar($value)
               )
               ||
               (
                   $type === 'callable'
                   &&
                   \is_callable($value)
               )
               ||
               (
                   $type === 'numeric'
                   &&
                   (
                       \is_float($value)
                       ||
                       \is_int($value)
                   )
               )
               ||
               (
                   $type === 'resource'
                   &&
                   \is_resource($value)
               );
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function valueToString($value): string
    {
        // null
        if ($value === null) {
            return 'NULL';
        }

        // bool
        if (\is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        // array
        if (\is_array($value)) {
            return 'Array';
        }

        // scalar types (integer, float, string)
        if (\is_scalar($value)) {
            return (string) $value;
        }

        // resource
        if (\is_resource($value)) {
            return \get_resource_type($value) . ' resource #' . (int) $value;
        }

        if (\is_object($value)) {
            return \get_class($value) . ' Object';
        }

        return '';
    }

    /**
     * @param string $type
     * @param mixed  $collection
     *
     * @return bool
     */
    private function isValidGenericCollection(string $type, &$collection): bool
    {
        if (!\is_array($collection)) {
            return false;
        }

        $valueType = \str_replace('[]', '', $type);

        foreach ($collection as $value) {
            if ($this->assertTypeEquals($valueType, $value)) {
                return true;
            }
        }

        return false;
    }
}
