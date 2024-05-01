<?php

declare(strict_types=1);

namespace Tests\Unit\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\InvalidPackageName;
use Lendable\ComposerLicenseChecker\PackageName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\TestCase;

#[CoversClass(PackageName::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class PackageNameTest extends TestCase
{
    public function test_construction(): void
    {
        $packageName = new PackageName('vendor/project');

        self::assertSame('vendor', $packageName->vendor);
        self::assertSame('project', $packageName->project);
        self::assertSame('vendor/project', $packageName->toString());
    }

    /**
     * @param non-empty-string $packageName
     */
    #[DataProvider('invalidPackageNamesProvider')]
    public function test_throws_on_invalid_package_name(string $packageName): void
    {
        $this->expectExceptionObject(InvalidPackageName::for($packageName));

        new PackageName($packageName);
    }

    /**
     * @return iterable<array{non-empty-string}>
     */
    public static function invalidPackageNamesProvider(): iterable
    {
        yield 'empty' => [' '];
        yield 'vendor only' => ['vendor'];
        yield 'too many parts' => ['vendor/name/subname'];
        yield 'only a /' => ['/'];
        yield 'vendor/' => ['vendor/'];
        yield '/package' => ['/package'];
    }
}
