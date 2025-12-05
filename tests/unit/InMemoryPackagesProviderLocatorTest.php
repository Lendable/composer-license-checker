<?php

declare(strict_types=1);

namespace Tests\Unit\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\PackagesProviderNotLocated;
use Lendable\ComposerLicenseChecker\InMemoryPackagesProviderLocator;
use Lendable\ComposerLicenseChecker\PackagesProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryPackagesProviderLocator::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class InMemoryPackagesProviderLocatorTest extends TestCase
{
    private PackagesProvider&MockObject $provider1;

    private PackagesProvider&MockObject $provider2;

    private InMemoryPackagesProviderLocator $locator;

    #[\Override]
    protected function setUp(): void
    {
        $this->provider1 = $this->createMock(PackagesProvider::class);
        $this->provider2 = $this->createMock(PackagesProvider::class);

        $this->locator = new InMemoryPackagesProviderLocator([
            'first' => $this->provider1,
            'second' => $this->provider2,
        ]);
    }

    public function test_exposes_ids(): void
    {
        self::assertSame(['first', 'second'], $this->locator->ids());
    }

    public function test_locates_provider_by_id(): void
    {
        $located1 = $this->locator->locate('first');
        $located2 = $this->locator->locate('second');

        self::assertSame($this->provider1, $located1);
        self::assertSame($this->provider2, $located2);
    }

    public function test_throws_on_locating_failure(): void
    {
        $this->expectExceptionObject(PackagesProviderNotLocated::withId('third'));

        $this->locator->locate('third');
    }
}
