<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Psl\Regex;

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
        return \in_array($license, $this->allowedLicenses, true);
    }

    public function allowsPackage(string $package): bool
    {
        foreach ($this->allowedPackagePatterns as $pattern) {
            if (Regex\matches($package, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
