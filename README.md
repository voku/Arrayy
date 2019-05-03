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
Arrayy::create(['Array', 'Array'])->unique()->append('y')->implode() // Arrayy
```
[documentation via gitbooks.io](https://voku.gitbooks.io/arrayy/content/)

* [Installation](#installation-via-composer-require)
* [Multidimensional ArrayAccess](#multidimensional-arrayaccess)
* [PhpDoc @property checking](#phpdoc-property-checking)
* [OO and Chaining](#oo-and-chaining)
* [Collections](#collections)
* [Class methods](#class-methods)
    * [use a "default object"](#use-a-default-object)
    * [create](#createarray-array--arrayy-immutable)
    * [createByReference](#createbyreferencearray-array--arrayy-mutable)
    * [createFromJson](#createfromjsonstring-json--arrayy-immutable)
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
    * [append](#appendmixed-value--arrayy-mutable)
    * [prepend](#prependmixed-value--arrayy-mutable)
    * [at](#atclosure-closure--arrayy-immutable)
    * [average](#averageint-decimals--intdouble)
    * [chunk](#chunkint-size-bool-preservekeys--arrayy-immutable)
    * [clean](#clean--arrayy-immutable)
    * [clear](#clear--arrayy-mutable)
    * [customSortKeys](#customsortkeysfunction--arrayy-mutable)
    * [customSortValues](#customsortvaluesfunction--arrayy-mutable)
    * [contains](#containsmixed-value--boolean)
    * [containsKey](#containskeymixed-key--boolean)
    * [diff](#diffarray-array--arrayy-immutable)
    * [diffReverse](#diffreversearray-array--arrayy-immutable)
    * [diffRecursive](##diffrecursivearray-array-nullarray-helpervariableforrecursion--arrayy-immutable)
    * [each](#eachclosure-closure--arrayy-immutable)
    * [exists](#existsclosure-closure--boolean)
    * [filter](#filterclosurenull-closure--arrayy-immutable)
    * [filterBy](#filterby--arrayy-immutable)
    * [find](#findclosure-closure--mixed)
    * [first](#first--mixed)
    * [firstsMutable](#firstsmutablenullint-take--arrayy-mutable)
    * [firstsImmutable](#firstsimmutablenullint-take--arrayy-immutable)
    * [flip](#flip--arrayy-immutable)
    * [getColumn](#getcolumnmixed-columnkey-mixed-indexkey--arrayy)
    * [getIterator](#getiterator)
    * [implode](#implodestring-with--string)
    * [initial](#initialint-to--arrayy-immutable)
    * [intersection](#intersectionarray-search--arrayy-immutable)
    * [intersects](#intersectsarray-search--boolean)
    * [isAssoc](#isassoc--boolean)
    * [isMultiArray](#ismultiarray--boolean)
    * [keys](#keys--arrayy-immutable)
    * [last](#last--mixed)
    * [lastsImmutable](#lastsimmutablenullint-take--arrayy-immutable)
    * [lastsMutable](#lastsmutablenullint-take--arrayy-mutable)
    * [length](#length--int)
    * [max](#max--mixed)
    * [matches](#matchesclosure-closure--boolean)
    * [matchesAny](#matchesanyclosure-closure--boolean)
    * [mergeAppendKeepIndex](#mergeappendkeepindexarray-array--arrayy-immutable)
    * [mergePrependKeepIndex](#mergeprependkeepindexarray-array--arrayy-immutable)
    * [mergeAppendNewIndex](#mergeappendnewindexarray-array--arrayy-immutable)
    * [mergePrependNewIndex](#mergeprependnewindexarray-array--arrayy-immutable)
    * [min](#min--mixed)
    * [randomKey](#randomkey--mixed)
    * [randomKeys](#randomkeys--arrayy-immutable)
    * [randomValue](#randomvalue--mixed)
    * [randomValues](#randomvalues--arrayy-immutable)
    * [randomImmutable](#randomimmutableintnull-take--arrayy-immutable)
    * [randomMutable](#randommutableintnull-take--arrayy-mutable)
    * [randomWeighted](#randomweightedarray-array-intnull-take--arrayy-immutable)
    * [reduce](#reducecallable-predicate-array-init--arrayy-immutable)
    * [reindex](#reindex--arrayy-mutable)
    * [reject](#rejectclosure-closure--arrayy-immutable)
    * [remove](#removemixed-key--immutable)
    * [removeFirst](#removefirst--arrayy-immutable)
    * [removeLast](#removelast--arrayy-immutable)
    * [removeValue](#removevaluemixed-value--arrayy-immutable)
    * [replace](#replacearray-keys--arrayy-immutable)
    * [replaceAllKeys](#replaceallkeysarray-keys--arrayy-immutable)
    * [replaceAllValues](#replaceallvaluesarray-array--arrayy-immutable)
    * [replaceKeys](#replacekeysarray-keys--arrayy-immutable)
    * [replaceOneValue](#replaceonevaluemixed-search-mixed-replacement--arrayy-immutable)
    * [replaceValues](#replacevaluesstring-search-string-replacement--arrayy-immutable)
    * [rest](#restint-from--arrayy-immutable)
    * [reverse](#reverse--arrayy-mutable)
    * [searchIndex](#searchindexmixed-value--mixed)
    * [searchValue](#searchvaluemixed-index--arrayy-immutable)
    * [sort](#sortstringintsort_asc-direction-intsort_regular-strategy-boolfalse-keepkeys--arrayy-mutable)
    * [sorter](#sorternullcallable-sorter-stringintsort_asc-direction-intsort_regular-strategy--arrayy-immutable)
    * [sortKeys](#sortkeysstringintsort_asc-direction--arrayy-mutable)
    * [sortValueNewIndex](#sortvaluenewindexstringintsort_asc-direction-intsort_regular-strategy--arrayy-immutable)
    * [sortValueKeepIndex](#sortvaluekeepindexstringintsort_asc-direction-intsort_regular-strategy--arrayy-immutable)
    * [split](#splitint2-numberofpieces-boolfalse-keepkeys--arrayy-immutable)
    * [shuffle](#shuffle--arrayy-immutable)
    * [toJson](#tojson--string)
    * [unique](#unique--arrayy-mutable)
    * [values](#values--arrayy-immutable)
    * [walk](#walk--arrayy-mutable)
* [Tests](#tests)
* [License](#license)

## Installation via "composer require"
```shell
composer require voku/arrayy
```

## Multidimensional ArrayAccess

You can access / change the array via Object, Array or with "Arrayy"-syntax.

### Access via "Arrayy"-syntax: (dot-notation)

-- **Recommended** --

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

-- **Recommended** --

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
 */
class User extends \Arrayy\Arrayy
{
  protected $checkPropertyTypes = true;

  protected $checkPropertiesMismatchInConstructor = true;
}

/**
 * Class CityData
 *
 * @property string|null $plz
 * @property string      $name
 * @property string[]    $infos
 */
class CityData extends \Arrayy\Arrayy
{
    protected $checkPropertyTypes = true;

    protected $checkPropertiesMismatchInConstructor = true;
}
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
to use a simple array or Arrayy object. For this cases you can use the ```AbstractCollection```
class.

It will throw a ```InvalidArgumentException``` if you try to add a non valid object into the collection.

e.g.: "YOURCollection.php" (see example ```/tests/CollectionTest.php``` on github)
```php
use Arrayy\Collection\AbstractCollection;

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
// $YOURcollection = \Arrayy\Collection(YOURInterface::class, [$YOURobject1]);
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

