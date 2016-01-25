[![Stories in Ready](https://badge.waffle.io/voku/Arrayy.png?label=ready&title=Ready)](https://waffle.io/voku/Arrayy)
[![Build Status](https://api.travis-ci.org/voku/Arrayy.svg?branch=master)](https://travis-ci.org/voku/Arrayy)
[![Coverage Status](https://coveralls.io/repos/voku/Arrayy/badge.svg?branch=master&service=github)](https://coveralls.io/github/voku/Arrayy?branch=master)
[![codecov.io](https://codecov.io/github/voku/Arrayy/coverage.svg?branch=master)](https://codecov.io/github/voku/Arrayy?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/voku/Arrayy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/voku/Arrayy/?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/TODO)](https://www.codacy.com/app/voku/Arrayy)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/TODO/mini.png)](https://insight.sensiolabs.com/projects/TODO)
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
* [Extensions](#extensions)
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
echo A::create(array('fòô', 'bàř', 'bàř'))->unique()->reverse()->implode(); // 'bàř,fòô'
```

## Implemented Interfaces

`Arrayy\Arrayy` implements the `IteratorAggregate` interface, meaning that
`foreach` can be used with an instance of the class:

``` php
$arrayy = A::create(array('fòôbàř', 'foo'));
foreach ($arrayy as $value) {
    echo $value;
}
// 'fòôbàř'
// 'foo'
```

It implements the `Countable` interface, enabling the use of `count()` to
retrieve the number of characters in the string:

``` php
$arrayy = A::create(array('fòô', 'foo'));
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

##### append(mixed $value)

Returns a new arrayy object with $value appended.

```php
a(['fòô' => 'bàř'])->append('foo'); // a(['fòô' => 'bàř', 0 => 'foo'])
```

##### prepend(mixed $value)

Returns a new arrayy object with $value prepended.

```php
a(['fòô' => 'bàř'])->prepend('foo'); // a([0 => 'foo', 'fòô' => 'bàř'])
```

TODO ... add more examples ... v2

## Tests

From the project directory, tests can be ran using `phpunit`

## License

Released under the MIT License - see `LICENSE.txt` for details.
