<?php

declare(strict_types=1);

namespace Tests\Unit\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Package;
use Lendable\ComposerLicenseChecker\PackageName;
use Lendable\ComposerLicenseChecker\Packages;
use PHPUnit\Framework\TestCase;

final class PackagesTest extends TestCase
{
    public function test_returns_instance_with_sorted_packages(): void
    {
        $packages = new Packages([
            new Package(new PackageName('c/d'), []),
            new Package(new PackageName('x/y'), []),
            new Package(new PackageName('c/b'), []),
        ]);

        $sorted = $packages->sort();
        self::assertNotSame($packages, $sorted);
        self::assertSame([
            'c/b',
            'c/d',
            'x/y',
        ], \array_map(static fn (Package $package): string => $package->name->toString(), \iterator_to_array($sorted)));
    }

    public function test_returns_count(): void
    {
        self::assertCount(0, new Packages([]));
        self::assertCount(1, new Packages([new Package(new PackageName('a/b'), [])]));
        self::assertCount(2, new Packages([
            new Package(new PackageName('a/a'), []),
            new Package(new PackageName('a/b'), []),
        ]));
    }

    public function test_can_be_iterated(): void
    {
        $packages = new Packages([
            new Package(new PackageName('a/a'), []),
            new Package(new PackageName('a/b'), []),
        ]);

        self::assertCount(2, $packages->getIterator());
        self::assertCount(2, \iterator_to_array($packages->getIterator()));
    }
}
