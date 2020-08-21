[![Build Status](https://api.travis-ci.org/voku/Arrayy.svg?branch=master)](https://travis-ci.org/voku/Arrayy)
[![codecov.io](https://codecov.io/github/voku/Arrayy/coverage.svg?branch=master)](https://codecov.io/github/voku/Arrayy?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/b8c4c88a063545d787e2a4f1f5dfdf23)](https://www.codacy.com/app/voku/Arrayy)
[![Latest Stable Version](https://poser.pugx.org/voku/arrayy/v/stable)](https://packagist.org/packages/voku/arrayy) 
[![Total Downloads](https://poser.pugx.org/voku/arrayy/downloads)](https://packagist.org/packages/voku/arrayy) 
[![License](https://poser.pugx.org/voku/arrayy/license)](https://packagist.org/packages/voku/arrayy)
[![Donate to this project using Paypal](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.me/moelleken)
[![Donate to this project using Patreon](https://img.shields.io/badge/patreon-donate-yellow.svg)](https://www.patreon.com/voku)

# 🗃 Arrayy

A PHP array manipulation library. Compatible with PHP 7+


``` php
\Arrayy\Type\StringCollection::create(['Array', 'Array'])->unique()->append('y')->implode() // Arrayy
```
[documentation via gitbooks.io](https://voku.gitbooks.io/arrayy/content/)

* [Installation](#installation-via-composer-require)
* [Multidimensional ArrayAccess](#multidimensional-arrayaccess)
* [PhpDoc @property checking](#phpdoc-property-checking)
* [OO and Chaining](#oo-and-chaining)
* [Collections](#collections)
    * [Pre-Defined Typified Collections](#pre-defined-typified-collections])
    * [Convert JSON-Data into Collections](#convert-json-data-into-objects-collection)
* [Class methods](#class-methods)
    * [use a "default object"](#use-a-default-object)
    * [create](#createarray-array--arrayy-immutable)
    * [createByReference](#createbyreferencearray-array--arrayy-mutable)
    * [createFromJson](#createfromjsonstring-json--arrayy-immutable)
    * [createFromJsonMapper](#createfromjsonmapperstring-json--arrayy-immutable)
    * [createFromObject](#createfromobjectarrayaccess-object--arrayy-immutable)
    * [createFromObjectVars](#createfromobjectvarsobject-object--arrayy-immutable)
    * [createWithRange](#createwithrange--arrayy-immutable)
    * [createFromGeneratorImmutable](#createfromgeneratorimmutable--arrayy-immutable)
    * [createFromGeneratorFunction](#createfromgeneratorfunction--arrayy-immutable)
    * [createFromString](#createfromstringstring-str--arrayy-immutable)
* [Instance methods](#instance-methods)
    * ["set an array value"](#set-an-array-value)
    * ["set an array value via dot-notation"](#setmixed-key-mixed-value--arrayy-immutable)
    * ["get an array value"](#get-an-array-value)
    * ["get an array value via dot-notation"](#getstring-key-null-default-null-array--mixed)
    * ["get the array"](#get-the-array)
    * ["delete an array value"](#delete-an-array-value)
    * ["check if an array value is-set"](#check-if-an-array-value-is-set)
    * ["simple loop with an Arrayy-object"](#simple-loop-with-an-arrayy-object)
    * [overview](#arrayy-methods)
* [Tests](#tests)
* [License](#license)

## Installation via "composer require"
```shell
composer require voku/arrayy
```

## Multidimensional ArrayAccess

You can access / change the array via Object, Array or with "Arrayy"-syntax.

### Access via "Arrayy"-syntax: (dot-notation)

```php
$arrayy = new A(['Lars' => ['lastname' => 'Moelleken']]);

$arrayy->get('Lars'); // ['lastname' => 'Moelleken']
$arrayy->get('Lars.lastname'); // 'Moelleken'
```

### Access via "array"-syntax:

```php
$arrayy = new A(['Lars' => ['lastname' => 'Moelleken']]);

$arrayy['Lars'];             // ['lastname' => 'Moelleken']
$arrayy['Lars']['lastname']; // 'Moelleken'
```

### Access via "object"-syntax:

```php
$arrayy = new A(['Lars' => ['lastname' => 'Moelleken']]);

$arrayy->Lars; // Arrayy['lastname' => 'Moelleken']
$arrayy->Lars->lastname; // 'Moelleken'
```

### Set values via "Arrayy"-syntax: (dot-notation)

```php
$arrayy = new A(['Lars' => ['lastname' => 'Mueller']]);

$arrayy->set('Lars.lastname', 'Moelleken');
$arrayy->get('Lars.lastname'); // 'Moelleken'
```

### Set values via "array"-syntax:

```php
$arrayy = new A(['Lars' => ['lastname' => 'Moelleken']]);

$arrayy['Lars'] = array('lastname' => 'Müller');
$arrayy['Lars']['lastname]; // 'Müller'
```

### Set values via "object"-syntax:

```php
$arrayy = new A(['Lars' => ['lastname' => 'Moelleken']]);

$arrayy->Lars = array('lastname' => 'Müller');
$arrayy->Lars->lastname; // 'Müller'
```

## PhpDoc @property checking

The library offers a type checking for @property phpdoc-class-comments, as seen below:

```php
/**
 * @property int        $id
 * @property int|string $firstName
 * @property string     $lastName
 * @property null|City  $city
 *
 * @extends  \Arrayy\Arrayy<array-key,mixed>
 */
class User extends \Arrayy\Arrayy
{
  protected $checkPropertyTypes = true;

  protected $checkPropertiesMismatchInConstructor = true;
}

/**
 * @property string|null $plz
 * @property string      $name
 * @property string[]    $infos
 *
 * @extends  \Arrayy\Arrayy<array-key,mixed>
 */
class City extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatchInConstructor = true;
}

$cityMeta = City::meta();
$city = new City(
    [
        $cityMeta->plz   => null,
        $cityMeta->name  => 'Düsseldorf',
        $cityMeta->infos => ['lall'],
    ]
);

$userMeta = User::meta();
$user = new User(
    [
        $userMeta->id        => 1,
        $userMeta->firstName => 'Lars',
        $userMeta->lastName  => 'Moelleken',
        $userMeta->city      => $city,
    ]
);

var_dump($user['lastName']); // 'Moelleken'
var_dump($user[$userMeta->lastName]); // 'Moelleken'
var_dump($user->lastName); // Moelleken

var_dump($user['city.name']); // 'Düsseldorf'
var_dump($user[$userMeta->city][$cityMeta->name]); // 'Düsseldorf'
var_dump($user->city->name); // Düsseldorf
```

- "checkPropertyTypes": activate the type checking for all defined @property in the class-phpdoc
- "checkPropertiesMismatchInConstructor": activate the property mismatch check, so you can only add an 
                                          array with all needed properties (or an empty array) into the constructor

## OO and Chaining

The library also offers OO method chaining, as seen below:

```php
echo a(['fòô', 'bàř', 'bàř'])->unique()->reverse()->implode(','); // 'bàř,fòô'
```

## Implemented Interfaces

`Arrayy\Arrayy` implements the `IteratorAggregate` interface, meaning that
`foreach` can be used with an instance of the class:

``` php
$arrayy = a(['fòôbàř', 'foo']);
foreach ($arrayy as $value) {
    echo $value;
}
// 'fòôbàř'
// 'foo'
```

It implements the `Countable` interface, enabling the use of `count()` to
retrieve the number of elements in the array:

``` php
$arrayy = a(['fòô', 'foo']);
count($arrayy);  // 2
```

## PHP 5.6 Creation

As of PHP 5.6, [`use function`](https://wiki.php.net/rfc/use_function) is
available for importing functions. Arrayy exposes a namespaced function,
`Arrayy\create`, which emits the same behaviour as `Arrayy\Arrayy::create()`.
If running PHP 5.6, or another runtime that supports the `use function` syntax,
you can take advantage of an even simpler API as seen below:

``` php
use function Arrayy\create as a;

// Instead of: A::create(['fòô', 'bàř'])->reverse()->implode();
a(['fòô', 'bàř'])->reverse()->implode(','); // 'bàř,fòô'
```

## Collections

If you need to group objects together, it's not a good idea 
to use a simple array or Arrayy object. For these cases you can use the ```AbstractCollection```
class.

It will throw a ```InvalidArgumentException``` if you try to add a non valid object into the collection.

e.g.: "YOURCollection.php" (see example ```/tests/CollectionTest.php``` on github)
```php
use Arrayy\Collection\AbstractCollection;

/**
 * @extends  AbstractCollection<array-key,YOURInterface>
 */
class YOURCollection extends AbstractCollection
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     */
    public function getType(): string
    {
        return YOURInterface::class;
    }
}

$YOURobject1 = new YOURClass();
$YOURobject2 = new YOURClass();

$YOURcollection = new YOURCollection($YOURobject1);
$YOURcollection->add($YOURobject2); // add one more object

// Or, you can use an array of objects.
//
// $YOURcollection = new YOURCollection([$YOURobject1, $YOURobject2]);

// Or, if you don't want to create new classes ... 
// ... and you don't need typehints and autocompletion via classes.
//
// $YOURcollection = \Arrayy\Collection::construct(YOURInterface::class, [$YOURobject1]);
// $YOURcollection->add($YOURobject2); // add one more object

// Or, if you don't like classes at all. ;-)
//
// $YOURcollection = \Arrayy\collection(YOURInterface::class, [$YOURobject1]);
// $YOURcollection->add($YOURobject2); // add one more object

foreach ($YOURcollection as $YOURobject) {
    if ($YOURobject instanceof YOURInterface) {
        // Do something with $YOURobject
    }
}
``` 

You can also use "dot-notation" to get data from your collections e.g.
```$YOURcollection->get('3123.foo.bar');```

## Pre-Defined Typified Collections

### simple example

This will throw a "TypeError"-Exception. 

```php
use Arrayy\Type\StringCollection;

$collection = new StringCollection(['A', 'B', 'C', 1]);
```

### complex example

This will NOT throw a "TypeError"-Exception. 

```php
use Arrayy\Type\IntCollection;
use Arrayy\Type\StringCollection;
use Arrayy\Type\InstancesCollection;
use Arrayy\Type\TypeInterface;

$collection = InstancesCollection::construct(
    TypeInterface::class,
    [new StringCollection(['A', 'B', 'C']), new IntCollection([1])]
);

$collection->toArray(true); // [['A', 'B', 'C'], [1]]
```

## Convert JSON-Data into Objects (Collection)

```php

namespace Arrayy\tests\Collection;

use Arrayy\Collection\AbstractCollection;

/**
 * @extends  AbstractCollection<array-key,\Arrayy\tests\UserData>
 */
class UserDataCollection extends AbstractCollection
{
    /**
     * The type (FQCN) associated with this collection.
     *
     * @return string
     */
    public function getType()
    {
        return \Arrayy\tests\UserData::class;
    }
}

$json = '[{"id":1,"firstName":"Lars","lastName":"Moelleken","city":{"name":"Düsseldorf","plz":null,"infos":["lall"]}}, {"id":1,"firstName":"Sven","lastName":"Moelleken","city":{"name":"Köln","plz":null,"infos":["foo"]}}]';
$userDataCollection = UserDataCollection::createFromJsonMapper($json);

/** @var \Arrayy\tests\UserData[] $userDatas */
$userDataCollection->getAll();

$userData0 = $userDataCollection[0];
echo $userData0->firstName; // 'Lars'
$userData0->city; // CityData::class
echo $userData0->city->name; // 'Düsseldorf'

$userData1 = $userDataCollection[1];
echo $userData1->firstName; // 'Sven'
$userData1->city; // CityData::class
echo $userData1->city->name; // 'Köln'
```

## Class methods

##### use a "default object" 

Creates an Arrayy object.

```php
$arrayy = new Arrayy(array('fòô', 'bàř')); // Arrayy['fòô', 'bàř']
```

##### create(array $array) : Arrayy (Immutable)

Creates an Arrayy object, via static "create()"-method

```php
$arrayy = A::create(array('fòô', 'bàř')); // Arrayy['fòô', 'bàř']
```

##### createByReference(array &$array) : Arrayy (Mutable)

WARNING: Creates an Arrayy object by reference.

```php
$array = array('fòô', 'bàř');
$arrayy = A::createByReference($array); // Arrayy['fòô', 'bàř']
```

##### createFromJson(string $json) : Arrayy (Immutable)

Create an new Arrayy object via JSON.

```php
$str = '{"firstName":"John", "lastName":"Doe"}';
$arrayy = A::createFromJson($str); // Arrayy['firstName' => 'John', 'lastName' => 'Doe']
```

##### createFromJsonMapper(string $json) : Arrayy (Immutable)

Create an new Arrayy object via JSON and fill sub-objects is possible.

```php
<?php

namespace Arrayy\tests;

/**
 * @property int                         $id
 * @property int|string                  $firstName
 * @property string                      $lastName
 * @property \Arrayy\tests\CityData|null $city
 *
 * @extends  \Arrayy\Arrayy<array-key,mixed>
 */
class UserData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkForMissingPropertiesInConstructor = true;
}

/**
 * @property string|null $plz
 * @property string      $name
 * @property string[]    $infos
 *
 * @extends  \Arrayy\Arrayy<array-key,mixed>
 */
class CityData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatchInConstructor = true;

    protected $checkForMissingPropertiesInConstructor = true;

    protected $checkPropertiesMismatch = true;
}

$json = '{"id":1,"firstName":"Lars","lastName":"Moelleken","city":{"name":"Düsseldorf","plz":null,"infos":["lall"]}}';
$userData = UserData::createFromJsonMapper($json);

$userData; // => \Arrayy\tests\UserData::class
echo $userData->firstName; // 'Lars' 
$userData->city; // => \Arrayy\tests\CityData::class
echo $userData->city->name; // 'Düsseldorf'
```

##### createFromObject(ArrayAccess $object) : Arrayy (Immutable)

Create an new instance filled with values from an object that have implemented ArrayAccess.

```php
$object = A::create(1, 'foo');
$arrayy = A::createFromObject($object); // Arrayy[1, 'foo']
```

##### createFromObjectVars(\object $object) : Arrayy (Immutable)

Create an new instance filled with values from an object.

```php
$object = new stdClass();
$object->x = 42;
$arrayy = A::createFromObjectVars($object); // Arrayy['x' => 42]
```

##### createWithRange() : Arrayy (Immutable)

Create an new instance containing a range of elements.

```php
$arrayy = A::createWithRange(2, 4); // Arrayy[2, 3, 4]
```

##### createFromGeneratorImmutable() : Arrayy (Immutable)

Create an new instance filled with a copy of values from a "Generator"-object.

WARNING: Need more memory then the "A::createFromGeneratorFunction()" call, because we
         will fetch and store all keys and values from the Generator.

```php
$generator = A::createWithRange(2, 4)->getGenerator();
$arrayy = A::createFromGeneratorImmutable($generator); // Arrayy[2, 3, 4]
```

##### createFromGeneratorFunction() : Arrayy (Immutable)

Create an new instance from a callable function which will return a Generator.

```php
$generatorFunction = static function() {
    yield from A::createWithRange(2, 4)->getArray();
};
$arrayy = A::createFromGeneratorFunction($generatorFunction); // Arrayy[2, 3, 4]
```

##### createFromString(string $str) : Arrayy (Immutable)

Create an new Arrayy object via string.

```php
$arrayy = A::createFromString(' foo, bar '); // Arrayy['foo', 'bar']
```

## Instance Methods

Arrayy: All examples below make use of PHP 5.6
function importing, and PHP 5.4 short array syntax. For further details,
see the documentation for the create method above, as well as the notes
on PHP 5.6 creation.

##### "set an array value"

```php
$arrayy = a(['fòô' => 'bàř']);
$arrayy['foo'] = 'bar';
var_dump($arrayy); // Arrayy['fòô' => 'bàř', 'foo' => 'bar']
```

##### "get an array value"

```php
$arrayy = a(['fòô' => 'bàř']);
var_dump($arrayy['fòô']); // 'bàř'
```

##### "get the array"

```php
$arrayy = a(['fòô' => 'bàř']);
var_dump($arrayy->getArray()); // ['fòô' => 'bàř']
```

##### "delete an array value"

```php
$arrayy = a(['fòô' => 'bàř', 'lall']);
unset($arrayy['fòô']);
var_dump($arrayy); // Arrayy[0 => 'lall']
```

##### "check if an array value is-set"

```php
$arrayy = a(['fòô' => 'bàř']);
isset($arrayy['fòô']); // true
```

##### "simple loop with an Arrayy-object"
 
```php
$arrayy = a(['fòô' => 'bàř']);
foreach ($arrayy) as $key => $value) {
  echo $key . ' | ' . $value; // fòô | bàř
}
```

## Arrayy methods

%__functions_index__Arrayy\Arrayy__%

%__functions_list__Arrayy\Arrayy__%


## Support

For support and donations please visit [Github](https://github.com/voku/Arrayy/) | [Issues](https://github.com/voku/Arrayy/issues) | [PayPal](https://paypal.me/moelleken) | [Patreon](https://www.patreon.com/voku).

For status updates and release announcements please visit [Releases](https://github.com/voku/Arrayy/releases) | [Twitter](https://twitter.com/suckup_de) | [Patreon](https://www.patreon.com/voku/posts).

For professional support please contact [me](https://about.me/voku).

## Thanks

- Thanks to [GitHub](https://github.com) (Microsoft) for hosting the code and a good infrastructure including Issues-Managment, etc.
- Thanks to [IntelliJ](https://www.jetbrains.com) as they make the best IDEs for PHP and they gave me an open source license for PhpStorm!
- Thanks to [Travis CI](https://travis-ci.com/) for being the most awesome, easiest continous integration tool out there!
- Thanks to [StyleCI](https://styleci.io/) for the simple but powerfull code style check.
- Thanks to [PHPStan](https://github.com/phpstan/phpstan) && [Psalm](https://github.com/vimeo/psalm) for relly great Static analysis tools and for discover bugs in the code!

## Tests

From the project directory, tests can be ran using `phpunit`

## License

Released under the MIT License - see `LICENSE.txt` for details.
