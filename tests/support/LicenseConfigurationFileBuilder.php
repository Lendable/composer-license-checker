<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

final class LicenseConfigurationFileBuilder
{
    /**
     * @var list<string>
     */
    private array $allowedLicenses = [];

    /**
     * @var list<string>
     */
    private array $allowedVendors = [];

    /**
     * @var list<string>
     */
    private array $allowedPackages = [];

    private ?bool $ignoreDev = null;

    /**
     * @param resource $resource
     */
    private function __construct(private readonly mixed $resource) {}

    /**
     * @param resource $resource
     */
    public static function create(mixed $resource): self
    {
        return new self($resource);
    }

    public function withLicense(string $license): self
    {
        $this->allowedLicenses[] = $license;

        return $this;
    }

    public function withAllowedVendor(string $vendor): self
    {
        $this->allowedVendors[] = $vendor;

        return $this;
    }

    public function withAllowedPackage(string $package): self
    {
        $this->allowedPackages[] = $package;

        return $this;
    }

    public function withIgnoreDev(bool $ignore): self
    {
        $this->ignoreDev = $ignore;

        return $this;
    }

    public function build(): string
    {
        \fwrite($this->resource, $this->buildContent());

        return \stream_get_meta_data($this->resource)['uri'] ?? throw new \RuntimeException('Could not fetch meta');
    }

    private function buildContent(): string
    {
        $content = <<<PHP
            <?php

            declare(strict_types=1);

            use Lendable\ComposerLicenseChecker\LicenseConfigurationBuilder;

            return (new LicenseConfigurationBuilder())
            PHP;

        if ($this->allowedLicenses !== []) {
            $content .= \sprintf('->addLicenses(\'%s\')', \implode('\',\'', $this->allowedLicenses));
        }

        foreach ($this->allowedVendors as $allowedVendor) {
            $content .= \sprintf('->addAllowedVendor(\'%s\')', $allowedVendor);
        }

        foreach ($this->allowedPackages as $allowedPackage) {
            $content .= \sprintf('->addAllowedPackage(\'%s\')', $allowedPackage);
        }

        if ($this->ignoreDev !== null) {
            $content .= \sprintf('->ignoreDev(%s)', $this->ignoreDev ? 'true' : 'false');
        }

        return $content.'->build();';
    }
}