PS: you can also use "dot-notation" to get data from your collections e.g.
    ```$YOURcollection->get('3123.foo.bar');```

## StaticArrayy

All methods listed under "Instance methods" are available as part of a static
wrapper.

```php
use Arrayy\StaticArrayy as A;

// Translates to Arrayy::create(['fòô', 'bàř'])->reverse();
// Returns an Arrayy object with the array
A::reverse(['fòô', 'bàř']);
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

Create an new instance from a callable function which will return an Generator.

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

##### append(mixed $value, mixed $key) : Arrayy (Mutable)

Append a value to the current array.

alias: "Arrayy->add()"

```php
a(['fòô' => 'bàř'])->append('foo'); // Arrayy['fòô' => 'bàř', 0 => 'foo']
```

##### appendArrayValues(array $values, mixed $key) : Arrayy (Mutable)

Append a (key) + values to the current array.

```php
a(['fòô' => ['bàř']])->appendArrayValues(['foo1', 'foo2'], 'fòô'); // Arrayy['fòô' => ['bàř', 'foo1', 'foo2']]
```

##### prepend(mixed $value) : Arrayy (Mutable)

Prepend a value to the current array.

```php
a(['fòô' => 'bàř'])->prepend('foo'); // Arrayy[0 => 'foo', 'fòô' => 'bàř']
```

##### at(Closure $closure) : Arrayy (Immutable)

Iterate over the current array and execute a callback for each loop.

```php
$result = A::create();
$closure = function ($value, $key) use ($result) {
  $result[$key] = ':' . $value . ':';
};
a(['foo', 'bar' => 'bis'])->at($closure); // Arrayy[':foo:', 'bar' => ':bis:']
```

##### average(int $decimals) : int|double

Returns the average value of the current array.

```php
a([-9, -8, -7, 1.32])->average(2); // -5.67
```

##### chunk(int $size, bool $preserveKeys) : Arrayy (Immutable)

Create a chunked version of the current array.

```php
a([-9, -8, -7, 1.32])->chunk(2); // Arrayy[[-9, -8], [-7, 1.32]]
```

##### clean() : Arrayy (Immutable)

Clean all falsy values from the current array.

```php
a([-8 => -9, 1, 2 => false])->clean(); // Arrayy[-8 => -9, 1]
```

##### clear() : Arrayy (Mutable)

WARNING!!! -> Clear the current array.

```php
a([-8 => -9, 1, 2 => false])->clear(); // Arrayy[]
```

##### customSortKeys($function) : Arrayy (Mutable) 

Custom sort by index via "uksort".

```php
$callable = function ($a, $b) {
  if ($a == $b) {
    return 0;
  }
  return ($a > $b) ? 1 : -1;
};
$arrayy = a(['three' => 3, 'one' => 1, 'two' => 2]);
$resultArrayy = $arrayy->customSortKeys($callable); // Arrayy['one' => 1, 'three' => 3, 'two' => 2]
```

##### customSortValues($function) : Arrayy (Mutable) 

Custom sort by value via "usort".

```php
$callable = function ($a, $b) {
  if ($a == $b) {
    return 0;
  }
  return ($a > $b) ? 1 : -1;
};
$arrayy = a(['three' => 3, 'one' => 1, 'two' => 2]);
$resultArrayy = $arrayy->customSortValues($callable); // Arrayy['one' => 1, 'two' => 2, 'three' => 3]
```

##### contains(string|int|float $value) : boolean

Check if an item is in the current array.

alias: "Arrayy->containsValue()"

```php
a([1, true])->contains(true); // true
```

##### containsValues(array $values) : boolean

Check if all given needles are present in the array.

```php
a([1, true])->containsValues(array(1, true)); // true
```

##### containsKey(string|int|float $key) : boolean

Check if the given key/index exists in the array.

```php
a([1 => true])->containsKey(1); // true
```

##### containsKeys(array $key) : boolean

Check if all given needles are present in the array as key/index.

```php
a([1 => true])->containsKeys(array(1 => 0)); // true
```

##### containsCaseInsensitive(string $value) : boolean

Check if an (case-insensitive) string is in the current array.

```php
a(['E', 'é'])->containsCaseInsensitive('É'); // true
```

##### diff(array $array) : Arrayy (Immutable)

Return values that are only in the current array.

```php
a([1 => 1, 2 => 2])->diff([1 => 1]); // Arrayy[2 => 2]
```

##### diffReverse(array $array) : Arrayy (Immutable)

Return values that are only in the new $array.

```php
a([1 => 1])->diffReverse([1 => 1, 2 => 2]); // Arrayy[2 => 2]
```

##### diffRecursive(array $array, null|array $helperVariableForRecursion) : Arrayy (Immutable)

Return values that are only in the current multi-dimensional array.

```php
a([1 => [1 => 1], 2 => [2 => 2]])->diffRecursive([1 => [1 => 1]]); // Arrayy[2 => [2 => 2]]
```

##### divide() : Arrayy (Immutable)

Divide an array into two arrays. One with keys and the other with values.

```php
a(['a' => 1, 'b' => ''])->divide(); // Arrayy[Arrayy['a', 'b'], Arrayy[1, '']]
```

##### each(Closure $closure) : Arrayy (Immutable)

Iterate over the current array and modify the array's value.

```php
$result = A::create();
$closure = function ($value) {
  return ':' . $value . ':';
};
a(['foo', 'bar' => 'bis'])->each($closure); // Arrayy[':foo:', 'bar' => ':bis:']
```

##### exists(Closure $closure) : boolean

```php
$callable = function ($value, $key) {
  return 2 === $key and 'two' === $value;
};
a(['foo', 2 => 'two'])->exists($callable); // true
```

##### fillWithDefaults(int $num, mixed $default = null) : Arrayy (Immutable)

Fill the array until "$num" with "$default" values.

```php
a(['bar'])->fillWithDefaults(3, 'foo'); // Arrayy['bar', 'foo', 'foo']
```

##### filter(Closure|null $closure) : Arrayy (Immutable)

Find all items in an array that pass the truth test.

```php
$closure = function ($value) {
  return $value % 2 !== 0;
}
a([1, 2, 3, 4])->filter($closure); // Arrayy[0 => 1, 2 => 3]
```

##### filterBy() : Arrayy (Immutable)

Filters an array of objects (or a numeric array of associative arrays)
based on the value of a particular property within that.

```php
$array = [
  0 => ['id' => 123, 'name' => 'foo', 'group' => 'primary', 'value' => 123456, 'when' => '2014-01-01'],
  1 => ['id' => 456, 'name' => 'bar', 'group' => 'primary', 'value' => 1468, 'when' => '2014-07-15'],
];        
a($array)->filterBy('name', 'foo'); // Arrayy[0 => ['id' => 123, 'name' => 'foo', 'group' => 'primary', 'value' => 123456, 'when' => '2014-01-01']]
```

##### find(Closure $closure) : mixed

Find the first item in an array that passes the truth test, otherwise return false.

```php
$search = 'foo';
$closure = function ($value, $key) use ($search) {
  return $value === $search;
};
a(['foo', 'bar', 'lall'])->find($closure); // 'foo'
```

##### first() : mixed

Get the first value from the current array and return null if there wasn't a element.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->first(); // 'foo'
```

