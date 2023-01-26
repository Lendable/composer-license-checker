<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

interface ComposerRunner
{
    public function licenses(string $projectPath): string;
}
