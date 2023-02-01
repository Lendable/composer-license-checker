<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\FailedRunningComposer;

interface ComposerRunner
{
    /**
     * @param non-empty-string $projectPath
     *
     * @throws FailedRunningComposer
     */
    public function licenses(string $projectPath): string;
}
