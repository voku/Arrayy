<?php

declare(strict_types=1);

namespace Arrayy\tests\PHPStan;

use Arrayy\Arrayy;
use Arrayy\PHPStan\GetDynamicMethodReturnTypeExtension;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GetDynamicMethodReturnTypeExtensionTest extends TestCase
{
    public function testExtensionMetadataOnlySupportsGet(): void
    {
        $extension = new GetDynamicMethodReturnTypeExtension();
        $get = $this->createMock(MethodReflection::class);
        $get->method('getName')->willReturn('get');
        $set = $this->createMock(MethodReflection::class);
        $set->method('getName')->willReturn('set');

        static::assertSame(Arrayy::class, $extension->getClass());
        static::assertTrue($extension->isMethodSupported($get));
        static::assertFalse($extension->isMethodSupported($set));
    }

    /**
     * @dataProvider unsupportedPathProvider
     */
    public function testUnsupportedPathsFallBackToTheNativeMethodType(string $path): void
    {
        $extension = new GetDynamicMethodReturnTypeExtension();
        $call = $this->createGetCall($path);
        $scope = $this->createMock(Scope::class);
        $scope->method('getType')->willReturnCallback(
            static fn ($node) => $node instanceof String_ && $node->value === $path
                ? new ConstantStringType($path)
                : new MixedType()
        );

        static::assertNull(
            $extension->getTypeFromMethodCall($this->createMock(MethodReflection::class), $call, $scope)
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public function unsupportedPathProvider(): array
    {
        return [
            'not nested' => ['profile'],
            'wildcard'   => ['profile.*'],
        ];
    }

    public function testCallWithoutAPathIsNotHandled(): void
    {
        $extension = new GetDynamicMethodReturnTypeExtension();
        $scope = $this->createMock(Scope::class);
        $call = new MethodCall(new Variable('arrayy'), 'get');

        static::assertNull(
            $extension->getTypeFromMethodCall($this->createMock(MethodReflection::class), $call, $scope)
        );
    }

    public function testTypedPathOnAnUntypedReceiverIsNotHandled(): void
    {
        $extension = new GetDynamicMethodReturnTypeExtension();
        $call = $this->createGetCall('profile.name');
        $scope = $this->createMock(Scope::class);
        $scope->method('getType')->willReturnCallback(
            static fn ($node) => $node instanceof String_ && $node->value === 'profile.name'
                ? new ConstantStringType('profile.name')
                : new MixedType()
        );

        static::assertNull(
            $extension->getTypeFromMethodCall($this->createMock(MethodReflection::class), $call, $scope)
        );
    }

    public function testFallbackTypeHandlesMissingNeverAndConcreteDefaults(): void
    {
        $extension = new GetDynamicMethodReturnTypeExtension();
        $method = new \ReflectionMethod($extension, 'getFallbackType');
        $scope = $this->createMock(Scope::class);

        $withoutFallback = $this->createGetCall('profile.name');
        static::assertInstanceOf(NullType::class, $method->invoke($extension, $withoutFallback, $scope));

        $withFallback = $this->createGetCall('profile.name', new Arg(new String_('Guest')));
        $scope->method('getType')->willReturnOnConsecutiveCalls(new NeverType(), new ConstantStringType('Guest'));
        static::assertInstanceOf(NullType::class, $method->invoke($extension, $withFallback, $scope));

        $fallback = $method->invoke($extension, $withFallback, $scope);
        static::assertInstanceOf(ConstantStringType::class, $fallback);
        static::assertSame('Guest', $fallback->getValue());
    }

    private function createGetCall(string $path, ?Arg $fallback = null): MethodCall
    {
        $args = [new Arg(new String_($path))];
        if ($fallback !== null) {
            $args[] = $fallback;
        }

        return new MethodCall(new Variable('arrayy'), 'get', $args);
    }
}
