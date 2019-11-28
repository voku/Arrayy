<?php

declare(strict_types=1);

namespace Arrayy\TypeCheck;

final class TypeCheckSimple extends AbstractTypeCheck implements TypeCheckInterface
{
    /**
     * @param string|string[] $type
     * @param bool            $isNullable
     */
    public function __construct($type, bool $isNullable = false)
    {
        if (\is_array($type)) {
            foreach ($type as $typeTmp) {
                $this->types[] = $typeTmp;
            }
        } else {
            $this->types[] = $type;
        }

        $this->isNullable = $isNullable;
    }

    /**
     * @param string $expectedTypes
     * @param mixed  $value
     * @param string $type
     *
     * @return \TypeError
     */
    public function throwException($expectedTypes, $value, $type): \Throwable
    {
        throw new \TypeError("Invalid type: expected to be of type {{$expectedTypes}}, instead got value `" . \print_r($value, true) . "` with type {{$type}}.");
    }
}
