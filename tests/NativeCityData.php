<?php

namespace Arrayy\tests;

/**
 * @extends \Arrayy\Arrayy<array-key,mixed,array<array-key,mixed>>
 */
class NativeCityData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatchInConstructor = true;

    protected $checkForMissingPropertiesInConstructor = true;

    protected $checkPropertiesMismatch = true;

    protected ?string $plz;

    protected string $name;

    /**
     * @var string[]
     */
    protected array $infos;
}
