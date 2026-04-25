<?php

namespace Arrayy\tests;

/**
 * @extends \Arrayy\Arrayy<array-key,mixed,array<array-key,mixed>>
 */
class NativeUserData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkForMissingPropertiesInConstructor = true;

    protected int $id;

    protected int|string $firstName;

    protected string $lastName;

    protected ?NativeCityData $city;
}
