<?php

declare(strict_types=1);

namespace Tests\Unit\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Licenses;
use Lendable\ComposerLicenseChecker\Package;
use Lendable\ComposerLicenseChecker\PackageName;
use Lendable\ComposerLicenseChecker\Packages;
use PHPUnit\Framework\TestCase;

final class PackagesTest extends TestCase
{
    public function test_returns_instance_with_sorted_packages(): void
    {
        $packages = new Packages([
            new Package(new PackageName('c/d'), new Licenses([])),
            new Package(new PackageName('x/y'), new Licenses([])),
            new Package(new PackageName('c/b'), new Licenses([])),
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
        self::assertCount(1, new Packages([new Package(new PackageName('a/b'), new Licenses([]))]));
        self::assertCount(2, new Packages([
            new Package(new PackageName('a/a'), new Licenses([])),
            new Package(new PackageName('a/b'), new Licenses([])),
        ]));
    }
}
