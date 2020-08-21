<?php

namespace Arrayy\tests;

/**
 * @property string|null $plz
 * @property string      $name
 * @property string[]    $infos
 *
 * @extends    \Arrayy\Arrayy<array-key,mixed>
 */
class CityData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatchInConstructor = true;

    protected $checkForMissingPropertiesInConstructor = true;

    protected $checkPropertiesMismatch = true;
}
