<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\PackagesProviderNotLocated;

final class InMemoryPackagesProviderLocator implements PackagesProviderLocator
{
    /**
     * @param array<string, PackagesProvider> $providers
     */
    public function __construct(private readonly array $providers)
    {
    }

    /**
     * @return list<string>
     */
    public function ids(): array
    {
        return \array_keys($this->providers);
    }

    public function locate(string $id): PackagesProvider
    {
        return $this->providers[$id] ?? throw PackagesProviderNotLocated::withId($id);
    }
}
