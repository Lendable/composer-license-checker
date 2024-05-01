<?php

declare(strict_types=1);

namespace Tests\E2E\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\ComposerRunner\SymfonyProcessComposerRunner;
use Lendable\ComposerLicenseChecker\PackagesProvider;
use Lendable\ComposerLicenseChecker\PackagesProvider\ComposerLicensesPackagesProvider;
use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use Tests\Support\Lendable\ComposerLicenseChecker\CommandTesterAsserter;
use Tests\Support\Lendable\ComposerLicenseChecker\LicenseConfigurationFileBuilder;

#[DisableReturnValueGenerationForTestDoubles]
final class ComposerLicensesLicenseCheckerTest extends LicenseCheckerCase
{
    protected function packagesProvider(): PackagesProvider
    {
        return new ComposerLicensesPackagesProvider(new SymfonyProcessComposerRunner());
    }

    public function test_verbose_mode_doesnt_impact_composer_execution(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->withAllowedPackage('lendable/unlicensed')->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => 'tests/data/with_unlicensed', '-vvv' => true]);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->hasStatusCode(0);
    }
}
