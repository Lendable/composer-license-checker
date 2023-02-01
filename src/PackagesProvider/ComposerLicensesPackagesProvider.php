<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\PackagesProvider;

use Lendable\ComposerLicenseChecker\ComposerRunner;
use Lendable\ComposerLicenseChecker\Exception\FailedProvidingPackages;
use Lendable\ComposerLicenseChecker\Exception\FailedRunningComposer;
use Lendable\ComposerLicenseChecker\Package;
use Lendable\ComposerLicenseChecker\PackageName;
use Lendable\ComposerLicenseChecker\Packages;
use Lendable\ComposerLicenseChecker\PackagesProvider;

final class ComposerLicensesPackagesProvider implements PackagesProvider
{
    public function __construct(private readonly ComposerRunner $composerRunner)
    {
    }

    public function provide(string $projectPath): Packages
    {
        try {
            $composerOutput = $this->composerRunner->licenses($projectPath);
        } catch (FailedRunningComposer $e) {
            throw FailedProvidingPackages::dueTo($e);
        }

        try {
            /** @var array{
             *      name: non-empty-string,
             *      version: non-empty-string,
             *      license: list<non-empty-string>,
             *      dependencies: array<non-empty-string, array{version: non-empty-string, license: list<non-empty-string>}>
             * }|false $data
             */
            $data = \json_decode($composerOutput, true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw FailedProvidingPackages::withReason(\sprintf('Decoding failed "%s"', $e->getMessage()));
        }

        if (!\is_array($data)) {
            throw FailedProvidingPackages::withReason('Decoded data in unexpected format');
        }

        return new Packages(
            \array_map(
                static fn (string $name, array $package): Package => new Package(new PackageName($name), $package['license']),
                \array_keys($data['dependencies']),
                $data['dependencies'],
            ),
        );
    }
}
