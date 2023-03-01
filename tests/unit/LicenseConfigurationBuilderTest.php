<?php

declare(strict_types=1);

namespace Tests\Unit\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\LicenseConfigurationBuilder;
use PHPUnit\Framework\TestCase;

final class LicenseConfigurationBuilderTest extends TestCase
{
    public function test_builds_expected_default_configuration(): void
    {
        $configuration = (new LicenseConfigurationBuilder())->build();

        self::assertSame([], $configuration->allowedLicenses);
        self::assertSame([], $configuration->allowedPackagePatterns);
        self::assertFalse($configuration->ignoreDev);
    }

    public function test_builds_expected_configuration(): void
    {
        $configuration = (new LicenseConfigurationBuilder())
            ->addLicenses('MIT', 'WTFPL')
            ->addAllowedVendor('someone')
            ->addAllowedPackage('other/person')
            ->addAllowedVendor('lenbadle')
            ->ignoreDev()
            ->build();

        self::assertSame(['MIT', 'WTFPL'], $configuration->allowedLicenses);
        self::assertSame(['~^someone/.+$~', '~^other/person$~', '~^lenbadle/.+$~'], $configuration->allowedPackagePatterns);
        self::assertSame(true, $configuration->ignoreDev);
    }

    public function test_license_can_be_removed(): void
    {
        $configuration = (new LicenseConfigurationBuilder())
            ->addLicenses('MIT', 'WTFPL', 'LGPL')
            ->removeLicenses('WTFPL')
            ->build();

        self::assertSame(['MIT', 'LGPL'], $configuration->allowedLicenses);
    }
}
