<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

final class LicenseConfiguration
{
    /**
     * @param list<string> $allowedLicenses
     * @param list<string> $allowedPackagePatterns
     */
    public function __construct(public readonly array $allowedLicenses, public readonly array $allowedPackagePatterns)
    {
    }

    public function allowsLicense(string $license): bool
    {
        return \in_array($license, $this->allowedLicenses);
    }

    public function allowsPackage(string $package): bool
    {
        foreach ($this->allowedPackagePatterns as $pattern) {
            if (\preg_match($pattern, $package) === 1) {
                return true;
            }
        }

        return false;
    }
}
