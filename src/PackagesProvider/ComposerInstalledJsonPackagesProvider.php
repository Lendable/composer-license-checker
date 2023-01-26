<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\PackagesProvider;

use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;
use Lendable\ComposerLicenseChecker\Package;
use Lendable\ComposerLicenseChecker\PackageName;
use Lendable\ComposerLicenseChecker\Packages;
use Lendable\ComposerLicenseChecker\PackagesProvider;

final class ComposerInstalledJsonPackagesProvider implements PackagesProvider
{
    private const DS = \DIRECTORY_SEPARATOR;

    public function provide(string $projectPath): Packages
    {
        $installedJson = (string) \realpath(
            \sprintf('%s%2$svendor%2$scomposer%2$sinstalled.json', \rtrim($projectPath, self::DS), self::DS)
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

        return (new Packages(
            \array_map(static function (mixed $package): Package {
                if (!\is_array($package)) {
                    throw FailedProvidingPackages::withReason('Package data in unexpected format');
                }

                if (!isset($package['name'])) {
                    throw FailedProvidingPackages::withReason('Missing "name" key for package');
                }

                if (!\is_string($package['name'])) {
                    throw FailedProvidingPackages::withReason('Key "name" is not a string');
                }

                if (!isset($package['license'])) {
                    throw FailedProvidingPackages::withReason('Missing "license" key for package');
                }

                if (!\is_array($package['license']) || !\array_is_list($package['license'])) {
                    throw FailedProvidingPackages::withReason('Key "license" is not a list');
                }

                /** @var array{name: string, license: list<string>} $package */
                return new Package(
                    new PackageName($package['name']),
                    \array_values($package['license']),
                );
            }, $dependencies)
        ))->sorted();
    }
}
