<?php

declare(strict_types=1);

namespace Tests\E2E\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\PackagesProvider\ComposerInstalledJsonPackagesProvider;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;

#[DisableReturnValueGenerationForTestDoubles]
final class ComposerInstalledJsonLicenseCheckerTest extends LicenseCheckerCase
{
    #[\Override]
    protected function packagesProvider(): ComposerInstalledJsonPackagesProvider
    {
        return new ComposerInstalledJsonPackagesProvider();
    }
}
