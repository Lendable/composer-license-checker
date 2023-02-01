<?php

declare(strict_types=1);

namespace Tests\Unit\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\InMemoryPackagesProviderLocator;
use Lendable\ComposerLicenseChecker\PackagesProvider;
use Lendable\ComposerLicenseChecker\PackagesProviderType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InMemoryPackagesProviderLocatorTest extends TestCase
{
    private PackagesProvider&MockObject $provider1;

    private PackagesProvider&MockObject $provider2;

    private InMemoryPackagesProviderLocator $locator;

    protected function setUp(): void
    {
        $this->provider1 = $this->createMock(PackagesProvider::class);
        $this->provider2 = $this->createMock(PackagesProvider::class);

        $this->locator = new InMemoryPackagesProviderLocator([
            PackagesProviderType::COMPOSER_LICENSES->value => $this->provider1,
            PackagesProviderType::INSTALLED_JSON->value => $this->provider2,
        ]);
    }

    public function test_locates_provider_by_id(): void
    {
        $located1 = $this->locator->locate(PackagesProviderType::COMPOSER_LICENSES);
        $located2 = $this->locator->locate(PackagesProviderType::INSTALLED_JSON);

        self::assertSame($this->provider1, $located1);
        self::assertSame($this->provider2, $located2);
    }
}
