<?php

declare(strict_types=1);

namespace Tests\E2E\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\ComposerRunner\SymfonyProcessComposerRunner;
use Lendable\ComposerLicenseChecker\PackagesProvider;
use Lendable\ComposerLicenseChecker\PackagesProvider\ComposerLicensesPackagesProvider;

final class ComposerLicensesLicenseCheckerTest extends LicenseCheckerCase
{
    protected function packagesProvider(): PackagesProvider
    {
        return new ComposerLicensesPackagesProvider(new SymfonyProcessComposerRunner());
    }
}
