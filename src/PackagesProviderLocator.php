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
     * @param non-empty-string $id
     *
     * @throws PackagesProviderNotLocated
     */
    public function locate(string $id): PackagesProvider;
}
