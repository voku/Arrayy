<?php

/** @noinspection ClassReImplementsParentInterfaceInspection */

declare(strict_types=1);

namespace Arrayy\TypeCheck;

class TypeCheckSimple extends AbstractTypeCheck implements TypeCheckInterface
{
    /**
     * @param string|string[] $type
     * @param bool            $isNullable
     */
    public function __construct($type, bool $isNullable = false)
    {
        $this->getTypesHelper($type);

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

    /**
     * @param string|string[] $type
     *
     * @return void
     */
    protected function getTypesHelper($type)
    {
        if (\is_array($type)) {
            foreach ($type as $typeTmp) {
                $this->getTypesHelper($typeTmp);
            }

            return;
        }

        if (\strpos($type, '|') !== false) {
            $typesTmp = \explode('|', $type);

            foreach ($typesTmp as $typeTmp) {
                $this->types[] = $typeTmp;
            }
        } else {
            $this->types[] = $type;
        }
    }
}
