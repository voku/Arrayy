<?php

declare(strict_types=1);

namespace Arrayy\tests;

/**
 * @internal
 */
final class MetaPhpStanIntegrationTest extends \PHPUnit\Framework\TestCase
{
    public function testPhpStanAcceptsValidMetaUsage(): void
    {
        [$exitCode, $output] = $this->runPhpStanFixture('MetaValidUsage.php');

        static::assertSame(0, $exitCode, $output);
    }

    public function testPhpStanRejectsInvalidMetaUsage(): void
    {
        [$exitCode, $output] = $this->runPhpStanFixture('MetaInvalidUsage.php');

        static::assertSame(1, $exitCode, $output);
        static::assertStringContainsString('undefined property', \strtolower($output));
        static::assertStringContainsString('$ghost', $output);
        static::assertStringContainsString('Parameter #1 $string of function strlen expects string, int|null given.', $output);
    }

    /**
     * @return array{int, string}
     */
    private function runPhpStanFixture(string $fixtureFile): array
    {
        if (!\function_exists('proc_open')) {
            self::markTestSkipped('proc_open() is required to execute PHPStan.');
        }

        $repoRoot = \dirname(__DIR__);
        $command = [
            \PHP_BINARY,
            $repoRoot . '/vendor/bin/phpstan',
            'analyse',
            '--no-progress',
            '--error-format=raw',
            '--configuration=' . $repoRoot . '/phpstan.neon',
            $repoRoot . '/tests/PHPStan/' . $fixtureFile,
        ];

        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = \proc_open($command, $descriptorSpec, $pipes, $repoRoot);
        static::assertIsResource($process);

        $stdout = \stream_get_contents($pipes[1]);
        $stderr = \stream_get_contents($pipes[2]);

        \fclose($pipes[1]);
        \fclose($pipes[2]);

        $exitCode = \proc_close($process);

        return [$exitCode, \trim($stdout . $stderr)];
    }
}
