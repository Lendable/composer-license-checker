<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final readonly class CommandTesterAsserter
{
    private function __construct(private SingleCommandApplicationTester|CommandTester $commandTester) {}

    public static function assertThat(SingleCommandApplicationTester|CommandTester $commandTester): self
    {
        return new self($commandTester);
    }

    public function successfullyRan(): self
    {
        $this->commandTester->assertCommandIsSuccessful();

        return $this;
    }

    public function hasStatusCode(int $statusCode): self
    {
        Assert::assertSame($statusCode, $this->commandTester->getStatusCode());

        return $this;
    }

    public function hasNormalizedStdout(string $stdout): self
    {
        Assert::assertSame(
            $stdout,
            $this->normalizedStdout(),
        );

        return $this;
    }

    public function foundNoLicensingIssues(): self
    {
        $this->successfullyRan();
        $this->hasNormalizedStdout(
            <<<STDOUT

                Composer License Checker
                ========================

                 [OK] All dependencies have allowed licenses.


                STDOUT
        );

        return $this;
    }

    /**
     * @param array<string, string|list<string>|null> $issues
     */
    public function foundLicensingIssues(array $issues): self
    {
        $this->hasStatusCode(Command::FAILURE);

        $expectedOutput = [
            '',
            'Composer License Checker',
            '========================',
            '',
        ];

        foreach ($issues as $package => $license) {
            if (\is_string($license)) {
                $license = [$license];
            }

            if (\is_array($license)) {
                if (\count($license) > 1) {
                    $expectedOutput[] = \sprintf(
                        ' [ERROR] Dependency "%s" is licensed under any of "%s", none of which are allowed.',
                        $package,
                        \implode(', ', $license),
                    );
                    $expectedOutput[] = '';
                } else {
                    $expectedOutput[] = \sprintf(
                        ' [ERROR] Dependency "%s" is licensed under "%s" which is not in the allowed list.',
                        $package,
                        \implode(', ', $license),
                    );
                    $expectedOutput[] = '';
                }
            } else {
                $expectedOutput[] = \sprintf(
                    ' [ERROR] Dependency "%s" does not have a license and is not explicitly allowed.',
                    $package,
                );
                $expectedOutput[] = '';
            }
        }

        $expectedOutput[] = '';

        $expectedStdout = \implode("\n", $expectedOutput);

        $this->hasNormalizedStdout($expectedStdout);

        return $this;
    }

    public function encounteredError(string $error): self
    {
        $this->hasStatusCode(1);

        $expectedOutput = [
            '',
            'Composer License Checker',
            '========================',
            '',
            ' [ERROR] '.$error,
            '',
            '',
        ];

        $expectedStdout = \implode("\n", $expectedOutput);

        $this->hasNormalizedStdout($expectedStdout);

        return $this;
    }

    /**
     * @param non-empty-string $fragment
     */
    public function containsInStdout(string $fragment): self
    {
        foreach ($this->normalizedStdoutLines() as $line) {
            if (\str_contains($line, $fragment)) {
                Assert::assertThat(true, Assert::isTrue()); // Ensure an assertion is recorded.

                return $this;
            }
        }

        Assert::fail(
            \sprintf(
                "Output did not contain \"%s\". Output\n: %s",
                $fragment,
                $this->normalizedStdout(),
            ),
        );
    }

    /**
     * @param non-empty-string $fragment
     */
    public function doesNotContainInStdout(string $fragment): self
    {
        foreach ($this->normalizedStdoutLines() as $line) {
            if (\str_contains($line, $fragment)) {
                Assert::fail(
                    \sprintf(
                        "Output contained unexpected \"%s\". Output\n: %s",
                        $fragment,
                        $this->normalizedStdout(),
                    ),
                );
            }
        }

        Assert::assertThat(true, Assert::isTrue()); // Ensure an assertion is recorded.

        return $this;
    }

    private function normalizedStdout(): string
    {
        return \implode("\n", $this->normalizedStdoutLines());
    }

    /**
     * @return list<string>
     */
    private function normalizedStdoutLines(): array
    {
        return \array_map(
            static fn(string $line): string => \rtrim($line, " \t"),
            \explode("\n", $this->commandTester->getDisplay(true)),
        );
    }
}
