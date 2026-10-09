<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

/**
 * @template T of array{name: string, plz?: string|null}
 * @extends \Arrayy\Arrayy<key-of<T>, value-of<T>, T>
 */
final class ArrayShapeCity extends \Arrayy\Arrayy
{
    /**
     * @param T $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatchInConstructor = true;
}
