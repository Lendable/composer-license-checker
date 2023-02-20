<?php

declare(strict_types=1);

namespace Tests\Unit\Lendable\ComposerLicenseChecker\PackagesProvider;

use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;
use Lendable\ComposerLicenseChecker\PackagesProvider\ComposerInstalledJsonPackagesProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @todo Add happy path tests, including ignoring dev, license key being optional, etc.
 */
final class ComposerInstalledJsonPackagesProviderTest extends TestCase
{
    /**
     * @var non-empty-string
     */
    private string $projectPath;

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->projectPath);
    }

    public function test_throws_if_installed_json_missing(): void
    {
        $this->setUpTemporaryProjectWithInstalledJson([]);
        (new Filesystem())->remove($this->projectPath.'/vendor/composer/installed.json');

        $this->expectExceptionObject(
            FailedProvidingPackages::withReason(
                \sprintf('File "%s" not found', $this->projectPath.'/vendor/composer/installed.json'),
            )
        );

        (new ComposerInstalledJsonPackagesProvider())->provide($this->projectPath, false);
    }

    public function test_throws_if_installed_json_not_readable(): void
    {
        $this->setUpTemporaryProjectWithInstalledJson([]);

        (new Filesystem())->chmod($this->projectPath.'/vendor/composer/installed.json', 0o222);

        $this->expectExceptionObject(
            FailedProvidingPackages::withReason(
                \sprintf('File "%s" is not readable', $this->projectPath.'/vendor/composer/installed.json'),
            )
        );

        (new ComposerInstalledJsonPackagesProvider())->provide($this->projectPath, false);
    }

    public function test_throws_if_installed_json_contains_non_json(): void
    {
        $this->setUpTemporaryProjectWithInstalledJson('{"invalid": json');

        $this->expectExceptionObject(FailedProvidingPackages::withReason('Decoding failed "Syntax error"'));

        (new ComposerInstalledJsonPackagesProvider())->provide($this->projectPath, false);
    }

    public function test_throws_if_installed_json_contains_json_which_is_not_an_array(): void
    {
        $this->setUpTemporaryProjectWithInstalledJson('"literal_json_string"');

        $this->expectExceptionObject(FailedProvidingPackages::withReason('Decoded data in unexpected format'));

        (new ComposerInstalledJsonPackagesProvider())->provide($this->projectPath, false);
    }

    /**
     * @return iterable<array{array<mixed>, FailedProvidingPackages}>
     */
    public static function provideInstalledJsonDataWithErrorsAndExpectedExceptions(): iterable
    {
        yield 'Missing dev-package-names' => [
            [
                'dev' => true,
                'packages' => [
                    [
                        'name' => 'foo/bar',
                        'version' => '1.0.0',
                        'licenses' => ['MIT'],
                    ],
                ],
            ],
            FailedProvidingPackages::withReason('Missing "dev-package-names" key'),
        ];
        yield 'dev-package-names not an array' => [
            [
                'dev' => true,
                'packages' => [
                    [
                        'name' => 'foo/bar',
                        'version' => '1.0.0',
                        'licenses' => ['MIT'],
                    ],
                ],
                'dev-package-names' => 'foo',
            ],
            FailedProvidingPackages::withReason('Decoded dev dependencies data in unexpected format'),
        ];
        yield 'dev-package-names not a list' => [
            [
                'dev' => true,
                'packages' => [
                    [
                        'name' => 'foo/bar',
                        'version' => '1.0.0',
                        'licenses' => ['MIT'],
                    ],
                ],
                'dev-package-names' => ['foo' => 'bar'],
            ],
            FailedProvidingPackages::withReason('Decoded dev dependencies data in unexpected format'),
        ];
        yield 'packages key missing' => [
            [
                'dev' => true,
                'dev-package-names' => ['foo/bar'],
            ],
            FailedProvidingPackages::withReason('Missing "packages" key'),
        ];
        yield 'packages not an array (bool, only one element)' => [
            [
                'dev' => true,
                'dev-package-names' => ['foo/bar'],
                'packages' => false,
            ],
            FailedProvidingPackages::withReason('Decoded data in unexpected format'),
        ];
        yield 'package not an array (bool)' => [
            [
                'dev' => true,
                'dev-package-names' => [],
                'packages' => [false, ['name' => 'foo/bar', 'version' => '1.0.0', 'license' => ['MIT']]],
            ],
            FailedProvidingPackages::withReason('Package data in unexpected format'),
        ];
        yield 'package missing name key' => [
            [
                'dev' => true,
                'dev-package-names' => [],
                'packages' => [['version' => '1.0.0', 'license' => ['MIT']]],
            ],
            FailedProvidingPackages::withReason('Missing "name" key for package'),
        ];
        yield 'package with name key not a string' => [
            [
                'dev' => true,
                'dev-package-names' => [],
                'packages' => [['name' => true, 'version' => '1.0.0', 'license' => ['MIT']]],
            ],
            FailedProvidingPackages::withReason('Key "name" is not a string'),
        ];
        yield 'package with license key that is not a list (object)' => [
            [
                'dev' => true,
                'dev-package-names' => [],
                'packages' => [['name' => 'foo/bar', 'version' => '1.0.0', 'license' => ['MIT' => true]]],
            ],
            FailedProvidingPackages::withReason('Key "license" is not a list'),
        ];
        yield 'package with license key that is not a list (string)' => [
            [
                'dev' => true,
                'dev-package-names' => [],
                'packages' => [['name' => 'foo/bar', 'version' => '1.0.0', 'license' => 'MIT']],
            ],
            FailedProvidingPackages::withReason('Key "license" is not a list'),
        ];
    }

    /**
     * @param array<mixed> $data
     */
    #[DataProvider('provideInstalledJsonDataWithErrorsAndExpectedExceptions')]
    public function test_throws_when_installed_json_has_valid_json_but_unexpected_data(
        array $data,
        FailedProvidingPackages $expectedException,
    ): void {
        $this->setUpTemporaryProjectWithInstalledJson($data);

        $this->expectExceptionObject($expectedException);

        (new ComposerInstalledJsonPackagesProvider())->provide($this->projectPath, true);
    }

    /**
     * @param array<mixed> $data
     */
    private function setUpTemporaryProjectWithInstalledJson(string|array $data): void
    {
        $fs = new Filesystem();
        $tempDir = \sys_get_temp_dir();
        $attempts = 0;

        do {
            $projectPath = \sprintf(
                '%s%s%s',
                $tempDir,
                \DIRECTORY_SEPARATOR,
                'license_checker_tests_project_'.\random_int(1, 5000)
            );

            if (!\is_dir($projectPath)) {
                $installedJsonDir = \sprintf('%s%2$svendor%2$scomposer', $projectPath, \DIRECTORY_SEPARATOR);
                $fs->mkdir($installedJsonDir);


                $fs->dumpFile(
                    \sprintf('%s%sinstalled.json', $installedJsonDir, \DIRECTORY_SEPARATOR),
                    \is_string($data) ? $data : \json_encode($data, \JSON_THROW_ON_ERROR),
                );

                $this->projectPath = $projectPath;

                return;
            }

            $attempts++;
        } while ($attempts < 100);

        throw new \RuntimeException(
            'Failed to create a temporary project, could not find a unique temporary directory.',
        );
    }
}
