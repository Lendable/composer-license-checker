<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\PackagesProvider;

use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;
use Lendable\ComposerLicenseChecker\Licenses;
use Lendable\ComposerLicenseChecker\Package;
use Lendable\ComposerLicenseChecker\PackageName;
use Lendable\ComposerLicenseChecker\Packages;
use Lendable\ComposerLicenseChecker\PackagesProvider;

/**
 * @phpstan-type PackageData array{name: non-empty-string, version: non-empty-string, license?: list<non-empty-string>}
 * @phpstan-type InstalledData array{dev: bool, dev-package-names: list<non-empty-string>, packages: list<PackageData>}
 */
final class ComposerInstalledJsonPackagesProvider implements PackagesProvider
{
    public function provide(string $projectPath, bool $ignoreDev): Packages
    {
        $installedJson = \sprintf(
            '%s%2$svendor%2$scomposer%2$sinstalled.json',
            \realpath(\rtrim($projectPath, \DIRECTORY_SEPARATOR)),
            \DIRECTORY_SEPARATOR,
        );

        if (!\file_exists($installedJson)) {
            throw FailedProvidingPackages::withReason(\sprintf('File "%s" not found', $installedJson));
        }

        if (!\is_readable($installedJson)) {
            throw FailedProvidingPackages::withReason(\sprintf('File "%s" is not readable', $installedJson));
        }

        try {
            $data = \json_decode((string) \file_get_contents($installedJson), true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw FailedProvidingPackages::withReason(\sprintf('Decoding failed "%s"', $e->getMessage()));
        }

        if (!\is_array($data)) {
            throw FailedProvidingPackages::withReason('Decoded data in unexpected format');
        }

        $dependencies = $data['packages'] ?? throw FailedProvidingPackages::withReason('Missing "packages" key');
        if (!\is_array($dependencies)) {
            throw FailedProvidingPackages::withReason('Decoded data in unexpected format');
        }

        $skipPackages = [];
        if ($ignoreDev) {
            $devDependencies = $data['dev-package-names'] ?? throw FailedProvidingPackages::withReason('Missing "dev-package-names" key');
            if (!\is_array($devDependencies) || !\array_is_list($devDependencies)) {
                throw FailedProvidingPackages::withReason('Decoded dev dependencies data in unexpected format');
            }

            /** @var list<string> $devDependencies */
            $skipPackages = \array_flip($devDependencies);
        }

        return (new Packages(
            \array_filter(
                \array_map(
                    static function (mixed $package) use ($skipPackages): ?Package {
                        if (!\is_array($package)) {
                            throw FailedProvidingPackages::withReason('Package data in unexpected format');
                        }

                        if (!isset($package['name'])) {
                            throw FailedProvidingPackages::withReason('Missing "name" key for package');
                        }

                        if (isset($skipPackages[$package['name']])) {
                            return null;
                        }

                        if (!\is_string($package['name'])) {
                            throw FailedProvidingPackages::withReason('Key "name" is not a string');
                        }

                        if ($package['name'] === '') {
                            throw FailedProvidingPackages::withReason('Key "name" is an empty string');
                        }

                        $licenses = $package['license'] ?? [];

                        if (!\is_array($licenses) || !\array_is_list($licenses)) {
                            throw FailedProvidingPackages::withReason('Key "license" is not a list');
                        }

                        /** @var PackageData $package */
                        return new Package(
                            new PackageName($package['name']),
                            new Licenses($licenses),
                        );
                    },
                    $dependencies,
                ),
                static fn(?Package $package): bool => $package !== null,
            ),
        ))->sort();
    }
}
