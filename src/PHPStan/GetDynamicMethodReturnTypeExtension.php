<?php

declare(strict_types=1);

namespace Arrayy\PHPStan;

use Arrayy\Arrayy;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Resolves literal dot-notation paths against Arrayy's TData array shape.
 *
 * @internal
 */
final class GetDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Arrayy::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'get';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        if (!isset($methodCall->args[0]) || !$methodCall->args[0] instanceof Arg) {
            return null;
        }

        $pathType = $scope->getType($methodCall->args[0]->value);
        $paths = $pathType->getConstantStrings();
        if (
            \count($paths) !== 1
            || !\str_contains($paths[0]->getValue(), '.')
            || \str_contains($paths[0]->getValue(), '*')
        ) {
            return null;
        }

        $dataType = $this->getArrayyDataType($scope->getType($methodCall->var));
        if ($dataType === null || !$dataType->isConstantArray()->yes()) {
            return null;
        }

        $valueType = $dataType;
        foreach (\explode('.', $paths[0]->getValue()) as $segment) {
            $valueType = TypeCombinator::removeNull($valueType);
            $nestedDataType = $this->getArrayyDataType($valueType);
            if ($nestedDataType !== null) {
                $valueType = $nestedDataType;
            }

            $offsetType = new ConstantStringType($segment);
            if (!$valueType->isOffsetAccessible()->yes() || $valueType->hasOffsetValueType($offsetType)->no()) {
                return $this->getFallbackType($methodCall, $scope);
            }

            $valueType = $valueType->getOffsetValueType($offsetType);
        }

        if (!$valueType->isArray()->yes() && $valueType->getArrays() !== []) {
            // Arrayy wraps array results in an Arrayy instance. If the shape is a
            // union of arrays and scalars, the native method type is safer than
            // pretending the runtime wrapper is a scalar.
            return null;
        }

        if ($valueType->isArray()->yes()) {
            $valueType = new GenericObjectType(
                Arrayy::class,
                [$valueType->getIterableKeyType(), $valueType->getIterableValueType(), $valueType]
            );
        }

        return TypeCombinator::union($valueType, $this->getFallbackType($methodCall, $scope));
    }

    private function getArrayyDataType(Type $type): ?Type
    {
        foreach ($type->getObjectClassReflections() as $classReflection) {
            if ($classReflection->getName() === Arrayy::class) {
                // Plain Arrayy instances freely change shape at runtime. Typed
                // subclasses provide the stable TData contract needed here.
                continue;
            }

            $arrayyReflection = $this->getArrayyReflection($classReflection);
            if ($arrayyReflection === null) {
                continue;
            }

            $dataType = $arrayyReflection->getActiveTemplateTypeMap()->getType('TData');
            if ($dataType !== null) {
                return $dataType;
            }
        }

        return null;
    }

    private function getArrayyReflection(ClassReflection $classReflection): ?ClassReflection
    {
        if ($classReflection->getName() === Arrayy::class) {
            return $classReflection;
        }

        return $classReflection->getAncestorWithClassName(Arrayy::class);
    }

    private function getFallbackType(MethodCall $methodCall, Scope $scope): Type
    {
        if (!isset($methodCall->args[1]) || !$methodCall->args[1] instanceof Arg) {
            return new NullType();
        }

        $fallbackType = $scope->getType($methodCall->args[1]->value);

        return $fallbackType instanceof NeverType ? new NullType() : $fallbackType;
    }
}
