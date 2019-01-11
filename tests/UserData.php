<?php

namespace Arrayy\tests;

/**
 * Class UserData
 *
 * @property int                         $id
 * @property int|string                  $firstName
 * @property string                      $lastName
 * @property \Arrayy\tests\CityData|null $city
 */
class UserData extends \Arrayy\Arrayy
{
    /**
     * @var bool
     */
    protected $checkPropertyTypes = true;
}