##### firstsMutable(null|int $take) : Arrayy (Mutable)

Get and remove the first value(s) from the current array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->firsts(2); // Arrayy[0 => 'foo', 1 => 'bar']
```

##### firstsImmutable(null|int $take) : Arrayy (Immutable)

Get the first value(s) from the current array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->firsts(2); // Arrayy[0 => 'foo', 1 => 'bar']
```

##### flip() : Arrayy (Immutable)

Exchanges all keys with their associated values in an array.

```php
a([0 => 'foo', 1 => 'bar'])->flip(); // Arrayy['foo' => 0, 'bar' => 1]
```

##### get(string $key, [null $default], [null $array]) : mixed

Get a value from an array (optional using dot-notation).

```php
$arrayy = a(['user' => ['lastname' => 'Moelleken']]);
$arrayy->get('user.lastname'); // 'Moelleken'

// ---

$arrayy = new A();
$arrayy['user'] = ['lastname' => 'Moelleken'];
$arrayy['user.firstname'] = 'Lars';

$arrayy['user']['lastname'] // Moelleken
$arrayy['user.lastname'] // Moelleken
$arrayy['user.firstname'] // Lars
```

##### getColumn(mixed $columnKey, mixed $indexKey) : Arrayy

Returns the values from a single column of the input array, identified by
the $columnKey, can be used to extract data-columns from multi-arrays.

