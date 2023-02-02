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

    private bool $ignoreDev = false;

    public function __construct()
    {
    }

    public function addLicenses(string $license, string ...$rest): self
    {
        foreach ([$license, ...$rest] as $entry) {
            $this->licenses[$entry] = true;
        }

        return $this;
    }

    public function removeLicenses(string $license, string ...$rest): self
    {
        foreach ([$license, ...$rest] as $entry) {
            unset($this->licenses[$entry]);
        }

        return $this;
    }

    public function addAllowedPackage(string $package): self
    {
        $this->packagePatterns[\sprintf('~^%s$~', \preg_quote($package, '~'))] = true;

        return $this;
    }

    public function addAllowedVendor(string $vendor): self
    {
        $this->packagePatterns[\sprintf('~^%s/.+$~', \preg_quote($vendor, '~'))] = true;

        return $this;
    }

    public function ignoreDev(bool $ignore = true): self
    {
        $this->ignoreDev = $ignore;

        return $this;
    }

    public function build(): LicenseConfiguration
    {
        return new LicenseConfiguration(
            \array_keys($this->licenses),
            \array_keys($this->packagePatterns),
            $this->ignoreDev,
        );
    }
}
