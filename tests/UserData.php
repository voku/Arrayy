<?php

require_once __DIR__ . '/CityData.php';

/**
 * Class UserData
 *
 * @property int           $id
 * @property int|string    $firstName
 * @property string        $lastName
 * @property null|CityData $city
 */
class UserData extends \Arrayy\Arrayy
{
  /**
   * @var bool
   */
  protected $checkPropertyTypes = true;
}
