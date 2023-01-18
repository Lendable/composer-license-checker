<?php

declare(strict_types=1);

use Lendable\ComposerLicenseChecker\LicenseConfigurationBuilder;

return (new LicenseConfigurationBuilder())
    ->addLicenses('MIT')
    ->addAllowedVendor('lendable')
    ->addAllowedPackage('foo/bar')
    ->build();
