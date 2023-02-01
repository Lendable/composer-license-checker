<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\PackagesProviderNotLocated;

interface PackagesProviderLocator
{
    /**
     * @throws PackagesProviderNotLocated
     */
    public function locate(PackagesProviderType $type): PackagesProvider;
}
