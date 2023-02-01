<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;

interface PackagesProvider
{
    /**
     * @param non-empty-string $projectPath
     *
     * @throws FailedProvidingPackages
     */
    public function provide(string $projectPath): Packages;
}
