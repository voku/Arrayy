<?php

namespace Arrayy;

use phpDocumentor\Reflection\Type;

/**
 * Class Property
 *
 * inspired by https://github.com/spatie/value-object
 */
class Property extends \ReflectionProperty
{
  /**
   * @var array
   */
  protected static $typeMapping = [
      'int'  => 'integer',
      'bool' => 'boolean',
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
   * @param null|\ReflectionProperty $reflectionProperty
   * @param object                   $fakeObject
   *
   * @throws \ReflectionException
   */
  public function __construct($reflectionProperty, $fakeObject = null)
  {
    parent::__construct($fakeObject, $reflectionProperty->getName());
  }

  /**
   * @param string $type
   * @param mixed  $value
   *
   * @return bool
   */
  protected function assertTypeEquals(string $type, $value): bool
  {
    if (strpos($type, '[]') !== false) {
      return $this->isValidGenericCollection($type, $value);
    }

    if ($type === 'mixed' && $value !== null) {
      return true;
    }

    return $value instanceof $type
           ||
           \gettype($value) === (self::$typeMapping[$type] ?? $type);
  }

  public function checkType($value)
  {
    if (!$this->isValidType($value)) {
      $this->throwInvalidType($value);
    }
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

  /**
   * @param mixed $value
   *
   * @return bool
   */
  protected function isValidType($value): bool
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

    return false;
  }

  /**
   * @param Type $type
   *
   * @return string[]|string
   */
  protected static function parseDocTypeObject(Type $type)
  {
    if ($type instanceof \phpDocumentor\Reflection\Types\Object_) {
      return (string)$type->getFqsen();
    }

    if ($type instanceof \phpDocumentor\Reflection\Types\Array_) {
      $value = $type->getValueType();
      if ($value instanceof \phpDocumentor\Reflection\Types\Mixed_) {
        return 'mixed';
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

    if ($type instanceof \phpDocumentor\Reflection\Types\Compound) {
      $types = [];
      foreach ($type as $subType) {
        $types[] = self::parseDocTypeObject($subType);
      }

      return $types;
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

    throw new \Exception('Unhandled PhpDoc type: ' . get_class($type));
  }

  /**
   * @param mixed $value
   */
  protected function throwInvalidType($value)
  {
    $type = \gettype($value);

    if ($type === 'NULL') {
      $value = 'null';
    } elseif ($type === 'object') {
      $value = \get_class($value);
    } elseif ($type === 'array') {
      $value = 'array';
    }

    $expectedTypes = \implode('|', $this->getTypes());

    throw new \InvalidArgumentException("Invalid type: expected {$this->name} to be of type {{$expectedTypes}}, instead got value `{$value}` with type {{$type}}.");
  }
}
