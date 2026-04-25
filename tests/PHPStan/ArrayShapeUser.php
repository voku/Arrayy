<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

/**
 * @template T of array{id: int, firstName: string, lastName: string, city?: ArrayShapeCity|null}
 * @extends \Arrayy\Arrayy<key-of<T>, value-of<T>, T>
 */
final class ArrayShapeUser extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatchInConstructor = true;
}
