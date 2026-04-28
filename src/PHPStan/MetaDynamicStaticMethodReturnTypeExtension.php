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

        $reflection = new \ReflectionClass($className);
        /** @var Arrayy<array-key, mixed, array<array-key, mixed>> $instance */
        $instance = $reflection->newInstanceWithoutConstructor();

        $properties = [];
        foreach (\array_keys($instance->getPhpDocPropertiesFromClass()) as $propertyName) {
            if (!\is_string($propertyName)) {
                continue;
            }

            $properties[$propertyName] = new ConstantStringType($propertyName);
        }

        return new ObjectShapeType($properties, []);
    }
}
