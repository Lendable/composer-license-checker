<?php

declare(strict_types=1);

namespace Tests\Unit\Lendable\ComposerLicenseChecker\PackagesProvider;

use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;
use Lendable\ComposerLicenseChecker\Exception\FailedRunningComposer;
use Lendable\ComposerLicenseChecker\PackagesProvider\ComposerLicensesPackagesProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use PHPUnit\Framework\TestCase;
use Tests\Support\Lendable\ComposerLicenseChecker\Result;
use Tests\Support\Lendable\ComposerLicenseChecker\Returning;
use Tests\Support\Lendable\ComposerLicenseChecker\StubComposerRunner;
use Tests\Support\Lendable\ComposerLicenseChecker\Throwing;

#[CoversClass(ComposerLicensesPackagesProvider::class)]
#[DisableReturnValueGenerationForTestDoubles]
final class ComposerLicensesPackagesProviderTest extends TestCase
{
    public function test_returns_parsed_packages(): void
    {
        $provider = $this->createProvider(
            Returning::value(
                \json_encode(
                    [
                        'dependencies' => [
                            'vendor/project' => ['license' => ['MIT', 'LGPL']],
                            'vendor2/project' => ['license' => ['WTFPL']],
                        ],
                    ],
                    \JSON_THROW_ON_ERROR,
                ),
            ),
        );

        $packages = \iterator_to_array($provider->provide('path', false));

        self::assertCount(2, $packages);
        self::assertSame('vendor/project', $packages[0]->name->toString());
        self::assertSame('MIT, LGPL', $packages[0]->licenses->toString());

        self::assertSame('vendor2/project', $packages[1]->name->toString());
        self::assertSame('WTFPL', $packages[1]->licenses->toString());
    }

    public function test_wraps_and_throws_composer_runner_failure(): void
    {
        $provider = $this->createProvider(
            Throwing::exception(FailedRunningComposer::withCommand('composer licenses --format=json')),
        );

        $this->expectExceptionObject(FailedProvidingPackages::dueTo(FailedRunningComposer::withCommand('composer licenses --format=json')));

        $provider->provide('path', false);
    }

    public function test_throws_on_invalid_json(): void
    {
        $provider = $this->createProvider(Returning::value('[invalid, "json"]'));

        $this->expectExceptionObject(FailedProvidingPackages::withReason('Decoding failed "Syntax error"'));

        $provider->provide('path', false);
    }

    public function test_throws_when_decoded_json_is_not_an_array(): void
    {
        $provider = $this->createProvider(Returning::value('"json"'));

        $this->expectExceptionObject(FailedProvidingPackages::withReason('Decoded data in unexpected format'));

        $provider->provide('path', false);
    }

    /**
     * @param Result<string> $result
     */
    private function createProvider(Result $result): ComposerLicensesPackagesProvider
    {
        return new ComposerLicensesPackagesProvider(new StubComposerRunner($result));
    }
}
