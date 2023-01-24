<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

final class LicenseConfigurationBuilder
{
    /**
     * @var array<string, true>
     */
    private array $licenses = [];

    /**
     * @var array<string, true>
     */
    private array $packagePatterns = [];

    public function __construct()
    {
    }

    public function addLicenses(string ...$licenses): self
    {
        foreach ($licenses as $license) {
            $this->licenses[$license] = true;
        }

        return $this;
    }

    public function addAllowedVendor(string $vendor): self
    {
        $this->packagePatterns[\sprintf('~^%s/.+$~', \preg_quote($vendor, '~'))] = true;

        return $this;
    }

    public function build(): LicenseConfiguration
    {
        return new LicenseConfiguration(\array_keys($this->licenses), \array_keys($this->packagePatterns));
    }

    public function addAllowedPackage(string $package): self
    {
        $this->packagePatterns[\sprintf('~^%s$~', \preg_quote($package, '~'))] = true;

        return $this;
    }
}
