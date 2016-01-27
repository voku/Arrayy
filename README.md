[![Stories in Ready](https://badge.waffle.io/voku/Arrayy.png?label=ready&title=Ready)](https://waffle.io/voku/Arrayy)
[![Build Status](https://api.travis-ci.org/voku/Arrayy.svg?branch=master)](https://travis-ci.org/voku/Arrayy)
[![Coverage Status](https://coveralls.io/repos/voku/Arrayy/badge.svg?branch=master&service=github)](https://coveralls.io/github/voku/Arrayy?branch=master)
[![codecov.io](https://codecov.io/github/voku/Arrayy/coverage.svg?branch=master)](https://codecov.io/github/voku/Arrayy?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/voku/Arrayy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/voku/Arrayy/?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/b8c4c88a063545d787e2a4f1f5dfdf23)](https://www.codacy.com/app/voku/Arrayy)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1c9c7bda-18ab-46da-a9f4-f9a4db1dc45c/mini.png)](https://insight.sensiolabs.com/projects/1c9c7bda-18ab-46da-a9f4-f9a4db1dc45c)
[![Latest Stable Version](https://poser.pugx.org/voku/arrayy/v/stable)](https://packagist.org/packages/voku/arrayy) [![Total Downloads](https://poser.pugx.org/voku/arrayy/downloads)](https://packagist.org/packages/voku/arrayy) [![Latest Unstable Version](https://poser.pugx.org/voku/arrayy/v/unstable)](https://packagist.org/packages/voku/arrayy)
[![PHP 7 ready](http://php7ready.timesplinter.ch/voku/Arrayy/badge.svg)](https://travis-ci.org/voku/Arrayy)
[![License](https://poser.pugx.org/voku/arrayy/license)](https://packagist.org/packages/voku/arrayy)


A PHP array manipulation library. Compatible with PHP
5.3+, PHP 7, and HHVM.

``` php
a::create('Array', 'Array')->unique()->append('y')->implode() // Arrayy
```

* [Instance methods](#instance-methods)
    * [append](#appendmixed-value)
    * [prepend](#prependmixed-value)
    * TODO ... add more examples ... v1
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
use Arrayy\Arrayy as A;
echo a(array('fòô', 'bàř', 'bàř'))->unique()->reverse()->implode(); // 'bàř,fòô'
```

## Implemented Interfaces

`Arrayy\Arrayy` implements the `IteratorAggregate` interface, meaning that
`foreach` can be used with an instance of the class:

``` php
$arrayy = a(array('fòôbàř', 'foo'));
foreach ($arrayy as $value) {
    echo $value;
}
// 'fòôbàř'
// 'foo'
```

It implements the `Countable` interface, enabling the use of `count()` to
retrieve the number of characters in the string:

``` php
$arrayy = a(array('fòô', 'foo'));
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
a(['fòô', 'bàř'])->reverse()->implode();
```

## StaticArrayy

All methods listed under "Instance methods" are available as part of a static
wrapper.

```php
use Arrayy\StaticArrayy as A;

// Translates to Arrayy::create(array('fòô', 'bàř'))->reverse();
// Returns a Arrayy object with the array
A::reverse(array('fòô', 'bàř'));
```

## Class methods

##### create(array $array)

Creates a Arrayy object ...

```php
$arrayy = A::create(array('fòô', 'bàř'));
```

## Instance Methods

Arrayy: All examples below make use of PHP 5.6
function importing, and PHP 5.4 short array syntax. For further details,
see the documentation for the create method above, as well as the notes
on PHP 5.6 creation.

##### "set a array value"

```php
$arrayy = a(['fòô' => 'bàř']);
$arrayy['foo'] = 'bar';
var_dump($arrayy); // Arrayy['fòô' => 'bàř', 'foo' => 'bar']
```

##### "get a array value"

```php
$arrayy = a(['fòô' => 'bàř']);
var_dump($arrayy['fòô']); // 'bàř'
```

##### "delete a array value"

```php
$arrayy = a(['fòô' => 'bàř', 'lall']);
unset($arrayy['fòô']);
var_dump($arrayy); // Arrayy[0 => 'lall']
```

##### "check if a array value is-set"

```php
$arrayy = a(['fòô' => 'bàř']);
isset($arrayy['fòô']); // true
```

##### "simple loop with a arrayy-object"
 
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

##### searchValue(mixed $index) : Arrayy

Search for the value of the current array via $index.

```php
a(['fòô' => 'bàř'])->searchValue('fòô'); // Arrayy[0 => 'bàř']
```

##### searchIndex(mixed $value) : Arrayy

Search for the first index of the current array via $value.

```php
a(['fòô' => 'bàř', 'lall' => 'bàř'])->searchIndex('bàř'); // Arrayy[0 => 'fòô']
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

##### contains(mixed $value) : boolean

Check if an item is in an array.

```php
a([1, true])->contains(true); // true
```

##### average(int $decimals) : int|double

Returns the average value of an array

```php
a([-9, -8, -7, 1.32])->average(2); // -5.67
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

##### min() : mixed

Get the min value from an array.

```php
a([-9, -8, -7, 1.32])->min(); // -9
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

##### clean() : Arrayy

Clean all falsy values from an array.

```php
a([-8 => -9, 1, 2 => false])->clean(); // Arrayy[-8 => -9, 1]
```

##### random(int|null $take) : Arrayy

Get a random string from an array.

```php
a([1, 2, 3, 4])->random(2); // e.g.: Arrayy[1, 4]
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

##### first(null|int $take) : Arrayy

Get the first value(s) from the current array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->first(2); // Arrayy[0 => 'foo', 1 => 'bar']
```

##### last(null|int $take) : Arrayy

Get the last value(s) from the current array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->last(2); // Arrayy[0 => 'bar', 1 => 'lall']
```

##### initial(int $to) : Arrayy

Get everything but the last..$to items.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->initial(2); // Arrayy[0 => 'foo']
```

##### rest(int $from) : Arrayy

Get the last elements from index $from until the end of this array.

```php
a([2 => 'foo', 3 => 'bar', 4 => 'lall'])->rest(2); // Arrayy[0 => 'lall']
```

##### prepend(mixed $value)

Returns a new arrayy object with $value prepended.

```php
a(['fòô' => 'bàř'])->prepend('foo'); // Arrayy[0 => 'foo', 'fòô' => 'bàř']
```

TODO ... add more examples ... v2

## Tests

From the project directory, tests can be ran using `phpunit`

## License

Released under the MIT License - see `LICENSE.txt` for details.