```php
a([['foo' => 'bar', 'id' => 1], ['foo => 'lall', 'id' => 2]])->getColumn('foo', 'id'); // Arrayy[1 => 'bar', 2 => 'lall']
```

##### getIterator()

Returns a new ArrayyIterator, thus implementing the IteratorAggregate interface.

```php
a(['foo', 'bar'])->getIterator(); // ArrayyIterator['foo', 'bar']
```

##### implode(string $with) : string

Implodes an array.

```php
a([0 => -9, 1, 2])->implode('|'); // '-9|1|2'
```

##### initial(int $to) : Arrayy (Immutable)

Get everything but the last..$to items.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->initial(2); // Arrayy[0 => 'foo']
```

##### intersection(array $search) : Arrayy (Immutable)

Return an array with all elements found in input array.

```php
a(['foo', 'bar'])->intersection(['bar', 'baz']); // Arrayy['bar']
```

##### intersects(array $search) : boolean

Return a boolean flag which indicates whether the two input arrays have any common elements.

```php
a(['foo', 'bar'])->intersects(['föö', 'bär']); // false
```

##### isAssoc() : boolean

Check if we have named keys in the current array.

```php
a(['foo' => 'bar', 2, 3])->isAssoc(); // true
```

##### isEqual() : boolean

Check if we have named keys in the current array.

```php
a(['💩'])->isEqual(['💩']); // true
```

##### isMultiArray() : boolean

Check if the current array is a multi-array.

```php
a(['foo' => [1, 2 , 3]])->isMultiArray(); // true
```

##### isSequential() : boolean

Check if the current array is sequential [0, 1, 2, 3, 4, 5 ...] or not.

```php
a([0 => 'foo', 1 => 'lall', 2 => 'foobar'])->isSequential(); // true
```

##### keys() : Arrayy (Immutable)

Get all keys from the current array.

alias: "Arrayy->getKeys()"

```php
$arrayy = a([1 => 'foo', 2 => 'foo2', 3 => 'bar']);
$arrayy->keys(); // Arrayy[1, 2, 3]
```

##### last() : mixed

Get the last value from the current array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->last(); // 'lall'
```

