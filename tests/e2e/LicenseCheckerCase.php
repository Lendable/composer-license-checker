<?php

declare(strict_types=1);

namespace Tests\E2E\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\InMemoryPackagesProviderLocator;
use Lendable\ComposerLicenseChecker\LicenseChecker;
use Lendable\ComposerLicenseChecker\LicenseConfiguration;
use Lendable\ComposerLicenseChecker\PackagesProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\Lendable\ComposerLicenseChecker\CommandTesterAsserter;
use Tests\Support\Lendable\ComposerLicenseChecker\LicenseConfigurationFileBuilder;
use Tests\Support\Lendable\ComposerLicenseChecker\SingleCommandApplicationTester;

abstract class LicenseCheckerCase extends TestCase
{
    protected SingleCommandApplicationTester $commandTester;

    protected string $path = 'tests'.\DIRECTORY_SEPARATOR.'data'.\DIRECTORY_SEPARATOR.'default'.\DIRECTORY_SEPARATOR;

    protected function setUp(): void
    {
        $command = new LicenseChecker(
            new InMemoryPackagesProviderLocator([
                'id' => $this->packagesProvider(),
            ]),
        );
        $command->setAutoExit(false);

        $this->commandTester = new SingleCommandApplicationTester($command);
    }

    abstract protected function packagesProvider(): PackagesProvider;

    public function test_failure_with_non_existent_allowed_licenses_file(): void
    {
        $this->commandTester->execute(['--allow-file' => 'dont_have.pls']);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->encounteredError('File "dont_have.pls" could not be read.');
    }

    public function test_failure_with_non_existent_path(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => 'joke/path']);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->encounteredError('The provided path "joke/path" does not exist.');
    }

    public function test_failure_with_path_that_is_not_composer_project_root(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => '../']);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->hasStatusCode(1)
            ->containsInStdout('Failed to provide packages:');
    }

    public function test_failure_with_invalid_allowed_licenses_file(): void
    {
        $this->commandTester->execute([
            '--allow-file' => 'tests/data/default/invalid_allowed_licenses.php',
            '--path' => $this->path,
        ]);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->encounteredError(
                \sprintf(
                    "File \"tests/data/default/invalid_allowed_licenses.php\" must return an instance of\n         %s.",
                    LicenseConfiguration::class,
                )
            );
    }

    public function test_failure_with_invalid_output_format(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--format' => 'foo']);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->encounteredError('Format must be one of [human, json], "foo" is invalid.');
    }

    public function test_no_licenses_allowed(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->foundLicensingIssues(
                [
                    'lendable/apache' => 'Apache-2.0',
                    'lendable/bsd3_mit' => ['BSD-3-Clause', 'MIT'],
                    'package/bsd3' => 'BSD-3-Clause',
                    'package/mit' => 'MIT',
                ]
            );
    }

    public function test_with_unlicensed_package_from_non_trusted_vendor(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => 'tests/data/with_unlicensed']);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->foundLicensingIssues(['lendable/unlicensed' => null]);
    }

    public function test_with_unlicensed_package_from_trusted_vendor(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->withAllowedVendor('lendable')->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => 'tests/data/with_unlicensed']);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->foundNoLicensingIssues();
    }

    public function test_with_unlicensed_package_which_is_explicitly_allowed(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->withAllowedPackage('lendable/unlicensed')->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => 'tests/data/with_unlicensed']);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->foundNoLicensingIssues();
    }

    public function test_report_not_allowed_licenses(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withLicense('MIT')
            ->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->foundLicensingIssues(
                [
                    'lendable/apache' => 'Apache-2.0',
                    'lendable/bsd3_mit' => 'BSD-3-Clause',
                    'package/bsd3' => 'BSD-3-Clause',
                ]
            );
    }

    public function test_report_not_allowed_vendor_licenses(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withAllowedVendor('lendable')
            ->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->foundLicensingIssues([
                'package/bsd3' => 'BSD-3-Clause',
                'package/mit' => 'MIT',
            ]);
    }

    public function test_all_licenses_allowed(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withLicense('Apache-2.0')
            ->withLicense('BSD-3-Clause')
            ->withLicense('MIT')
            ->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->foundNoLicensingIssues();
    }

    public function test_all_licenses_allowed_through_vendors(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withAllowedVendor('lendable')
            ->withAllowedVendor('package')
            ->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->foundNoLicensingIssues();
    }

    public function test_ignores_dev_dependencies_when_requested_to_through_option(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withLicense('MIT')
            ->withLicense('BSD-3-Clause')
            ->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path, '--no-dev' => true]);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->foundNoLicensingIssues();
    }

    public function test_ignores_dev_dependencies_when_requested_to_through_config(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withLicense('MIT')
            ->withLicense('BSD-3-Clause')
            ->withIgnoreDev(true)
            ->build();

        $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);

        CommandTesterAsserter::assertThat($this->commandTester)
            ->foundNoLicensingIssues();
    }

    /**
     * @return resource
     */
    final protected function createTempFile(): mixed
    {
        $handle = \tmpfile();

        if ($handle === false) {
            throw new \RuntimeException('Failed to create temp file.');
        }

        return $handle;
    }
}
