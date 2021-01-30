<?php

namespace Arrayy\Mapper;

/**
 * @category Netresearch
 *
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 *
 * @see     http://cweiske.de/
 *
 * INFO: this json-mapper is mostly a copy of https://github.com/cweiske/jsonmapper/
 *
 * @internal
 */
final class Json
{
    /**
     * Override class names that JsonMapper uses to create objects.
     * Useful when your setter methods accept abstract classes or interfaces.
     *
     * @var array
     */
    public $classMap = [];

    /**
     * Callback used when an undefined property is found.
     *
     * Works only when $bExceptionOnUndefinedProperty is disabled.
     *
     * Parameters to this function are:
     * 1. Object that is being filled
     * 2. Name of the unknown JSON property
     * 3. JSON value of the property
     *
     * @var callable
     */
    public $undefinedPropertyHandler;

    /**
     * Runtime cache for inspected classes. This is particularly effective if
     * mapArray() is called with a large number of objects
     *
     * @var array property inspection result cache
     */
    private $arInspectedClasses = [];

    /**
     * Map data all data in $json into the given $object instance.
     *
     * @param iterable      $json   JSON object structure from json_decode()
     * @param object|string $object Object to map $json data into
     *
     * @phpstan-param object|class-string $object Object to map $json data into
     *
     * @return mixed mapped object is returned
     *
     * @see    mapArray()
     */
    public function map($json, $object)
    {
        if (\is_string($object) && \class_exists($object)) {
            $object = self::createInstance($object);
        }

        if (!\is_object($object)) {
            throw new \InvalidArgumentException(
                'JsonMapper::map() requires second argument to be an object, ' . \gettype($object) . ' given.'
            );
        }

        $strClassName = \get_class($object);
        $rc = new \ReflectionClass($object);
        $strNs = $rc->getNamespaceName();
        foreach ($json as $key => $jsonValue) {
            $key = $this->getSafeName($key);

            // Store the property inspection results, so we don't have to do it
            // again for subsequent objects of the same type.
            if (!isset($this->arInspectedClasses[$strClassName][$key])) {
                $this->arInspectedClasses[$strClassName][$key] = $this->inspectProperty($rc, $key);
            }

            list(
                $hasProperty,
                $accessor,
                $type
            ) = $this->arInspectedClasses[$strClassName][$key];

            if (!$hasProperty) {
                if (\is_callable($this->undefinedPropertyHandler)) {
                    \call_user_func(
                        $this->undefinedPropertyHandler,
                        $object,
                        $key,
                        $jsonValue
                    );
                }

                continue;
            }

            if ($accessor === null) {
                continue;
            }

            if ($this->isNullable($type)) {
                if ($jsonValue === null) {
                    $this->setProperty($object, $accessor, null);

                    continue;
                }

                $type = $this->removeNullable($type);
            } elseif ($jsonValue === null) {
                throw new \InvalidArgumentException(
                    'JSON property "' . $key . '" in class "' . $strClassName . '" must not be NULL'
                );
            }

            $type = $this->getFullNamespace($type, $strNs);
            $type = $this->getMappedType($type, $jsonValue);

            if (
                $type === null
                ||
                $type === 'mixed'
            ) {
                // no given type - simply set the json data
                $this->setProperty($object, $accessor, $jsonValue);

                continue;
            }

            if ($this->isObjectOfSameType($type, $jsonValue)) {
                $this->setProperty($object, $accessor, $jsonValue);

                continue;
            }

            if ($this->isSimpleType($type)) {
                if ($type === 'string' && \is_object($jsonValue)) {
                    throw new \InvalidArgumentException(
                        'JSON property "' . $key . '" in class "' . $strClassName . '" is an object and cannot be converted to a string'
                    );
                }

                if (\strpos($type, '|') !== false) {
                    foreach (\explode('|', $type) as $tmpType) {
                        if (\gettype($jsonValue) === $tmpType) {
                            \settype($jsonValue, $tmpType);
                        }
                    }
                } else {
                    \settype($jsonValue, $type);
                }

                $this->setProperty($object, $accessor, $jsonValue);

                continue;
            }

            if ($type === '') {
                throw new \InvalidArgumentException(
                    'Empty type at property "' . $strClassName . '::$' . $key . '"'
                );
            }

            $array = null;
            $subtype = null;
            if ($this->isArrayOfType($type)) {
                $array = [];
                $subtype = \substr($type, 0, -2);
            } elseif (\substr($type, -1) == ']') {
                list($proptype, $subtype) = \explode('[', \substr($type, 0, -1));
                if ($proptype == 'array') {
                    $array = [];
                } else {
                    /** @noinspection PhpSillyAssignmentInspection - phpstan helper */
                    /** @phpstan-var class-string $proptype */
                    $proptype = $proptype;
                    $array = self::createInstance($proptype, false, $jsonValue);
                }
            } elseif (\is_a($type, \ArrayObject::class, true)) {
                /** @noinspection PhpSillyAssignmentInspection - phpstan helper */
                /** @phpstan-var \ArrayObject<mixed, mixed> $type */
                $type = $type;
                $array = self::createInstance($type, false, $jsonValue);
            }

            if ($array !== null) {
                /** @noinspection NotOptimalIfConditionsInspection */
                if (
                    !\is_array($jsonValue)
                    &&
                    $this->isScalarType(\gettype($jsonValue))
                ) {
                    throw new \InvalidArgumentException(
                        'JSON property "' . $key . '" must be an array, ' . \gettype($jsonValue) . ' given'
                    );
                }

                $cleanSubtype = $this->removeNullable($subtype);
                $subtype = $this->getFullNamespace($cleanSubtype, $strNs);
                $child = $this->mapArray($jsonValue, $array, $subtype, $key);
            } elseif ($this->isScalarType(\gettype($jsonValue))) {
                // use constructor parameter if we have a class, but only a flat type (i.e. string, int)
                /** @noinspection PhpSillyAssignmentInspection - phpstan helper */
                /** @phpstan-var object $type */
                $type = $type;
                $child = self::createInstance($type, true, $jsonValue);
            } else {
                /** @noinspection PhpSillyAssignmentInspection - phpstan helper */
                /** @phpstan-var object $type */
                $type = $type;
                $child = self::createInstance($type, false, $jsonValue);
                $this->map($jsonValue, $child);
            }

            $this->setProperty($object, $accessor, $child);
        }

        return $object;
    }

