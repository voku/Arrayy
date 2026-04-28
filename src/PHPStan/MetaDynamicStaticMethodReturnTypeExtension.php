<?php

declare(strict_types=1);

namespace Arrayy\PHPStan;

use Arrayy\Arrayy;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ObjectShapeType;
use PHPStan\Type\Type;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;

final class MetaDynamicStaticMethodReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    /**
     * @var array<class-string, ObjectShapeType>
     */
    private array $types = [];

    public function getClass(): string
    {
        return Arrayy::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'meta';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        if (!$methodCall->class instanceof Name) {
            return null;
        }

        $className = $scope->resolveName($methodCall->class);
        if (!\is_a($className, Arrayy::class, true)) {
            return null;
        }

        if (isset($this->types[$className])) {
            return $this->types[$className];
        }

        /** @var object $meta */
        $meta = $className::meta();
        $properties = [];
        foreach (\get_object_vars($meta) as $propertyName => $value) {
            if (!\is_string($propertyName) || !\is_string($value)) {
                continue;
            }

            $properties[$propertyName] = new ConstantStringType($value);
        }

        return $this->types[$className] = new ObjectShapeType($properties, []); // second argument is optionalProperties; meta exposes only concrete keys
    }
}
