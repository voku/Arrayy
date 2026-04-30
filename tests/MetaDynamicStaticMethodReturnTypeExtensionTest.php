<?php

declare(strict_types=1);

namespace Arrayy\tests;

use Arrayy\PHPStan\MetaDynamicStaticMethodReturnTypeExtension;
use Arrayy\tests\PHPStan\ArrayShapeUser;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectShapeType;
use PHPStan\Type\VerbosityLevel;

require_once __DIR__ . '/PHPStan/ArrayShapeCity.php';
require_once __DIR__ . '/PHPStan/ArrayShapeUser.php';

/**
 * @internal
 */
final class MetaDynamicStaticMethodReturnTypeExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetClassTargetsArrayy(): void
    {
        $extension = new MetaDynamicStaticMethodReturnTypeExtension();

        self::assertSame(\Arrayy\Arrayy::class, $extension->getClass());
    }

    public function testIsStaticMethodSupportedOnlyForMeta(): void
    {
        $extension = new MetaDynamicStaticMethodReturnTypeExtension();

        $metaMethod = $this->createMock(MethodReflection::class);
        $metaMethod->method('getName')->willReturn('meta');

        $createMethod = $this->createMock(MethodReflection::class);
        $createMethod->method('getName')->willReturn('create');

        self::assertTrue($extension->isStaticMethodSupported($metaMethod));
        self::assertFalse($extension->isStaticMethodSupported($createMethod));
    }

    public function testReturnsNullWhenStaticCallClassIsNotANameNode(): void
    {
        $extension = new MetaDynamicStaticMethodReturnTypeExtension();

        $method = $this->createMock(MethodReflection::class);
        $scope = $this->createMock(Scope::class);
        $scope->expects(self::never())->method('resolveName');

        $type = $extension->getTypeFromStaticMethodCall(
            $method,
            new StaticCall(new Variable('className'), 'meta'),
            $scope
        );

        self::assertNull($type);
    }

    public function testReturnsNullForNonArrayyClasses(): void
    {
        $extension = new MetaDynamicStaticMethodReturnTypeExtension();

        $method = $this->createMock(MethodReflection::class);
        $scope = $this->createMock(Scope::class);
        $scope->expects(self::once())
            ->method('resolveName')
            ->willReturn(\stdClass::class);

        $type = $extension->getTypeFromStaticMethodCall(
            $method,
            new StaticCall(new Name('stdClass'), 'meta'),
            $scope
        );

        self::assertNull($type);
    }

    public function testBuildsAndCachesMetaShapeTypes(): void
    {
        $extension = new MetaDynamicStaticMethodReturnTypeExtension();

        $method = $this->createMock(MethodReflection::class);
        $scope = $this->createMock(Scope::class);
        $scope->expects(self::exactly(2))
            ->method('resolveName')
            ->willReturn(ArrayShapeUser::class);

        $call = new StaticCall(new Name('ArrayShapeUser'), 'meta');

        $firstType = $extension->getTypeFromStaticMethodCall($method, $call, $scope);
        $secondType = $extension->getTypeFromStaticMethodCall($method, $call, $scope);

        self::assertInstanceOf(ObjectShapeType::class, $firstType);
        self::assertSame($firstType, $secondType);
        self::assertSame(
            "object{id: 'id', firstName: 'firstName', lastName: 'lastName', city: 'city'}",
            $firstType->describe(VerbosityLevel::precise())
        );
    }
}
