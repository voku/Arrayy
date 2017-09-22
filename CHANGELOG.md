# Changelog

### 3.8.0 (23.09.2017)
[+]: add some pre- / append methods + tests

### 3.7.0 (11.08.2017)
* [+]: add "Arrayy::createFromObjectVars()"
* [+]: fix internal __toString() / Arrayy->implode()
* [+]: fix in_array() usage for multidimensional array

### 3.6.0 (09.05.2017)

* [+]: add flag-parameter for "Arrayy->filter()" + polyfill for old php versions (< 5.6 || HHVM) 
* [+]: add "Arrayy->countValues()"-method

### 3.5.1 (11.04.2017)

* [!]: fix "offsetGet() must be compatible with that of ArrayAccess::offsetGet()"

### 3.5.0 (10.04.2017)

* [+]: more information via "InvalidArgumentException"
* [~]: re-use the "Arrayy->customSortKeys()"-method
* [+]: add more "sort"-methods + tests

### 3.4.0 (09.04.2017)

* [+]: overwrite "ArrayObject"-methods
* [+]: dependency injection for the "Iterator" via __constructor
* [+]: fix serialize() + unserialize() -> we will process the object now, not only the array in the object
* [+]: add more tests

### 3.3.0 (08.04.2017)

* [+]: add "Arrayy->changeKeyCase()" (with UTF-8 support)

### 3.2.1 (07.04.2017)

* [+]: fix "StaticArrayy"-class -> return value from "repeat()" is always an instance of the "Arrayy"-class

### 3.2.0 (01.04.2017)

* [~]: fix php-doc (for extended classes)
* [+]: add "Array->uniqueKeepIndex()"
* [*]: fix some more php-docs

### 3.1.2

* [+]: fix "matches()" and "matchesAny()" with empty-arrays

### 3.1.1

* [!]: fix usage of "isset() / array_key_exists()" and "array()$value / array($value)"

### 3.1.0

* [!]: fix some bugs with the magic __set // __get
* [+]: fix bug from Arrayy->get()

### 3.0.0

* [!]: "Recursively return new Arrayy objects" | thx @brad-jones

### 2.2.9 (2016-12-16)

* [*]: Apply fixes from StyleCI


### 2.2.8 (2016-12-16)

* [+]: add "Arrayy->moveElement()"

### 2.2.6 (2016-12-11)
 
* [+]: add "Arrayy->containsKeys()"
* [+]: add "Arrayy->containsValues()"

### 2.2.4 (2016-11-05)

* [+]: fix for PHP 5.3

### 2.2.3 (2016-11-04)

* [+]: add Arrayy->divide()
* [+]: add Arrayy->swap()
* [+]: add Arrayy->stripEmpty()

### 2.2.2 (2016-08-12)

* [+]: use new version of "portable-utf8" (3.0)

### 2.2.0 (2016-06-20)

* [+]: add "containsCaseInsensitive()"
* [+]: add "isEqual()"
* [+]: add "isSequential()"

### 2.1.0 (2016-04-19)

* [+]: add "Arrayy->diffRecursive()"

### 2.0.1 (2016-03-21)

* [~]: use new "portable-utf8"-version

### 2.0.0 (2016-02-10)

* [!]: fixed dot-notation
* [!]: merged doublicate functions
* [!]: use "Immutable & Mutable"-methods
* [+]: use the "ArrayAccess"-Interface
* [+]: try to fix for old php-versions

### 1.2.0 (2016-02-04)

* [+]: add Arrayy->create()
* [+]: add Arrayy->flip()
* [+]: add Arrayy->reduce() | thx @formigone

### 1.1.1 (2016-01-31)

* [*]: "Fixed Countable interface description" | thx @dvdmarchetti

### 1.1.0 (2016-01-31)

* [!]: fixed Arrayy->mergePrependKeepIndex()
* [!]: fixed Arrayy->mergeAppendKeepIndex()

### 1.0.5 (2016-01-30)

* [+]: add Arrayy->getColumn()
* [+]: use the "array_column()"-polyfill

### 1.0.4 (2016-01-30)

* [+]: add Arrayy->randomWeighted()
* [+]: add Arrayy->split()
 
### 1.0.3 (2016-01-29)

* [!]: replace "self" with "static"

### 1.0.2 (2016-01-27)

* [+]: add Arrayy->isMultiArray()
* [*]: added some more documentation

### 1.0.1 (2016-01-27)

[+]: added some more doc's 
[+]: fixed "Arrayy->random()"

### 1.0.0 (2016-01-26)

[+]: return a "Arrayy"-object
[+]: fixed "replaceValue()" 
[~]: rename "replaceValue()" -> into "replaceOneValue()"
[+]: init