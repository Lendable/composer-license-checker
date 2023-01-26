<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\PackagesProviderNotLocated;

interface PackagesProviderLocator
{
    /**
     * @return list<string>
     */
    public function ids(): array;

    /**
     * @throws PackagesProviderNotLocated
     */
    public function locate(string $id): PackagesProvider;
}
