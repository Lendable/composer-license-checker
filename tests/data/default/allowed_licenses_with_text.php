ShouldNotAppearInOutput
<?php

use Lendable\ComposerLicenseChecker\LicenseConfigurationBuilder;

return (new LicenseConfigurationBuilder())
    ->addLicenses('BSD-3-Clause', 'MIT')
    ->build();
