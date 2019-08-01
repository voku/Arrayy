# Changelog

### 5.13.2 (2019-08-01)

- fix "array_first()" + "array_last()" (move from global namespace into Arrayy)


### 5.13.1 (2019-07-19)

- fix return type of "Arrayy->internalGetArray()"


### 5.13.0 (2019-07-19)

- add Arrayy->moveElementToFirstPlace()
- add Arrayy->moveElementToLastPlace()


### 5.12.1 (2019-07-03)

- fix for php >= 7.3


### 5.12.0 (2019-07-03)

- add new array key functions + most value functions 

   -> "Arrayy->firstKey()", "Arrayy->lastKey()", "Arrayy->mostUsedValue()", "Arrayy->mostUsedValues()"


### 5.11.1 (2019-06-25)

- Collection -> fix some string methods from the parent "Arrayy"-class


### 5.11.0 (2019-06-25)

- AbstractCollection -> accept collections (self) as valid source + fix phpdoc


### 5.10.0 (2019-06-21)

- add "keyExists()" / "delete()" / "pull()"


### 5.9.1 (2019-05-03)

- "first()" / "last()" -> fix -> do not change the current array, if it's not needed 


### 5.9.0 (2019-05-03)

- "group()" / "sorter()" -> fix phpdoc
- "keys()" / "values()" -> optimize generator usage
- "replace()" -> fix immutable of the input
- "sizeIs()" / "sizeIsLessThan()" / "sizeIsGreaterThan()" / "sizeIsBetween()" -> added
- "invoke()" -> improve generator usage + fix phpdoc
- "map()" -> allow to use the key, in the callable + additional parameter
- "containsCaseInsensitive()" -> optimize for generator usage


### 5.8.1 (2019-04-30)
- optimize performance from "Arrayy->unshift()"
- optimize performance from "Arrayy->push()"


### 5.8.0 (2019-04-20)
- add a simple "Collection" implementation + function alias \Arrayy\collection()
- fix errors reported by phpstan (level 7)
- improve performance (use "dot-notation" internally only if needed)
- improve "dot-notation" handling for non "Arrayy" objects


### 5.7.1 (2019-04-18)
- "AbstractCollection" -> optimize foreach usage
- "AbstractCollection" -> fix merge && where methods


### 5.7.0 (2019-04-17)
- optimize property check in the constructor
- better support for PhpDoc @property checks
- allow callable<mixed, \Generator> as input (Arrayy::createFromGeneratorFunction())
- add a abstract "Collection" implementation


### 5.6.3 (2019-01-11)
- "ramsey/array_column" is not needed anymore
- use autoloader also for the tests 


### 5.6.2 (2019-01-02)
- fix issue when requiring float types
- update phpcs fixer config


### 5.6.1 (2018-12-20)
- update "require-dev"
- optimize the "constructor"
- use the "JsonSerializable" interface
- fix fallback for "this->shuffle()"


### 5.6.0 (2018-12-20)
- use phpstan + fixes (level 5)
- use phpcs fixer


### 5.5.0 (2018-12-07)
- replace "UTF8" with "mbstring"
  -> Warning: is you need the "UTF8" class, please add it separately in you composer.json 
  -> "voku/portable-utf8": "~5.0"


### 5.4.0 (2018-12-07)

- fix "checkForMissingPropertiesInConstructor" with arrays 
  -> new parameter in the constructor
- fix internal "ArrayyIterator" handling


### 5.3.2 (2018-11-10)

- use generators for for-each loops
- add "Arrayy->getGenerator()" + tests


### 5.3.1 (2018-11-05)

- test the tests via "infection" (Mutation Code Coverage: 91%)
- optimize performance


### 5.3.0 (2018-11-03)

- add "type checking for @property"


### 5.2.0 (2018-09-08)

- add Arrayy->appendArrayValues()
- fix usage of set() with nested dot-notation


### 5.1.0 (2018-06-08)

- add Arrayy->fillWithDefaults()
- fix usage of "count()" + COUNT_RECURSIVE if needed


### 5.0.0 (2017-12-23)

- update "Portable UTF8" from v4 -> v5
  
  -> this is a breaking change without API-changes - but the requirement from 
  "Portable UTF8" has been changed (it no longer requires all polyfills from Symfony)


