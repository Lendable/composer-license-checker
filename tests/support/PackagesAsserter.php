<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Package;
use Lendable\ComposerLicenseChecker\Packages;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;

final class PackagesAsserter
{
    private function __construct(private readonly Packages $packages)
    {
    }

    public static function assertThat(Packages $packages): self
    {
        return new self($packages);
    }

    public function hasCount(int $count): self
    {
        Assert::assertCount($count, $this->packages);

        return $this;
    }

    /**
     * @param \Countable|array<mixed> $countable
     */
    public function sameSize(\Countable|array $countable): self
    {
        Assert::assertSameSize($countable, $this->packages);

        return $this;
    }

    public function containsPackage(Package $package): self
    {
        foreach ($this->packages as $existing) {
            try {
                PackageAsserter::assertThat($existing)->equals($package);

                return $this;
            } catch (AssertionFailedError) {
            }
        }

        Assert::fail(
            \sprintf(
                'Failed to find a package with name "%s" and licenses [%s].',
                $package->name->toString(),
                \implode(', ', $package->licenses),
            ),
        );
    }

    public function equals(Packages $packages): self
    {
        $this->sameSize($packages);

        $iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ALL);
        $iterator->attachIterator(new \IteratorIterator($packages));
        $iterator->attachIterator(new \IteratorIterator($this->packages));

        foreach ($iterator as [$expected, $actual]) {
            PackageAsserter::assertThat($actual)->equals($expected);
        }

        return $this;
    }
}