    /**
     * Map an array
     *
     * @param array       $json       JSON array structure from json_decode()
     * @param mixed       $array      Array or ArrayObject that gets filled with
     *                                data from $json
     * @param string|null $class      Class name for children objects.
     *                                All children will get mapped onto this type.
     *                                Supports class names and simple types
     *                                like "string" and nullability "string|null".
     *                                Pass "null" to not convert any values
     * @param string      $parent_key defines the key this array belongs to
     *                                in order to aid debugging
     *
     * @pslam-param null|class-string $class
     *
     * @return mixed Mapped $array is returned
     */
    public function mapArray($json, $array, $class = null, $parent_key = '')
    {
        $originalClass = $class;
        foreach ($json as $key => $jsonValue) {
            $class = $this->getMappedType($originalClass, $jsonValue);
            if ($class === null) {
                $foundArrayy = false;
                if ($array instanceof \Arrayy\Arrayy && $jsonValue instanceof \stdClass) {
                    foreach ($array->getPhpDocPropertiesFromClass() as $typesKey => $typesTmp) {
                        if (
                            $typesKey === $key
                            &&
                            \count($typesTmp->getTypes()) === 1
                            &&
                            \is_subclass_of($typesTmp->getTypes()[0], \Arrayy\Arrayy::class)
                        ) {
                            $array[$key] = $typesTmp->getTypes()[0]::createFromObjectVars($jsonValue);
                            $foundArrayy = true;

                            break;
                        }
                    }
                }
                if ($foundArrayy === false) {
                    $array[$key] = $jsonValue;
                }
            } elseif ($this->isArrayOfType($class)) {
                $array[$key] = $this->mapArray(
                    $jsonValue,
                    [],
                    \substr($class, 0, -2)
                );
            } elseif ($this->isScalarType(\gettype($jsonValue))) {
                // Use constructor parameter if we have a class, but only a flat type (i.e. string, int).
                if ($jsonValue === null) {
                    $array[$key] = null;
                } elseif ($this->isSimpleType($class)) {
                    \settype($jsonValue, $class);
                    $array[$key] = $jsonValue;
                } else {
                    /** @noinspection PhpSillyAssignmentInspection - phpstan helper */
                    /** @phpstan-var class-string $class */
                    $class = $class;
                    $array[$key] = self::createInstance(
                        $class,
                        true,
                        $jsonValue
                    );
                }
            } elseif ($this->isScalarType($class)) {
                throw new \InvalidArgumentException(
                    'JSON property "' . ($parent_key ?: '?') . '" is an array of type "' . $class . '" but contained a value of type "' . \gettype($jsonValue) . '"'
                );
            } elseif (\is_a($class, \ArrayObject::class, true)) {
                /** @noinspection PhpSillyAssignmentInspection - phpstan helper */
                /** @phpstan-var \ArrayObject<mixed, mixed> $class */
                $class = $class;
                $array[$key] = $this->mapArray(
                    $jsonValue,
                    self::createInstance($class)
                );
            } else {
                /** @noinspection PhpSillyAssignmentInspection - phpstan helper */
                /** @phpstan-var class-string $class */
                $class = $class;
                $array[$key] = $this->map(
                    $jsonValue,
                    self::createInstance($class, false, $jsonValue)
                );
            }
        }

        return $array;
    }

