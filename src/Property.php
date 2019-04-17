<?php

namespace Arrayy;

/**
 * inspired by https://github.com/spatie/value-object
 */
final class Property extends \ReflectionProperty
{
    /**
     * @var array
     */
    protected static $typeMapping = [
        'int'   => 'integer',
        'bool'  => 'boolean',
        'float' => 'double',
    ];

    /**
     * @var bool
     */
    protected $hasTypeDeclaration = false;

    /**
     * @var bool
     */
    protected $isNullable = false;

    /**
     * @var array
     */
    protected $types = [];

    /**
     * Property constructor.
     *
     * @param \ReflectionProperty $reflectionProperty
     * @param object              $fakeObject
     *
     * @throws \ReflectionException
     */
    public function __construct($reflectionProperty, $fakeObject = null)
    {
        parent::__construct($fakeObject, $reflectionProperty->getName());
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function checkType($value): bool
    {
        if (!$this->hasTypeDeclaration) {
            return true;
        }

        if ($this->isNullable && $value === null) {
            return true;
        }

        foreach ($this->types as $currentType) {
            $isValidType = $this->assertTypeEquals($currentType, $value);

            if ($isValidType) {
                return true;
            }
        }

        $type = \gettype($value);

        $expectedTypes = \implode('|', $this->getTypes());

        throw new \InvalidArgumentException("Invalid type: expected {$this->name} to be of type {{$expectedTypes}}, instead got value `" . \print_r($value, true) . "` with type {{$type}}.");
    }

    public static function fromPhpDocumentorProperty(\phpDocumentor\Reflection\DocBlock\Tags\Property $phpDocumentorReflectionProperty): self
    {
        $tmpProperty = $phpDocumentorReflectionProperty->getVariableName();
        $tmpObject = new \stdClass();
        $tmpObject->{$tmpProperty} = null;

        $tmpReflection = new self(new \ReflectionProperty($tmpObject, $tmpProperty), $tmpObject);

        $type = $phpDocumentorReflectionProperty->getType();

        if ($type) {
            $tmpReflection->hasTypeDeclaration = true;

            $docTypes = self::parseDocTypeObject($type);
            if (\is_array($docTypes) === true) {
                foreach ($docTypes as $docType) {
                    $tmpReflection->types[] = $docType;
                }
            } else {
                $tmpReflection->types[] = $docTypes;
            }

            if (\in_array('null', $tmpReflection->types, true)) {
                $tmpReflection->isNullable = true;
            }
        }

        return $tmpReflection;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param \phpDocumentor\Reflection\Type $type
     *
     * @return string|string[]
     */
    public static function parseDocTypeObject($type)
    {
        if ($type instanceof \phpDocumentor\Reflection\Types\Object_) {
            $tmpObject = (string) $type->getFqsen();
            if ($tmpObject) {
                return $tmpObject;
            }

            return 'object';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Compound) {
            $types = [];
            foreach ($type as $subType) {
                $types[] = self::parseDocTypeObject($subType);
            }

            return $types;
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Array_) {
            $valueTypeTmp = $type->getValueType() . '';
            if ($valueTypeTmp !== 'mixed') {
                return $valueTypeTmp . '[]';
            }

            return 'array';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Null_) {
            return 'null';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Mixed_) {
            return 'mixed';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Scalar) {
            return 'string|int|float|bool';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Boolean) {
            return 'bool';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Callable_) {
            return 'callable';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Float_) {
            return 'float';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\String_) {
            return 'string';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Integer) {
            return 'int';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Void_) {
            return 'void';
        }

        if ($type instanceof \phpDocumentor\Reflection\Types\Resource_) {
            return 'resource';
        }

        return $type . '';
    }

    /**
     * @param string $type
     * @param mixed  $value
     *
     * @return bool
     */
    protected function assertTypeEquals(string $type, $value): bool
    {
        if (\strpos($type, '[]') !== false) {
            return $this->isValidGenericCollection($type, $value);
        }

        if ($type === 'mixed' && $value !== null) {
            return true;
        }

        return $value instanceof $type
               ||
               \gettype($value) === (self::$typeMapping[$type] ?? $type);
    }

    protected function isValidGenericCollection(string $type, $collection): bool
    {
        if (!\is_array($collection)) {
            return false;
        }

        $valueType = \str_replace('[]', '', $type);

        foreach ($collection as $value) {
            if (!$this->assertTypeEquals($valueType, $value)) {
                return false;
            }
        }

        return true;
    }
}
