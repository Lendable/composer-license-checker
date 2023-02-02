<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\ComposerRunner;
use Lendable\ComposerLicenseChecker\Exception\FailedRunningComposer;

final class StubComposerRunner implements ComposerRunner
{
    private bool $willThrow = false;

    public function __construct(
        private string $output = '',
    ) {
    }

    public function licenses(string $projectPath): string
    {
        if ($this->willThrow) {
            throw FailedRunningComposer::withCommand($this->output === '' ? 'unknown' : $this->output);
        }

        return $this->output;
    }

    public function setOutput(string $output): void
    {
        $this->output = $output;
    }

    public function willThrow(string $message = 'problem, officer?'): void
    {
        $this->willThrow = true;
        $this->output = $message;
    }
}