### 4.0.0 (2017-11-14)

- "php": ">=7.0" 
  * drop support for PHP < 7.0
  * use "strict_types"


### 3.8.0 (2017-09-23)

- add some pre- / append methods + tests


### 3.7.0 (2017-08-11)

- add "Arrayy::createFromObjectVars()"
- fix internal __toString() / Arrayy->implode()
- fix in_array() usage for multidimensional array


### 3.6.0 (2017-05-09)

- add flag-parameter for "Arrayy->filter()" + polyfill for old php versions (< 5.6 || HHVM) 
- add "Arrayy->countValues()"-method


### 3.5.1 (2017-04-11)

- fix "offsetGet() must be compatible with that of ArrayAccess::offsetGet()"


### 3.5.0 (2017-04-10)

- more information via "InvalidArgumentException"
- re-use the "Arrayy->customSortKeys()"-method
- add more "sort"-methods + tests


### 3.4.0 (2017-04-09)

- overwrite "ArrayObject"-methods
- dependency injection for the "Iterator" via __constructor
- fix serialize() + unserialize() -> we will process the object now, not only the array in the object
- add more tests


### 3.3.0 (2017-04-08)

- add "Arrayy->changeKeyCase()" (with UTF-8 support)


### 3.2.1 (2017-04-07)

- fix "StaticArrayy"-class -> return value from "repeat()" is always an instance of the "Arrayy"-class


### 3.2.0 (2017-04-01)

- fix php-doc (for extended classes)
- add "Array->uniqueKeepIndex()"
- fix some more php-docs


### 3.1.2

- fix "matches()" and "matchesAny()" with empty-arrays


### 3.1.1

- fix usage of "isset() / array_key_exists()" and "array()$value / array($value)"


### 3.1.0

- fix some bugs with the magic __set // __get
- fix bug from Arrayy->get()


### 3.0.0

- "Recursively return new Arrayy objects" | thx @brad-jones


### 2.2.9 (2016-12-16)

- Apply fixes from StyleCI


### 2.2.8 (2016-12-16)

- add "Arrayy->moveElement()"


### 2.2.6 (2016-12-11)
 
- add "Arrayy->containsKeys()"
- add "Arrayy->containsValues()"


### 2.2.4 (2016-11-05)

- fix for PHP 5.3


### 2.2.3 (2016-11-04)

- add Arrayy->divide()
- add Arrayy->swap()
- add Arrayy->stripEmpty()


### 2.2.2 (2016-08-12)

- use new version of "portable-utf8" (3.0)


### 2.2.0 (2016-06-20)

- add "containsCaseInsensitive()"
- add "isEqual()"
- add "isSequential()"


### 2.1.0 (2016-04-19)

- add "Arrayy->diffRecursive()"


### 2.0.1 (2016-03-21)

- use new "portable-utf8"-version


### 2.0.0 (2016-02-10)

- fixed dot-notation
- merged doublicate functions
- use "Immutable & Mutable"-methods
- use the "ArrayAccess"-Interface
- try to fix for old php-versions


### 1.2.0 (2016-02-04)

- add Arrayy->create()
- add Arrayy->flip()
- add Arrayy->reduce() | thx @formigone


### 1.1.1 (2016-01-31)

- "Fixed Countable interface description" | thx @dvdmarchetti


### 1.1.0 (2016-01-31)

- fixed Arrayy->mergePrependKeepIndex()
- fixed Arrayy->mergeAppendKeepIndex()


### 1.0.5 (2016-01-30)

- add Arrayy->getColumn()
- use the "array_column()"-polyfill


### 1.0.4 (2016-01-30)

- add Arrayy->randomWeighted()
- add Arrayy->split()

 
### 1.0.3 (2016-01-29)

- replace "self" with "static"


### 1.0.2 (2016-01-27)

- add Arrayy->isMultiArray()
- added some more documentation


### 1.0.1 (2016-01-27)

- added some more doc's 
- fixed "Arrayy->random()"


### 1.0.0 (2016-01-26)

- return a "Arrayy"-object
- fixed "replaceValue()" 
- rename "replaceValue()" -> into "replaceOneValue()"
- init