    /**
     * Convert a type name to a fully namespaced type name.
     *
     * @param string|null $type  Type name (simple type or class name)
     * @param string      $strNs Base namespace that gets prepended to the type name
     *
     * @return string|null Fully-qualified type name with namespace
     */
    private function getFullNamespace($type, $strNs)
    {
        if (
            $type === null
            ||
            $type === ''
            ||
            $type[0] == '\\'
            ||
            $strNs == ''
        ) {
            return $type;
        }

        list($first) = \explode('[', $type, 2);
        if (
            $first === 'mixed'
            ||
            $this->isSimpleType($first)
        ) {
            return $type;
        }

        //create a full qualified namespace
        return '\\' . $strNs . '\\' . $type;
    }

    /**
     * Try to find out if a property exists in a given class.
     * Checks property first, falls back to setter method.
     *
     * @param \ReflectionClass<object> $rc   Reflection class to check
     * @param string                   $name Property name
     *
     * @return array First value: if the property exists
     *               Second value: the accessor to use (
     *               Array-Key-String or ReflectionMethod or ReflectionProperty, or null)
     *               Third value: type of the property
     */
    private function inspectProperty(\ReflectionClass $rc, $name): array
    {
        // now try to set the property directly, we have to look it up in the class hierarchy
        $class = $rc;
        $accessor = null;

        /** @var \Arrayy\Arrayy[] $ARRAYY_CACHE */
        /** @phpstan-var array<string, \Arrayy\Arrayy<mixed, mixed>> $ARRAYY_CACHE */
        static $ARRAYY_CACHE = [];

        if (\is_subclass_of($class->name, \Arrayy\Arrayy::class)) {
            if (!isset($ARRAYY_CACHE[$class->name])) {
                $ARRAYY_CACHE[$class->name] = new $class->name();
            }

            $tmpProps = $ARRAYY_CACHE[$class->name]->getPhpDocPropertiesFromClass();
            if ($tmpProps === []) {
                return [true, $name, 'mixed'];
            }

            foreach ($tmpProps as $tmpName => $tmpProp) {
                if ($tmpName === $name) {
                    return [true, $name, \implode('|', $tmpProp->getTypes())];
                }
            }
        }

        do {
            if ($class->hasProperty($name)) {
                $accessor = $class->getProperty($name);
            }
        } while ($accessor === null && $class = $class->getParentClass());

        if ($accessor === null) {
            // case-insensitive property matching
            foreach ($rc->getProperties() as $p) {
                if ((\strcasecmp($p->name, $name) === 0)) {
                    $accessor = $p;

                    break;
                }
            }
        }

        if ($accessor !== null) {
            if ($accessor->isPublic()) {
                $docblock = $accessor->getDocComment();
                if ($docblock === false) {
                    return [true, null, null];
                }

                $annotations = self::parseAnnotations($docblock);

                if (!isset($annotations['var'][0])) {
                    return [true, $accessor, null];
                }

                // support "@var type description"
                list($type) = \explode(' ', $annotations['var'][0]);

                return [true, $accessor, $type];
            }

            // no private property
            return [true, null, null];
        }

        // no setter, no property
        return [false, null, null];
    }

