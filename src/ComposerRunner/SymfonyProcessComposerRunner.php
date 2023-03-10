<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\ComposerRunner;

use Lendable\ComposerLicenseChecker\ComposerRunner;
use Lendable\ComposerLicenseChecker\Exception\FailedRunningComposer;
use Symfony\Component\Process\Process;

final class SymfonyProcessComposerRunner implements ComposerRunner
{
    public function licenses(string $projectPath, bool $ignoreDev): string
    {
        $process = Process::fromShellCommandline(
            \sprintf('composer licenses --format=json %s', $ignoreDev ? '--no-dev' : ''),
            $projectPath,
            ['SHELL_VERBOSITY' => '0'],
        );
        $process->run();
        if (!$process->isSuccessful()) {
            throw FailedRunningComposer::withCommand($process->getCommandLine());
        }

        return $process->getOutput();
    }
}