##### lastsImmutable(null|int $take) : Arrayy (Immutable)

Get the last value(s) from the current array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->lasts(2); // Arrayy[0 => 'bar', 1 => 'lall']
```

##### lastsMutable(null|int $take) : Arrayy (Mutable)

Get the last value(s) from the current array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->lasts(2); // Arrayy[0 => 'bar', 1 => 'lall']
```

##### length() : int

Count the values from the current array.

alias: "Arrayy->count()" || "Arrayy->size()"

```php
a([-9, -8, -7, 1.32])->length(); // 4
```

##### max() : mixed

Get the max value from an array.

```php
a([-9, -8, -7, 1.32])->max(); // 1.32
```

##### matches(Closure $closure) : boolean

Check if all items in an array match a truth test.

```php
$closure = function ($value, $key) {
  return ($value % 2 === 0);
};
a([2, 4, 8])->matches($closure); // true
```

##### matchesAny(Closure $closure) : boolean

Check if any item in an array matches a truth test.

```php
$closure = function ($value, $key) {
  return ($value % 2 === 0);
};
a([1, 4, 7])->matches($closure); // true
```

##### mergeAppendKeepIndex(array $array) : Arrayy (Immutable)

Merge the new $array into the current array.

- keep key,value from the current array, also if the index is in the new $array

```php
$array1 = [1 => 'one', 'foo' => 'bar1'];
$array2 = ['foo' => 'bar2', 3 => 'three'];
a($array1)->mergeAppendKeepIndex($array2); // Arrayy[1 => 'one', 'foo' => 'bar2', 3 => 'three']
```

##### mergePrependKeepIndex(array $array) : Arrayy (Immutable)

Merge the the current array into the $array.

- use key,value from the new $array, also if the index is in the current array
   
```php
$array1 = [1 => 'one', 'foo' => 'bar1'];
$array2 = ['foo' => 'bar2', 3 => 'three'];
a($array1)->mergePrependKeepIndex($array2); // Arrayy['foo' => 'bar1', 3 => 'three', 1 => 'one']
```

##### mergeAppendNewIndex(array $array) : Arrayy (Immutable)

Merge the new $array into the current array.

- replace duplicate assoc-keys from the current array with the key,values from the new $array
- create new indexes

```php
$array1 = [1 => 'one', 'foo' => 'bar1'];
$array2 = ['foo' => 'bar2', 3 => 'three'];
a($array1)->mergeAppendNewIndex($array2); // Arrayy[0 => 'one', 'foo' => 'bar2', 1 => three']
```

##### mergePrependNewIndex(array $array) : Arrayy (Immutable)

Merge the current array into the new $array.

- replace duplicate assoc-keys from new $array with the key,values from the current array
- create new indexes

```php
$array1 = [1 => 'one', 'foo' => 'bar1'];
$array2 = ['foo' => 'bar2', 3 => 'three'];
a($array1)->mergePrependNewIndex($array2); // Arrayy['foo' => 'bar1', 0 => 'three', 1 => 'one']
```

##### min() : mixed

Get the min value from an array.

```php
a([-9, -8, -7, 1.32])->min(); // -9
```

##### moveElement(int|string $from, int|string $to) : Arrayy

Move an array element to a new index.

```php
$arr2 = new A(['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e']);
$newArr2 = $arr2->moveElement('D', 1); // Arrayy['A' => 'a', 'D' => 'd', 'B' => 'b', 'C' => 'c', 'E' => 'e']
```

