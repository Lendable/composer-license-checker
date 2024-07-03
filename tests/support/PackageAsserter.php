<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Licenses;
use Lendable\ComposerLicenseChecker\Package;
use Lendable\ComposerLicenseChecker\PackageName;
use PHPUnit\Framework\Assert;

final readonly class PackageAsserter
{
    private function __construct(private Package $package) {}

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

    public function equals(Package $package): self
    {
        $this->hasName($package->name);
        $this->hasExactLicenses($package->licenses);

        return $this;
    }

    private function hasExactLicenses(Licenses $licenses): void
    {
        Assert::assertSameSize($licenses, $this->package->licenses);

        foreach ($licenses as $license) {
            $this->hasLicense($license);
        }
    }

    private function hasLicense(string $license): void
    {
        Assert::assertContains($license, \explode(', ', $this->package->licenses->toString()));
    }
}
