<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\ComposerRunner;

use Lendable\ComposerLicenseChecker\ComposerRunner;
use Lendable\ComposerLicenseChecker\Exception\FailedRunningComposer;
use Symfony\Component\Process\Process;

final class SymfonyProcessComposerRunner implements ComposerRunner
{
    public function licenses(string $projectPath): string
    {
        $process = Process::fromShellCommandline('SHELL_VERBOSITY=0 composer licenses --format=json', $projectPath);
        $process->run();
        if (!$process->isSuccessful()) {
            throw FailedRunningComposer::withReason(
                \sprintf('Failed to run "%s" (%d)', $process->getCommandLine(), $process->getExitCode()),
            );
        }

        return $process->getOutput();
    }
}
