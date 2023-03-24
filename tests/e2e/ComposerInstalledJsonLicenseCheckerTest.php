<?php

declare(strict_types=1);

namespace Tests\E2E\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\PackagesProvider\ComposerInstalledJsonPackagesProvider;

final class ComposerInstalledJsonLicenseCheckerTest extends LicenseCheckerCase
{
    protected function packagesProvider(): ComposerInstalledJsonPackagesProvider
    {
        return new ComposerInstalledJsonPackagesProvider();
    }
}
