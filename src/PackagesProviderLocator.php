<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\PackagesProviderNotLocated;

interface PackagesProviderLocator
{
    /**
     * @return list<non-empty-string>
     */
    public function ids(): array;

    /**
     * @throws PackagesProviderNotLocated
     */
    /**
     * @param non-empty-string $id
     */
    public function locate(string $id): PackagesProvider;
}
