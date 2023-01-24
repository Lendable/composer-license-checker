<?php

declare(strict_types=1);

namespace Tests\E2E\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\LicenseChecker;
use Lendable\ComposerLicenseChecker\LicenseConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\Lendable\ComposerLicenseChecker\LicenseConfigurationFileBuilder;

final class LicenseCheckerTest extends TestCase
{
    private CommandTester $commandTester;

    private static string $previousCwd;

    public static function setUpBeforeClass(): void
    {
        self::$previousCwd = \getcwd();
        \chdir(__DIR__.\DIRECTORY_SEPARATOR.'..'.\DIRECTORY_SEPARATOR.'data');
    }

    public static function tearDownAfterClass(): void
    {
        \chdir(self::$previousCwd);
    }

    protected function setUp(): void
    {
        $command = new LicenseChecker();
        $command->setAutoExit(false);

        $this->commandTester = new CommandTester($command);
    }

    public function test_failure_with_non_existent_allowed_licenses_file(): void
    {
        $exitCode = $this->commandTester->execute(['--allow-file' => 'dont_have.pls']);
        $output = $this->getOutputLines();

        self::assertSame(1, $exitCode);
        self::assertCount(4, $output);
        self::assertSame('[ERROR] File "dont_have.pls" could not be read.', $output[3]);
    }

    public function test_failure_with_invalid_allowed_licenses_file(): void
    {
        $exitCode = $this->commandTester->execute(['--allow-file' => 'invalid_allowed_licenses.php']);
        $output = $this->getOutputLines();

        self::assertSame(1, $exitCode);
        self::assertCount(5, $output);
        self::assertSame('[ERROR] File "invalid_allowed_licenses.php" must return an instance of', $output[3]);
        self::assertSame(LicenseConfiguration::class.'.', $output[4]);
    }

    public function test_no_licenses_allowed(): void
    {
        $handle = \tmpfile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile]);
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
        $handle = \tmpfile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withLicense('MIT')
            ->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile]);
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
        $handle = \tmpfile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withAllowedVendor('lendable')
            ->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile]);
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
        $handle = \tmpfile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withLicense('Apache-2.0')
            ->withLicense('BSD-3-Clause')
            ->withLicense('MIT')
            ->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile]);
        $output = $this->getOutputLines();

        self::assertSame(0, $exitCode);
        self::assertCount(4, $output);
        self::assertSame('[OK] All dependencies have allowed licenses.', $output[3]);
    }

    public function test_all_licenses_allowed_through_vendors(): void
    {
        $handle = \tmpfile();
        $allowFile = LicenseConfigurationFileBuilder::create($handle)
            ->withAllowedVendor('lendable')
            ->withAllowedVendor('package')
            ->build();

        $exitCode = $this->commandTester->execute(['--allow-file' => $allowFile]);
        $output = $this->getOutputLines();

        self::assertSame(0, $exitCode);
        self::assertCount(4, $output);
        self::assertSame('[OK] All dependencies have allowed licenses.', $output[3]);
    }

    private function getOutputLines(): array
    {
        return \array_map(\trim(...), \explode(\PHP_EOL, \trim($this->commandTester->getDisplay())));
    }
}
