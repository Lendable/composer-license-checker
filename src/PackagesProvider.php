<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;

interface PackagesProvider
{
    /**
     * @throws FailedProvidingPackages
     */
    public function provide(string $projectPath): Packages;
}
