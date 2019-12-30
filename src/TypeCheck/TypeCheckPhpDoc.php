<?php

/** @noinspection TransitiveDependenciesUsageInspection */
/** @noinspection ClassReImplementsParentInterfaceInspection */

declare(strict_types=1);

namespace Arrayy\TypeCheck;

use phpDocumentor\Reflection\Type;

/**
 * inspired by https://github.com/spatie/value-object
 *
 * @internal
 */
final class TypeCheckPhpDoc extends AbstractTypeCheck implements TypeCheckInterface
{
    /**
     * @var bool
     */
    private $hasTypeDeclaration = false;

    /**
     * @var string
     */
    private $property_name;

    /**
     * @param string $reflectionPropertyName
     */
    public function __construct($reflectionPropertyName)
    {
        $this->property_name = $reflectionPropertyName;
    }

    /**
     * @param \phpDocumentor\Reflection\DocBlock\Tags\Property $phpDocumentorReflectionProperty
     *
     * @return self
     */
    public static function fromPhpDocumentorProperty(\phpDocumentor\Reflection\DocBlock\Tags\Property $phpDocumentorReflectionProperty): self
    {
        $tmpProperty = $phpDocumentorReflectionProperty->getVariableName();
        $tmpObject = new \stdClass();
        $tmpObject->{$tmpProperty} = null;

        $tmpReflection = new self((new \ReflectionProperty($tmpObject, $tmpProperty))->getName());

        $type = $phpDocumentorReflectionProperty->getType();

        /** @noinspection PhpSillyAssignmentInspection */
        /** @var Type|null $type */
        $type = $type;

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
                $typeTmp = self::parseDocTypeObject($subType);

                /** @noinspection PhpSillyAssignmentInspection - hack for phpstan */
                /** @var string $typeTmp */
                $typeTmp = $typeTmp;

                $types[] = $typeTmp;
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
     * @param string $expectedTypes
     * @param mixed  $value
     * @param string $type
     *
     * @return \TypeError
     */
    public function throwException($expectedTypes, $value, $type): \Throwable
    {
        throw new \TypeError("Invalid type: expected \"{$this->property_name}\" to be of type {{$expectedTypes}}, instead got value \"" . $this->valueToString($value) . '" (' . \print_r($value, true) . ") with type {{$type}}.");
    }
}