    /**
     * Copied from PHPUnit 3.7.29, Util/Test.php
     *
     * @param string $docblock Full method docblock
     *
     * @return array
     */
    private static function parseAnnotations($docblock): array
    {
        // init
        $annotations = [];

        // Strip away the docblock header and footer
        // to ease parsing of one line annotations
        $docblock = \substr($docblock, 3, -2);

        $re = '/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m';
        if (\preg_match_all($re, $docblock, $matches)) {
            $numMatches = \count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                $annotations[$matches['name'][$i]][] = $matches['value'][$i];
            }
        }

        return $annotations;
    }

    /**
     * Removes - and _ and makes the next letter uppercase
     *
     * @param string $name Property name
     *
     * @return string CamelCasedVariableName
     */
    private function getCamelCaseName($name): string
    {
        return \str_replace(
            ' ',
            '',
            \ucwords(\str_replace(['_', '-'], ' ', $name))
        );
    }

    /**
     * Since hyphens cannot be used in variables we have to uppercase them.
     *
     * Technically you may use them, but they are awkward to access.
     *
     * @param string $name Property name
     *
     * @return string Name without hyphen
     */
    private function getSafeName($name): string
    {
        $convertHyphens = \strpos($name, '-') !== false;
        $convertSnake = \strpos($name, '_') !== false;

        if ($convertHyphens || $convertSnake) {
            $name = $this->getCamelCaseName($name);
        }

        return $name;
    }

    /**
     * Set a property on a given object to a given value.
     *
     * Checks if the setter or the property are public are made before
     * calling this method.
     *
     * @param \Arrayy\Arrayy|object                        $object   Object to set property on
     * @param \ReflectionMethod|\ReflectionProperty|string $accessor Array-Key-String or ReflectionMethod or ReflectionProperty
     * @param mixed                                        $value    Value of property
     *
     * @return void
     */
    private function setProperty(
        $object,
        $accessor,
        $value
    ) {
        if (\is_string($accessor) && $object instanceof \Arrayy\Arrayy) {
            $object[$accessor] = $value;
        } elseif ($accessor instanceof \ReflectionProperty) {
            $accessor->setValue($object, $value);
        } elseif ($accessor instanceof \ReflectionMethod) {
            // setter method
            $accessor->invoke($object, $value);
        }
    }

    /**
     * Get the mapped class/type name for this class.
     * Returns the incoming classname if not mapped.
     *
     * @param string|null $type      Type name to map
     * @param mixed       $jsonValue Constructor parameter (the json value)
     *
     * @return string|null The mapped type/class name
     *
     * @phpstan-return class-string|string|null
     */
    private function getMappedType($type, $jsonValue = null)
    {
        if (isset($this->classMap[$type])) {
            $target = $this->classMap[$type];
        } elseif (
            \is_string($type)
            &&
            $type !== ''
            &&
            $type[0] == '\\'
            &&
            isset($this->classMap[\substr($type, 1)])
        ) {
            $target = $this->classMap[\substr($type, 1)];
        } else {
            $target = null;
        }

        if ($target) {
            if (\is_callable($target)) {
                $type = $target($type, $jsonValue);
            } else {
                $type = $target;
            }
        }

        return $type;
    }

    /**
     * Checks if the given type is a "simple type"
     *
     * @param string $type type name from gettype()
     *
     * @return bool True if it is a simple PHP type
     *
     * @see isScalarType()
     */
    private function isSimpleType($type): bool
    {
        if (\strpos($type, '|') !== false) {
            foreach (\explode('|', $type) as $tmpType) {
                if ($this->isSimpleType($tmpType)) {
                    return true;
                }
            }
        }

        /** @noinspection InArrayCanBeUsedInspection */
        return $type == 'string'
               || $type == 'boolean' || $type == 'bool'
               || $type == 'integer' || $type == 'int' || $type == 'int'
               || $type == 'double' || $type == 'float'
               || $type == 'array' || $type == 'object';
    }

    /**
     * Checks if the object is of this type or has this type as one of its parents
     *
     * @param string $type  class name of type being required
     * @param mixed  $value Some PHP value to be tested
     *
     * @return bool True if $object has type of $type
     */
    private function isObjectOfSameType($type, $value): bool
    {
        if (\is_object($value) === false) {
            return false;
        }

        return \is_a($value, $type);
    }

    /**
     * Checks if the given type is a type that is not nested
     * (simple type except array and object)
     *
     * @param string $type type name from gettype()
     *
     * @return bool True if it is a non-nested PHP type
     *
     * @see isSimpleType()
     */
    private function isScalarType($type): bool
    {
        /** @noinspection InArrayCanBeUsedInspection */
        return $type == 'NULL'
               || $type == 'string'
               || $type == 'boolean' || $type == 'bool'
               || $type == 'integer' || $type == 'int'
               || $type == 'double' || $type == 'float';
    }

    /**
     * Returns true if type is an array of elements
     * (bracket notation)
     *
     * @param string $strType type to be matched
     *
     * @return bool
     */
    private function isArrayOfType($strType): bool
    {
        return \substr($strType, -2) === '[]';
    }

    /**
     * Checks if the given type is nullable
     *
     * @param string $type type name from the phpdoc param
     *
     * @return bool True if it is nullable
     */
    private function isNullable($type): bool
    {
        return \stripos('|' . $type . '|', '|null|') !== false;
    }

    /**
     * Remove the 'null' section of a type
     *
     * @param string|null $type type name from the phpdoc param
     *
     * @return string|null The new type value
     */
    private function removeNullable($type)
    {
        if ($type === null) {
            return null;
        }

        return \substr(
            \str_ireplace('|null|', '|', '|' . $type . '|'),
            1,
            -1
        );
    }

    /**
     * Create a new object of the given type.
     *
     * This method exists to be overwritten in child classes,
     * so you can do dependency injection or so.
     *
     * @param object|string $class        Class name to instantiate
     * @param bool          $useParameter Pass $parameter to the constructor or not
     * @param mixed         $jsonValue    Constructor parameter (the json value)
     *
     * @phpstan-param object|class-string $class
     *
     * @return object Freshly created object
     *
     * @internal
     */
    private static function createInstance(
        $class,
        $useParameter = false,
        $jsonValue = null
    ) {
        if ($useParameter) {
            return new $class($jsonValue);
        }

        $reflectClass = new \ReflectionClass($class);
        $constructor = $reflectClass->getConstructor();
        if (
            $constructor === null
            ||
            $constructor->getNumberOfRequiredParameters() > 0
        ) {
            return $reflectClass->newInstanceWithoutConstructor();
        }

        return $reflectClass->newInstance();
    }
}
