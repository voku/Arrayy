<?php

namespace Arrayy\tests;

/**
 * @property string|null $plz
 * @property string      $name
 * @property string[]    $infos
 */
class CityData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatchInConstructor = true;

    protected $checkForMissingPropertiesInConstructor = true;
}
