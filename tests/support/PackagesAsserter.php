<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Packages;
use PHPUnit\Framework\Assert;

final class PackagesAsserter
{
    private function __construct(private readonly Packages $packages)
    {
    }

    public static function assertThat(Packages $packages): self
    {
        return new self($packages);
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

    /**
     * @param \Countable|array<mixed> $countable
     */
    private function sameSize(\Countable|array $countable): self
    {
        Assert::assertSameSize($countable, $this->packages);

        return $this;
    }
}
