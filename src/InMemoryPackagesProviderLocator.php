<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\PackagesProviderNotLocated;

final readonly class InMemoryPackagesProviderLocator implements PackagesProviderLocator
{
    /**
     * @param array<non-empty-string, PackagesProvider> $providers
     */
    public function __construct(private array $providers)
    {
    }

    /**
     * @return list<non-empty-string>
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
