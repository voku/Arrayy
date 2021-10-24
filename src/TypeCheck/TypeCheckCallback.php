<?php

declare(strict_types=1);

namespace Arrayy\TypeCheck;

class TypeCheckCallback implements TypeCheckInterface
{
    /**
     * @var callable
     *
     * @phpstan-var callable(mixed): bool
     */
    protected $callable;
    /**
     * @var bool
     */
    protected $isNullable = false;

    /**
     * @param callable                       $callable
     * @param bool                           $isNullable
     *
     * @phpstan-param  callable(mixed): bool $callable
     */
    public function __construct(callable $callable, bool $isNullable = false)
    {
        $this->callable = $callable;

        $this->isNullable = $isNullable;
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

        if (\call_user_func($this->callable, $value)) {
            return true;
        }

        $this->throwException('', $value, \gettype($value));

        return false;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return [];
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
        throw new \TypeError('Invalid type: callable failed, got value `' . \print_r($value, true) . "` with type {{$type}}.");
    }
}