##### randomKey() : mixed

Pick a random key/index from the keys of this array.

alias: "Arrayy->getRandomKey()"

```php
$arrayy = A::create([1 => 'one', 2 => 'two']);
$arrayy->randomKey(); // e.g. 2
```

##### randomKeys() : Arrayy (Immutable)

Pick a given number of random keys/indexes out of this array.

alias: "Arrayy->getRandomKeys()"

```php
$arrayy = A::create([1 => 'one', 2 => 'two']);
$arrayy->randomKeys(); // e.g. Arrayy[1, 2]
```

##### randomValue() : mixed

Pick a random value from the values of this array.

alias: "Arrayy->getRandomValue()"

```php
$arrayy = A::create([1 => 'one', 2 => 'two']);
$arrayy->randomValue(); // e.g. 'one'
```

##### randomValues() : Arrayy (Immutable)

Pick a given number of random values out of this array.

alias: "Arrayy->getRandomValues()"

```php
$arrayy = A::create([1 => 'one', 2 => 'two']);
$arrayy->randomValues(); // e.g. Arrayy['one', 'two']
```

##### randomImmutable(int|null $take) : Arrayy (Immutable)

Get a random string from an array.

alias: "Arrayy->getRandom()"

```php
a([1, 2, 3, 4])->randomImmutable(2); // e.g.: Arrayy[1, 4]
```

##### randomMutable(int|null $take) : Arrayy (Mutable)

Get a random string from an array.

```php
a([1, 2, 3, 4])->randomMutable(2); // e.g.: Arrayy[1, 4]
```

##### randomWeighted(array $array, int|null $take) : Arrayy (Immutable)

Get a random value from an array, with the ability to skew the results.
   
```php
a([0 => 3, 1 => 4])->randomWeighted([1 => 4]); // e.g.: Arrayy[4] (has a 66% chance of returning 4)
```

##### reduce(callable $predicate, array $init) : Arrayy (Immutable)

Reduce the current array via callable e.g. anonymous-function.

```php
function myReducer($resultArray, $value) {
  if ($value == 'foo') {
    $resultArray[] = $value;
  }
  return $resultArray;
};
a(['foo', 'bar'])->reduce('myReducer'); // Arrayy['foo']
```

##### reindex() : Arrayy (Mutable)

Create a numerically re-indexed Arrayy object.

```php
a([2 => 1, 3 => 2])->reindex(); // Arrayy[0 => 1, 1 => 2]
```

##### reject(Closure $closure) : Arrayy (Immutable)

Return all items that fail the truth test.

```php
$closure = function ($value) {
  return $value % 2 !== 0;
}
a([1, 2, 3, 4])->reject($closure); // Arrayy[1 => 2, 3 => 4]
```

##### remove(mixed $key) : (Immutable)

Remove a value from the current array (optional using dot-notation).

```php
a([1 => 'bar', 'foo' => 'foo'])->remove(1); // Arrayy['foo' => 'foo']
```

##### removeFirst() : Arrayy (Immutable)

Remove the first value from the current array.

```php
a([1 => 'bar', 'foo' => 'foo'])->removeFirst(); // Arrayy['foo' => 'foo']
```

##### removeLast() : Arrayy (Immutable)

Remove the last value from the current array.

```php
a([1 => 'bar', 'foo' => 'foo'])->removeLast(); // Arrayy[1 => 'bar']
```

##### removeValue(mixed $value) : Arrayy (Immutable)

Removes a particular value from an array (numeric or associative).

```php
a([1 => 'bar', 'foo' => 'foo'])->removeValue('foo'); // Arrayy[1 => 'bar']
```

##### replace(array $keys) : Arrayy (Immutable)

Replace a key with a new key/value pair.

```php
$arrayy = a([1 => 'foo', 2 => 'foo2', 3 => 'bar']);
$arrayy->replace(2, 'notfoo', 'notbar'); // Arrayy[1 => 'foo', 'notfoo' => 'notbar', 3 => 'bar']
```

##### replaceAllKeys(array $keys) : Arrayy (Immutable)

Create an array using the current array as values and the other array as keys.

```php
$firstArray = array(
    1 => 'one',
    2 => 'two',
    3 => 'three',
);
$secondArray = array(
    'one' => 1,
    1     => 'one',
    2     => 2,
);
$arrayy = a($firstArray);
$arrayy->replaceAllKeys($secondArray); // Arrayy[1 => "one", 'one' => "two", 2 => "three"]
```

