<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Tester\CommandTester;

final class SingleCommandApplicationTester
{
    private readonly CommandTester $commandTester;

    public function __construct(SingleCommandApplication $command)
    {
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @param array<mixed> $input
     * @param array<mixed> $options
     */
    public function execute(array $input, array $options = []): int
    {
        $prevShellVerbosity = \getenv('SHELL_VERBOSITY');

        try {
            return $this->commandTester->execute($input, $options);
        } finally {
            // Seems like Symfony goes to efforts to reset SHELL_VERBOSITY when using ApplicationTester, but not for CommandTester
            // this leaves the env var set and further test case execution will have that SHELL_VERBOSITY setting.
            if ($prevShellVerbosity === false) {
                @\putenv('SHELL_VERBOSITY');
                unset($_ENV['SHELL_VERBOSITY'], $_SERVER['SHELL_VERBOSITY']);
            } else {
                @\putenv('SHELL_VERBOSITY='.$prevShellVerbosity);
                $_ENV['SHELL_VERBOSITY'] = $prevShellVerbosity;
                $_SERVER['SHELL_VERBOSITY'] = $prevShellVerbosity;
            }
        }
    }

    public function getDisplay(bool $normalize = false): string
    {
        return $this->commandTester->getDisplay($normalize);
    }

    public function getErrorOutput(bool $normalize = false): string
    {
        return $this->commandTester->getErrorOutput($normalize);
    }

    public function getInput(): InputInterface
    {
        return $this->commandTester->getInput();
    }

    public function getOutput(): OutputInterface
    {
        return $this->commandTester->getOutput();
    }

    public function getStatusCode(): int
    {
        return $this->commandTester->getStatusCode();
    }

    public function assertCommandIsSuccessful(string $message = ''): void
    {
        $this->commandTester->assertCommandIsSuccessful($message);
    }
}
