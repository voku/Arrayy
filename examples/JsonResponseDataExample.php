<?php

namespace Arrayy\tests;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @property string                                $type
 * @property \Arrayy\tests\JsonApiResponseLocation $location
 */
class JsonApiResponse extends \Arrayy\ArrayyStrict
{
    public $checkPropertiesMismatchInConstructor = true;
}

/**
 * @property \Arrayy\tests\JsonApiResponsePrimary $primary
 */
class JsonApiResponseLocation extends \Arrayy\ArrayyStrict
{
    public $checkPropertiesMismatchInConstructor = true;
}

/**
 * @property string $city
 * @property string $state
 */
class JsonApiResponsePrimary extends \Arrayy\ArrayyStrict
{
    public $checkPropertiesMismatchInConstructor = true;
}

$json = '
{
    "type": "person",
    "location": {
        "primary": { 
            "city":"bakersfield",
            "state":"ca"
        }
    }
}';
$jsonData = JsonApiResponse::createFromJsonMapper($json);

echo $jsonData->location->primary->city; // bakersfield