##### replaceAllValues(array $array) : Arrayy (Immutable)

Create an array using the current array as keys and the other array as values.

```php
$firstArray = array(
    1 => 'one',
    2 => 'two',
    3 => 'three',
);
$secondArray = array(
    'one' => 1,
    1     => 'one',
    2     => 2,
);
$arrayy = a($firstArray);
$arrayy->replaceAllValues($secondArray); // Arrayy['one' => 1, 'two' => 'one', 'three' => 2]
```

##### replaceKeys(array $keys) : Arrayy (Immutable)

Replace the keys in an array with another set.

WARNING: An array of keys must matching the array's size and order!

```php
a([1 => 'bar', 'foo' => 'foo'])->replaceKeys([1 => 2, 'foo' => 'replaced']); // Arrayy[2 => 'bar', 'replaced' => 'foo']
```

##### replaceOneValue(mixed $search, mixed $replacement) : Arrayy (Immutable)

Replace the first matched value in an array.

```php
$testArray = ['bar', 'foo' => 'foo', 'foobar' => 'foobar'];
a($testArray)->replaceOneValue('foo', 'replaced'); // Arrayy['bar', 'foo' => 'replaced', 'foobar' => 'foobar']
```

##### replaceValues(string $search, string $replacement) : Arrayy (Immutable)

Replace values in the current array.

```php
$testArray = ['bar', 'foo' => 'foo', 'foobar' => 'foobar'];
a($testArray)->replaceValues('foo', 'replaced'); // Arrayy['bar', 'foo' => 'replaced', 'foobar' => 'replacedbar']
```

##### rest(int $from) : Arrayy (Immutable)

