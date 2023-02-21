<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Package;
use Lendable\ComposerLicenseChecker\PackageName;
use PHPUnit\Framework\Assert;

final class PackageAsserter
{
    private function __construct(private readonly Package $package)
    {
    }

    public static function assertThat(Package $package): self
    {
        return new self($package);
    }

    public function hasName(string|PackageName $name): self
    {
        if ($name instanceof PackageName) {
            $name = $name->toString();
        }

        Assert::assertSame($name, $this->package->name->toString());

        return $this;
    }

    public function hasLicense(string $license): self
    {
        Assert::assertContains($license, $this->package->licenses);

        return $this;
    }

    /**
     * @param list<string> $licenses
     */
    public function hasExactLicenses(array $licenses): self
    {
        Assert::assertSameSize($licenses, $this->package->licenses);

        foreach ($licenses as $license) {
            $this->hasLicense($license);
        }

        return $this;
    }

    public function equals(Package $package): self
    {
        $this->hasName($package->name);
        $this->hasExactLicenses($package->licenses);

        return $this;
    }
}
