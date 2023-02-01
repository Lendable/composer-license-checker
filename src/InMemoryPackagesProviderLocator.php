<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\PackagesProviderNotLocated;

final class InMemoryPackagesProviderLocator implements PackagesProviderLocator
{
    /**
     * @param array<value-of<PackagesProviderType>, PackagesProvider> $providers
     */
    public function __construct(private readonly array $providers)
    {
    }

    public function locate(PackagesProviderType $type): PackagesProvider
    {
        return $this->providers[$type->value] ?? throw PackagesProviderNotLocated::withType($type);
    }
}
