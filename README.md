[![Stories in Ready](https://badge.waffle.io/voku/Arrayy.png?label=ready&title=Ready)](https://waffle.io/voku/Arrayy)
[![Build Status](https://api.travis-ci.org/voku/Arrayy.svg?branch=master)](https://travis-ci.org/voku/Arrayy)
[![Coverage Status](https://coveralls.io/repos/voku/Arrayy/badge.svg?branch=master&service=github)](https://coveralls.io/github/voku/Arrayy?branch=master)
[![codecov.io](https://codecov.io/github/voku/Arrayy/coverage.svg?branch=master)](https://codecov.io/github/voku/Arrayy?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/voku/Arrayy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/voku/Arrayy/?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/b8c4c88a063545d787e2a4f1f5dfdf23)](https://www.codacy.com/app/voku/Arrayy)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1c9c7bda-18ab-46da-a9f4-f9a4db1dc45c/mini.png)](https://insight.sensiolabs.com/projects/1c9c7bda-18ab-46da-a9f4-f9a4db1dc45c)
[![Latest Stable Version](https://poser.pugx.org/voku/arrayy/v/stable)](https://packagist.org/packages/voku/arrayy) 
[![Total Downloads](https://poser.pugx.org/voku/arrayy/downloads)](https://packagist.org/packages/voku/arrayy) 
[![Latest Unstable Version](https://poser.pugx.org/voku/arrayy/v/unstable)](https://packagist.org/packages/voku/arrayy)
[![PHP 7 ready](http://php7ready.timesplinter.ch/voku/Arrayy/badge.svg)](https://travis-ci.org/voku/Arrayy)
[![License](https://poser.pugx.org/voku/arrayy/license)](https://packagist.org/packages/voku/arrayy)


A PHP array manipulation library. Compatible with PHP
5.3+, PHP 7, and HHVM.

``` php
Arrayy::create(['Array', 'Array'])->unique()->append('y')->implode() // Arrayy
```

* [Instance methods](#instance-methods)
    * ["set an array value"](#set-an-array-value)
    * ["get an array value"](#get-an-array-value)
    * ["delete an array value"](#delete-an-array-value)
    * ["check if an array value is-set"](#check-if-an-array-value-is-set)
    * ["simple loop with an Arrayy-object"](#simple-loop-with-an-arrayy-object)
    * [append](#appendmixed-value--arrayy)
    * [at](#atclosure-closure--arrayy)
    * [average](#averageint-decimals--intdouble)
    * [clean](#clean--arrayy)
    * [contains](#containsmixed-value--boolean)
    * [diff](#diffarray-array--arrayy)
    * [each](#eachclosure-closure--array)
    * [filter](#filterclosurenull-closure--arrayy)
    * [find](#findclosure-closure--mixed)
    * [first](#firstnullint-take--arrayy)
    * [flip](#flip--arrayy)
    * [getColumn](#getcolumnmixed-columnkey-mixed-indexkey--arrayy)
    * [implode](#implodestring-with--string)
    * [initial](#initialint-to--arrayy)
    * [intersection](#intersectionarray-search--arrayy)
    * [intersects](#intersectsarray-search--boolean)
    * [isAssoc](#isassoc--boolean)
    * [isMultiArray](#ismultiarray--boolean)
    * [last](#lastnullint-take--arrayy)
    * [length](#length--int)
    * [max](#max--mixed)
    * [matches](#matchesclosure-closure--boolean)
    * [matchesAny](#matchesanyclosure-closure--boolean)
    * [mergeAppendKeepIndex](#mergeappendkeepindexarray-array--arrayy)
    * [mergePrependKeepIndex](#mergeprependkeepindexarray-array--arrayy)
    * [mergeAppendNewIndex](#mergeappendnewindexarray-array--arrayy)
    * [mergePrependNewIndex](#mergeprependnewindexarray-array--arrayy)
    * [min](#min--mixed)
    * [prepend](#prependmixed-value--arrayy)
    * [random](#randomintnull-take--arrayy)
    * [randomWeighted](#randomweightedarray-array-intnull-take--arrayy)
    * [reduce](#reducecallable-predicate-array-init--arrayy)
    * [reject](#rejectclosure-closure--arrayy)
    * [removeFirst](#removefirst--arrayy)
    * [removeLast](#removelast--arrayy)
    * [removeValue](#removevaluemixed-value--arrayy)
    * [replaceKeys](#replacekeysarray-keys--arrayy)
    * [replaceOneValue](#replaceonevaluemixed-search-mixed-replacement--arrayy)
    * [replaceValues](#replacevaluesstring-search-string-replacement--arrayy)
    * [rest](#restint-from--arrayy)
    * [reverse](#reverse--arrayy)
    * [searchIndex](#searchindexmixed-value--arrayy)
    * [searchValue](#searchvaluemixed-index--arrayy)
    * [sortKeys](#sortkeysstring-direction--arrayy)
    * [split](#splitint2-numberofpieces-boolfalse-preservekeys--arrayy)
    * [shuffle](#shuffle--arrayy)
    * [toJson](#tojson--string)
    * [unique](#unique--arrayy)
* [Tests](#tests)
* [License](#license)

## Installation via "composer require"
```shell
composer require voku/arrayy
```

## Installation via composer (manually)

If you're using Composer to manage dependencies, you can include the following
in your composer.json file:

```json
"require": {
    "voku/arrayy": "~1.0"
}
```

Then, after running `composer update` or `php composer.phar update`, you can
load the class using Composer's autoloading:

```php
require 'vendor/autoload.php';
```

And in either case, I'd suggest using an alias.

```php
use Arrayy\Arrayy as A;
```

## OO and Chaining

The library offers OO method chaining, as seen below:

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

##### create(array $array)

Creates an Arrayy object ...

```php
$arrayy = A::create(array('fòô', 'bàř')); // Array['fòô', 'bàř']
```

##### createFromString(string $str)

Creates an Arrayy object ...

```php
$arrayy = A::createFromString(' foo, bar '); // Arrayy['foo', 'bar']
```

##### createFromJson(string $json)

Creates an Arrayy object, again ...

```php
$str = '{"firstName":"John", "lastName":"Doe"}';
$arrayy = A::createFromJson($str); // Arrayy['firstName' => 'John', 'lastName' => 'Doe']
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
foreach (a(['fòô' => 'bàř']) as $key => $value) {
  echo $key . ' | ' . $value; // fòô | bàř
}
```

##### append(mixed $value) : Arrayy

Returns a new arrayy object with $value appended.

```php
a(['fòô' => 'bàř'])->append('foo'); // Arrayy['fòô' => 'bàř', 0 => 'foo']
```

##### at(Closure $closure) : Arrayy

Iterate over an array and execute a callback for each loop.

```php
$result = A::create();
$closure = function ($value, $key) use ($result) {
  $result[$key] = ':' . $value . ':';
};
a(['foo', 'bar' => 'bis'])->at($closure); // Arrayy[':foo:', 'bar' => ':bis:']
```

##### average(int $decimals) : int|double

Returns the average value of an array

```php
a([-9, -8, -7, 1.32])->average(2); // -5.67
```

##### clean() : Arrayy

Clean all falsy values from an array.

```php
a([-8 => -9, 1, 2 => false])->clean(); // Arrayy[-8 => -9, 1]
```

##### contains(mixed $value) : boolean

Check if an item is in an array.

```php
a([1, true])->contains(true); // true
```

##### diff(array $array) : Arrayy

Return values that are only in the current array.

```php
a([1 => 1, 2 => 2])->diff([1 => 1]); // Arrayy[2 => 2]
```

##### diffReverse(array $array) : Arrayy

Return values that are only in the new $array.

```php
a([1 => 1])->diffReverse([1 => 1, 2 => 2]); // Arrayy[2 => 2]
```

##### each(Closure $closure) : array

Iterate over the current array and modify the array's value.

```php
$result = A::create();
$closure = function ($value) {
  return ':' . $value . ':';
};
a(['foo', 'bar' => 'bis'])->each($closure); // [':foo:', 'bar' => ':bis:']
```

##### filter(Closure|null $closure) : Arrayy

Find all items in an array that pass the truth test.

```php
$closure = function ($value) {
  return $value % 2 !== 0;
}
a([1, 2, 3, 4])->filter($closure); // Arrayy[0 => 1, 2 => 3]
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

##### first(null|int $take) : Arrayy

Get the first value(s) from the current array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->first(2); // Arrayy[0 => 'foo', 1 => 'bar']
```

##### flip() : Arrayy

Exchanges all keys with their associated values in an array.

```php
a([0 => 'foo', 1 => 'bar'])->flip(); // Arrayy['foo' => 0, 'bar' => 1]
```

##### getColumn(mixed $columnKey, mixed $indexKey) : Arrayy

Returns the values from a single column of the input array, identified by
the $columnKey, can be used to extract data-columns from multi-arrays.

```php
a([['foo' => 'bar', 'id' => 1], ['foo => 'lall', 'id' => 2]])->getColumn('foo', 'id'); // Arrayy[1 => 'bar', 2 => 'lall']
```

##### implode(string $with) : string

Implodes an array.

```php
a([0 => -9, 1, 2])->implode('|'); // '-9|1|2'
```

##### initial(int $to) : Arrayy

Get everything but the last..$to items.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->initial(2); // Arrayy[0 => 'foo']
```

##### intersection(array $search) : Arrayy

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

##### isMultiArray() : boolean

Check if the current array is a multi-array.

```php
a(['foo' => [1, 2 , 3]])->isMultiArray(); // true
```

##### last(null|int $take) : Arrayy

Get the last value(s) from the current array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->last(2); // Arrayy[0 => 'bar', 1 => 'lall']
```

##### length() : int

Count the values from the current array.

alias: count() || size()

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

##### mergeAppendKeepIndex(array $array) : Arrayy

Merge the new $array into the current array.

- keep key,value from the current array, also if the index is in the new $array

```php
$array1 = [1 => 'one', 'foo' => 'bar1'];
$array2 = ['foo' => 'bar2', 3 => 'three'];
a($array1)->mergeAppendKeepIndex($array2); // Arrayy[1 => 'one', 'foo' => 'bar2', 3 => 'three']
```

##### mergePrependKeepIndex(array $array) : Arrayy

Merge the the current array into the $array.

- use key,value from the new $array, also if the index is in the current array
   
```php
$array1 = [1 => 'one', 'foo' => 'bar1'];
$array2 = ['foo' => 'bar2', 3 => 'three'];
a($array1)->mergePrependKeepIndex($array2); // Arrayy['foo' => 'bar1', 3 => 'three', 1 => 'one']
```

##### mergeAppendNewIndex(array $array) : Arrayy

Merge the new $array into the current array.

- replace duplicate assoc-keys from the current array with the key,values from the new $array
- create new indexes

```php
$array1 = [1 => 'one', 'foo' => 'bar1'];
$array2 = ['foo' => 'bar2', 3 => 'three'];
a($array1)->mergeAppendNewIndex($array2); // Arrayy[0 => 'one', 'foo' => 'bar2', 1 => three']
```

##### mergePrependNewIndex(array $array) : Arrayy

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

##### prepend(mixed $value) : Arrayy

Returns a new arrayy object with $value prepended.

```php
a(['fòô' => 'bàř'])->prepend('foo'); // Arrayy[0 => 'foo', 'fòô' => 'bàř']
```

##### random(int|null $take) : Arrayy

Get a random string from an array.

```php
a([1, 2, 3, 4])->random(2); // e.g.: Arrayy[1, 4]
```

##### randomWeighted(array $array, int|null $take) : Arrayy

Get a random value from an array, with the ability to skew the results.
   
```php
a([0 => 3, 1 => 4])->randomWeighted([1 => 4]); // e.g.: Arrayy[4] (has a 66% chance of returning 4)
```

##### reduce(callable $predicate, array $init) : Arrayy

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

##### reject(Closure $closure) : Arrayy

Return all items that fail the truth test.

```php
$closure = function ($value) {
  return $value % 2 !== 0;
}
a([(1, 2, 3, 4])->reject($closure); // Arrayy[1 => 2, 3 => 4]
```

##### removeFirst() : Arrayy

Remove the first value from the current array.

```php
a([1 => 'bar', 'foo' => 'foo'])->removeFirst(); // Arrayy['foo' => 'foo']
```

##### removeLast() : Arrayy

Remove the last value from the current array.

```php
a([1 => 'bar', 'foo' => 'foo'])->removeLast(); // Arrayy[1 => 'bar']
```

##### removeValue(mixed $value) : Arrayy

Removes a particular value from an array (numeric or associative).

```php
a([1 => 'bar', 'foo' => 'foo'])->removeValue('foo'); // Arrayy[1 => 'bar']
```

##### replaceKeys(array $keys) : Arrayy

Replace the keys in an array with another set.

WARNING: An array of keys must matching the array's size and order!

```php
a([1 => 'bar', 'foo' => 'foo'])->replaceKeys([1 => 2, 'foo' => 'replaced']); // Arrayy[2 => 'bar', 'replaced' => 'foo']
```

##### replaceOneValue(mixed $search, mixed $replacement) : Arrayy

Replace the first matched value in an array.

```php
$testArray = ['bar', 'foo' => 'foo', 'foobar' => 'foobar'];
a($testArray)->replaceOneValue('foo', 'replaced'); // Arrayy['bar', 'foo' => 'replaced', 'foobar' => 'foobar']
```

##### replaceValues(string $search, string $replacement) : Arrayy

Replace values in the current array.

```php
$testArray = ['bar', 'foo' => 'foo', 'foobar' => 'foobar'];
a($testArray)->replaceValues('foo', 'replaced'); // Arrayy['bar', 'foo' => 'replaced', 'foobar' => 'replacedbar']
```

##### rest(int $from) : Arrayy

Get the last elements from index $from until the end of this array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->rest(2); // Arrayy[0 => 'lall']
```

##### reverse() : Arrayy

Return the array in the reverse order.

```php
a([1, 2, 3])->reverse(); // Arrayy[3, 2, 1]
```

##### searchIndex(mixed $value) : Arrayy

Search for the first index of the current array via $value.

```php
a(['fòô' => 'bàř', 'lall' => 'bàř'])->searchIndex('bàř'); // Arrayy[0 => 'fòô']
```

##### searchValue(mixed $index) : Arrayy

Search for the value of the current array via $index.

```php
a(['fòô' => 'bàř'])->searchValue('fòô'); // Arrayy[0 => 'bàř']
```

##### sortKeys(string $direction) : Arrayy

Sort the current array by key by $direction = 'asc' or $direction = 'desc'.

```php
a([1 => 2, 0 => 1])->sortKeys('asc'); // Arrayy[1, 2]
```

##### split(int(2) $numberOfPieces, bool(false) $preserveKeys) : Arrayy

Split an array in the given amount of pieces.
   
```php
a(['a' => 1, 'b' => 2])->split(2, true); // Arrayy[['a' => 1], ['b' => 2]]
```

##### shuffle() : Arrayy

Shuffle the current array.

```php
a([1 => 'bar', 'foo' => 'foo'])->shuffle(); // e.g.: Arrayy[['foo' => 'foo', 1 => 'bar']]
```

##### toJson() : string

Convert the current array to JSON.

```php
a(['bar', array('foo')])->toJson(); // '["bar",{"1":"foo"}]'
```

##### unique() : Arrayy

Return a duplicate free copy of the current array.

```php
a([1, 2, 2])->unique(); // Arrayy[1, 2]
```

## Tests

From the project directory, tests can be ran using `phpunit`

## License

Released under the MIT License - see `LICENSE.txt` for details.
