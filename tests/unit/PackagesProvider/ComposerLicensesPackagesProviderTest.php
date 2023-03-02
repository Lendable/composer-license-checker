<?php

declare(strict_types=1);

namespace Tests\Unit\Lendable\ComposerLicenseChecker\PackagesProvider;

use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;
use Lendable\ComposerLicenseChecker\Exception\FailedRunningComposer;
use Lendable\ComposerLicenseChecker\PackagesProvider\ComposerLicensesPackagesProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\Lendable\ComposerLicenseChecker\StubComposerRunner;

final class ComposerLicensesPackagesProviderTest extends TestCase
{
    private StubComposerRunner $composerRunner;

    private ComposerLicensesPackagesProvider $provider;

    protected function setUp(): void
    {
        $this->composerRunner = new StubComposerRunner();
        $this->provider = new ComposerLicensesPackagesProvider($this->composerRunner);
    }

    public function test_returns_parsed_packages(): void
    {
        $this->composerRunner->setOutput(\json_encode([
            'dependencies' => [
                'vendor/project' => ['license' => ['MIT', 'LGPL']],
                'vendor2/project' => ['license' => ['WTFPL']],
            ],
        ], \JSON_THROW_ON_ERROR));

        $packages = \iterator_to_array($this->provider->provide('path', false));

        self::assertCount(2, $packages);
        self::assertSame('vendor/project', $packages[0]->name->toString());
        self::assertSame(['MIT', 'LGPL'], $packages[0]->licenses);

        self::assertSame('vendor2/project', $packages[1]->name->toString());
        self::assertSame(['WTFPL'], $packages[1]->licenses);
    }

    public function test_wraps_and_throws_composer_runner_failure(): void
    {
        $this->composerRunner->willThrow();

        $exception = null;

        try {
            $this->provider->provide('path', false);
        } catch (\Throwable $exception) {
        }

        self::assertInstanceOf(FailedProvidingPackages::class, $exception);
        self::assertInstanceOf(FailedRunningComposer::class, $exception->getPrevious());
    }

    public function test_throws_on_invalid_json(): void
    {
        $this->composerRunner->setOutput('[invalid, "json"]');

        $this->expectExceptionObject(FailedProvidingPackages::withReason('Decoding failed "Syntax error"'));

        $this->provider->provide('path', false);
    }

    public function test_throws_when_decoded_json_is_not_an_array(): void
    {
        $this->composerRunner->setOutput('"json"');

        $this->expectExceptionObject(FailedProvidingPackages::withReason('Decoded data in unexpected format'));

        $this->provider->provide('path', false);
    }
}
