<?php

declare(strict_types=1);

namespace Tests\E2E\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\InMemoryPackagesProviderLocator;
use Lendable\ComposerLicenseChecker\LicenseChecker;
use Lendable\ComposerLicenseChecker\LicenseConfiguration;
use Lendable\ComposerLicenseChecker\PackagesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\Lendable\ComposerLicenseChecker\LicenseConfigurationFileBuilder;

abstract class LicenseCheckerCase extends TestCase
{
    private CommandTester $commandTester;

    private string $path = 'tests'.\DIRECTORY_SEPARATOR.'data'.\DIRECTORY_SEPARATOR;

    protected function setUp(): void
    {
        $command = new LicenseChecker(
            new InMemoryPackagesProviderLocator([
                'id' => $this->packagesProvider(),
            ]),
        );
        $command->setAutoExit(false);

        $this->commandTester = new CommandTester($command);
    }

    abstract protected function packagesProvider(): PackagesProvider;

    public function test_failure_with_non_existent_allowed_licenses_file(): void
    {
        $exitCode = $this->commandTester->execute(['--allow-file' => 'dont_have.pls']);
        $output = $this->getOutputLines();

        self::assertSame(1, $exitCode);
        self::assertCount(4, $output);
        self::assertSame('[ERROR] File "dont_have.pls" could not be read.', $output[3]);
    }

    public function test_failure_with_non_existent_path(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => 'joke/path']);
        $output = $this->getOutputLines();

        self::assertSame(1, $exitCode);
        self::assertCount(4, $output);
        self::assertSame('[ERROR] The provided path "joke/path" does not exist.', $output[3]);
    }

    public function test_failure_with_path_that_is_not_composer_project_root(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => '../']);
        $output = $this->getOutputLines();

        self::assertSame(1, $exitCode);
        self::assertStringStartsWith('[ERROR] Failed to provide packages:', $output[3]);
    }

    public function test_failure_with_invalid_allowed_licenses_file(): void
    {
        $exitCode = $this->commandTester->execute([
            '--allow-file' => 'tests/data/invalid_allowed_licenses.php',
            '--path' => $this->path,
        ]);
        $output = $this->getOutputLines();

        self::assertSame(1, $exitCode);
        self::assertCount(5, $output);
        self::assertSame('[ERROR] File "tests/data/invalid_allowed_licenses.php" must return an instance of', $output[3]);
        self::assertSame(LicenseConfiguration::class.'.', $output[4]);
    }

    public function test_no_licenses_allowed(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);
        $output = $this->getOutputLines();

        self::assertSame(1, $exitCode);
        self::assertCount(12, $output);
        self::assertSame(
            '[ERROR] Dependency "lendable/apache" has license "Apache-2.0" which is not in the allowed list.',
            $output[3],
        );
        self::assertSame(
            '[ERROR] Dependency "lendable/bsd3_mit" has license "BSD-3-Clause" which is not in the allowed list.',
            $output[5],
        );
        self::assertSame(
            '[ERROR] Dependency "lendable/bsd3_mit" has license "MIT" which is not in the allowed list.',
            $output[7],
        );
        self::assertSame(
            '[ERROR] Dependency "package/bsd3" has license "BSD-3-Clause" which is not in the allowed list.',
            $output[9],
        );
    }

    public function test_report_not_allowed_licenses(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withLicense('MIT')
            ->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);
        $output = $this->getOutputLines();

        self::assertSame(1, $exitCode);
        self::assertCount(8, $output);
        self::assertSame(
            '[ERROR] Dependency "lendable/apache" has license "Apache-2.0" which is not in the allowed list.',
            $output[3],
        );
        self::assertSame(
            '[ERROR] Dependency "lendable/bsd3_mit" has license "BSD-3-Clause" which is not in the allowed list.',
            $output[5],
        );
        self::assertSame(
            '[ERROR] Dependency "package/bsd3" has license "BSD-3-Clause" which is not in the allowed list.',
            $output[7],
        );
    }

    public function test_report_not_allowed_vendor_licenses(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withAllowedVendor('lendable')
            ->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);
        $output = $this->getOutputLines();

        self::assertSame(1, $exitCode);
        self::assertCount(6, $output);
        self::assertSame(
            '[ERROR] Dependency "package/bsd3" has license "BSD-3-Clause" which is not in the allowed list.',
            $output[3],
        );
        self::assertSame(
            '[ERROR] Dependency "package/mit" has license "MIT" which is not in the allowed list.',
            $output[5],
        );
    }

    public function test_all_licenses_allowed(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withLicense('Apache-2.0')
            ->withLicense('BSD-3-Clause')
            ->withLicense('MIT')
            ->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);
        $output = $this->getOutputLines();

        self::assertSame(0, $exitCode);
        self::assertCount(4, $output);
        self::assertSame('[OK] All dependencies have allowed licenses.', $output[3]);
    }

    public function test_all_licenses_allowed_through_vendors(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withAllowedVendor('lendable')
            ->withAllowedVendor('package')
            ->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);
        $output = $this->getOutputLines();

        self::assertSame(0, $exitCode);
        self::assertCount(4, $output);
        self::assertSame('[OK] All dependencies have allowed licenses.', $output[3]);
    }

    public function test_ignores_dev_dependencies_when_requested_to_through_option(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withLicense('MIT')
            ->withLicense('BSD-3-Clause')
            ->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path, '--no-dev' => true]);
        $output = $this->getOutputLines();

        self::assertSame(0, $exitCode);
        self::assertCount(4, $output);
        self::assertSame('[OK] All dependencies have allowed licenses.', $output[3]);
    }

    public function test_ignores_dev_dependencies_when_requested_to_through_config(): void
    {
        $handle = $this->createTempFile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withLicense('MIT')
            ->withLicense('BSD-3-Clause')
            ->withIgnoreDev(true)
            ->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile, '--path' => $this->path]);
        $output = $this->getOutputLines();

        self::assertSame(0, $exitCode);
        self::assertCount(4, $output);
        self::assertSame('[OK] All dependencies have allowed licenses.', $output[3]);
    }

    /**
     * @return list<string>
     */
    private function getOutputLines(): array
    {
        return \array_map(\trim(...), \explode(\PHP_EOL, \trim($this->commandTester->getDisplay())));
    }

    /**
     * @return resource
     */
    private function createTempFile(): mixed
    {
        $handle = \tmpfile();

        if ($handle === false) {
            throw new \RuntimeException('Failed to create temp file.');
        }

        return $handle;
    }
}
