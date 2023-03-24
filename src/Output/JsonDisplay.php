<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Output;

use Lendable\ComposerLicenseChecker\Package;
use Symfony\Component\Console\Output\OutputInterface;

final class JsonDisplay implements Display
{
    public const ENCODING_FLAGS = \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT;

    /**
     * @var array<string, list<string>>
     */
    public array $packagesWithViolatingLicenses = [];

    public function __construct(public readonly OutputInterface $output)
    {
    }

    public function onStarted(): void
    {
        $this->packagesWithViolatingLicenses = [];
    }

    public function onFatalError(string $message): void
    {
        $this->output->writeln(
            \json_encode(
                ['result' => 'error', 'message' => $message],
                self::ENCODING_FLAGS
            )
        );
    }

    public function onPackageWithViolatingLicense(Package $package, string $license): void
    {
        $this->packagesWithViolatingLicenses[$package->name->toString()][] = $license;
    }

    public function onUnlicensedPackageNotExplicitlyAllowed(Package $package): void
    {
        $this->packagesWithViolatingLicenses[$package->name->toString()][] = 'UNLICENSED';
    }

    public function onOutcomeFailure(): void
    {
        $this->output->writeln(
            \json_encode(
                ['result' => 'failure', 'violations' => $this->packagesWithViolatingLicenses],
                self::ENCODING_FLAGS,
            )
        );
    }

    public function onOutcomeSuccess(): void
    {
        $this->output->writeln(
            \json_encode(
                ['result' => 'success'],
                self::ENCODING_FLAGS,
            )
        );
    }

    public function onTraceInformation(string $message): void
    {
    }
}
