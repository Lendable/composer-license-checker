<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Output;

use Symfony\Component\Console\Output\OutputInterface;

final class JsonDisplay implements Display
{
    /**
     * @var array<string, list<string>>
     */
    private array $packagesWithViolatingLicenses = [];

    public function __construct(private readonly OutputInterface $output)
    {
    }

    public function onStart(): void
    {
        $this->packagesWithViolatingLicenses = [];
    }

    public function onFatalError(string $message): void
    {
        $this->output->writeln(\json_encode(['result' => 'error', 'message' => $message], \JSON_THROW_ON_ERROR));
    }

    public function onPackageWithViolatingLicense(string $package, string $license): void
    {
        $this->packagesWithViolatingLicenses[$package][] = $license;
    }

    public function onOverallFailure(): void
    {
        $this->output->writeln(
            \json_encode(
                ['result' => 'failure', 'violations' => $this->packagesWithViolatingLicenses],
                \JSON_THROW_ON_ERROR,
            )
        );
    }

    public function onOverallSuccess(): void
    {
        $this->output->writeln(\json_encode(['result' => 'success'], \JSON_THROW_ON_ERROR));
    }
}
