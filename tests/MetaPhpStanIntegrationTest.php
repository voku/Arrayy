<?php

declare(strict_types=1);

namespace Arrayy\tests;

/**
 * @internal
 */
final class MetaPhpStanIntegrationTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        if (!\function_exists('proc_open')) {
            static::markTestSkipped('proc_open() is required to execute PHPStan.');
        }
    }

    public function testPhpStanAcceptsValidMetaUsage(): void
    {
        $this->assertFixturePassesPhpStan('MetaValidUsage.php');
    }

    public function testPhpStanAcceptsValidArrayShapeUsage(): void
    {
        $this->assertFixturePassesPhpStan('ArrayShapeValidUsage.php');
    }

    public function testPhpStanRejectsInvalidMetaUsage(): void
    {
        $output = $this->assertFixtureFailsPhpStan('MetaInvalidUsage.php');

        static::assertStringContainsString('Access to an undefined property', $output);
        static::assertStringContainsString('$ghost', $output);
        static::assertStringContainsString('strlen', $output);
        static::assertStringContainsString('expects string', $output);
        static::assertStringContainsString('int|null', $output);
    }

    public function testPhpStanRejectsInvalidArrayShapeUsage(): void
    {
        $output = $this->assertFixtureFailsPhpStan('ArrayShapeInvalidUsage.php');

        static::assertStringContainsString('Parameter #1 $data of class Arrayy\tests\PHPStan\ArrayShapeUser constructor expects', $output);
        static::assertStringContainsString("array{id: 'wrong', firstName: 'Lars', lastName: 'Moelleken'} given", $output);
        static::assertStringContainsString("array{id: 1, firstName: 'Lars'} given", $output);
        static::assertStringContainsString('Parameter #1 $data of static method Arrayy\Arrayy', $output);
    }

    private function assertFixturePassesPhpStan(string $fixtureFile): void
    {
        [$exitCode, $stdout, $stderr] = $this->runPhpStanFixture($fixtureFile);
        $output = \trim($stdout . $stderr);

        static::assertSame(0, $exitCode, $output);
    }

    private function assertFixtureFailsPhpStan(string $fixtureFile): string
    {
        [$exitCode, $stdout, $stderr] = $this->runPhpStanFixture($fixtureFile);
        $output = \trim($stdout . $stderr);

        static::assertSame(1, $exitCode, $output);

        return $output;
    }

    /**
     * @return array{0: int, 1: string, 2: string}
     */
    private function runPhpStanFixture(string $fixtureFile): array
    {
        $repoRoot = \dirname(__DIR__);
        $command = [
            \PHP_BINARY,
            $repoRoot . '/vendor/bin/phpstan',
            'analyse',
            '--no-progress',
            '--error-format=raw',
            '--configuration=' . $repoRoot . '/phpstan-fixtures.neon',
            $repoRoot . '/tests/PHPStan/' . $fixtureFile,
        ];

        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = \proc_open($command, $descriptorSpec, $pipes, $repoRoot);
        static::assertIsResource($process);

        $stdout = \stream_get_contents($pipes[1]) ?: '';
        $stderr = \stream_get_contents($pipes[2]) ?: '';

        \fclose($pipes[1]);
        \fclose($pipes[2]);

        $exitCode = \proc_close($process);

        return [$exitCode, (string) $stdout, (string) $stderr];
    }
}
