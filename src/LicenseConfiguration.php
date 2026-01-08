<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

final readonly class LicenseConfiguration
{
    /**
     * @param list<string> $allowedLicenses
     * @param list<string> $allowedPackagePatterns
     */
    public function __construct(
        public array $allowedLicenses,
        public array $allowedPackagePatterns,
        public bool $ignoreDev,
    ) {}

    public function allowsLicenseOfPackage(Package $package): bool
    {
        foreach ($package->licenses as $license) {
            if ($this->allowsLicense($license)) {
                return true;
            }
        }

        return false;
    }

    public function allowsPackage(string $package): bool
    {
        return \array_any(
            $this->allowedPackagePatterns,
            static fn($pattern): bool => \preg_match($pattern, $package) === 1,
        );
    }

    private function allowsLicense(string $license): bool
    {
        return \in_array($license, $this->allowedLicenses, true);
    }
}