Get the last elements from index $from until the end of this array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->rest(2); // Arrayy[0 => 'lall']
```

##### reverse() : Arrayy (Mutable)

Return the array in the reverse order.

```php
a([1, 2, 3])->reverse(); // self[3, 2, 1]
```

##### searchIndex(mixed $value) : mixed

Search for the first index of the current array via $value.

```php
a(['fòô' => 'bàř', 'lall' => 'bàř'])->searchIndex('bàř'); // Arrayy[0 => 'fòô']
```

##### searchValue(mixed $index) : Arrayy (Immutable)

Search for the value of the current array via $index.

```php
a(['fòô' => 'bàř'])->searchValue('fòô'); // Arrayy[0 => 'bàř']
```

##### set(mixed $key, mixed $value) : Arrayy (Immutable)

Set a value for the current array (optional using dot-notation).

```php
$arrayy = a(['Lars' => ['lastname' => 'Moelleken']]);
$arrayy->set('Lars.lastname', 'Müller'); // Arrayy['Lars', ['lastname' => 'Müller']]]
```

##### setAndGet()

Get a value from a array and set it if it was not.

WARNING: this method only set the value, if the $key is not already set

```php
$arrayy = a([1 => 1, 2 => 2, 3 => 3]);
$arrayy->setAndGet(1, 4); // 1
$arrayy->setAndGet(0, 4); // 4
```

##### serialize() : string

Serialize the current arrayy-object.

```php
a([1, 4, 7])->serialize();
```

##### unserialize(string $string) : Arrayy (Mutable)
 
Unserialize an string and return this object.

```php
$serialized = a([1, 4, 7])->serialize();
a()->unserialize($serialized);
```

##### sort(string|int(SORT_ASC) $direction, int(SORT_REGULAR) $strategy, bool(false) $keepKeys) : Arrayy (Mutable)

Sort the current array and optional you can keep the keys.

- $direction e.g.: [SORT_ASC, SORT_DESC, 'ASC', 'asc', 'DESC', 'desc']
- $strategy e.g.: [SORT_REGULAR, SORT_NATURAL, ...]

```php
a(3 => 'd', 2 => 'f', 0 => 'a')->sort(SORT_ASC, SORT_NATURAL, false); // Arrayy[0 => 'a', 1 => 'd', 2 => 'f']
```

##### sorter(null|callable $sorter, string|int(SORT_ASC) $direction, int(SORT_REGULAR) $strategy) : Arrayy (Immutable)

Sort a array by value, by a closure or by a property.

- If the sorter is null (default), the array is sorted naturally.
- Associative (string) keys will be maintained, but numeric keys will be re-indexed.
- $direction e.g.: [SORT_ASC, SORT_DESC, 'ASC', 'asc', 'DESC', 'desc']
- $strategy e.g.: [SORT_REGULAR, SORT_NATURAL, ...]

```php
$testArray = range(1, 5);
$under = a($testArray)->sorter(
  function ($value) {
    return $value % 2 === 0;
  }
);
var_dump($under); // Arrayy[1, 3, 5, 2, 4]
```

##### sortKeys(string|int(SORT_ASC) $direction) : Arrayy (Mutable)

Sort the current array by key by $direction = 'asc' or $direction = 'desc'.

- $direction e.g.: [SORT_ASC, SORT_DESC, 'ASC', 'asc', 'DESC', 'desc']

```php
a([1 => 2, 0 => 1])->sortKeys('asc'); // Arrayy[0 => 1, 1 => 2]
```

##### sortValueNewIndex(string|int(SORT_ASC) $direction, int(SORT_REGULAR) $strategy) : Arrayy (Immutable)

Sort the current array by value.

- $direction e.g.: [SORT_ASC, SORT_DESC, 'ASC', 'asc', 'DESC', 'desc']
- $strategy e.g.: [SORT_REGULAR, SORT_NATURAL, ...]

```php
a(3 => 'd', 2 => 'f', 0 => 'a')->sortValueNewIndex(SORT_ASC, SORT_NATURAL); // Arrayy[0 => 'a', 1 => 'd', 2 => 'f']
```

##### sortValueKeepIndex(string|int(SORT_ASC) $direction, int(SORT_REGULAR) $strategy) : Arrayy (Immutable)

Sort the current array by value.

- $direction e.g.: [SORT_ASC, SORT_DESC, 'ASC', 'asc', 'DESC', 'desc']
- $strategy e.g.: [SORT_REGULAR, SORT_NATURAL, ...]

```php
a(3 => 'd', 2 => 'f', 0 => 'a')->sortValueNewIndex(SORT_ASC, SORT_REGULAR); // Arrayy[0 => 'a', 3 => 'd', 2 => 'f']
```

##### split(int(2) $numberOfPieces, bool(false) $keepKeys) : Arrayy (Immutable)

Split an array in the given amount of pieces.
   
```php
a(['a' => 1, 'b' => 2])->split(2, true); // Arrayy[['a' => 1], ['b' => 2]]
```

##### stripEmpty() : Arrayy (Immutable)

Stripe all empty items.

```php
a(['a' => 1, 'b' => ''])->stripEmpty(); // Arrayy[['a' => 1]]
```

##### swap(string|int $swapA, string|int $swapB) : Arrayy (Immutable)

Swap two values between positions by key.

```php
a(['a' => 1, 'b' => ''])->swap('a', 'b'); // Arrayy[['a' => '', 'b' => 1]]
```

##### shuffle() : Arrayy (Immutable)

Shuffle the current array.

```php
a([1 => 'bar', 'foo' => 'foo'])->shuffle(); // e.g.: Arrayy[['foo' => 'foo', 1 => 'bar']]
```

##### toJson() : string

Convert the current array to JSON.

```php
a(['bar', array('foo')])->toJson(); // '["bar",{"1":"foo"}]'
```

##### uniqueNewIndex() : Arrayy (Mutable)

Return a duplicate free copy of the current array.

```php
a([2 => 1, 3 => 2, 4 => 2])->uniqueNewIndex(); // Arrayy[1, 2]
```


##### uniqueKeepIndex() : Arrayy (Mutable)

Return a duplicate free copy of the current array.

```php
a([2 => 1, 3 => 2, 4 => 2])->uniqueNewIndex(); // Arrayy[2 => 1, 3 => 2]
```

##### values() : Arrayy (Immutable)

Get all values from a array.

```php
$arrayy = a([1 => 'foo', 2 => 'foo2', 3 => 'bar']);
$arrayyTmp->values(); // Arrayy[0 => 'foo', 1 => 'foo2', 2 => 'bar']
```

##### walk() : Arrayy (Mutable)

Apply the given function to every element in the array, discarding the results.

```php
$callable = function (&$value, $key) {
  $value = $key;
};
$arrayy = a([1, 2, 3]);
$arrayy->walk($callable); // Arrayy[0, 1, 2]
```

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
