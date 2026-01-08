<?php

declare(strict_types=1);

use Lendable\ComposerLicenseChecker\LicenseConfigurationBuilder;

return new LicenseConfigurationBuilder()
    ->addLicenses('BSD-3-Clause', 'MIT')
    ->addAllowedVendor('lendable')
    ->addAllowedPackage('foo/bar')
    ->build();
