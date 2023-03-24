<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Output;

use Lendable\ComposerLicenseChecker\Package;
use Symfony\Component\Console\Output\OutputInterface;

final class JsonDisplay implements Display
{
    private const ENCODING_FLAGS = \JSON_THROW_ON_ERROR
    | \JSON_PRETTY_PRINT
    | \JSON_UNESCAPED_SLASHES
    | \JSON_UNESCAPED_UNICODE;

    /**
     * @var array<non-empty-string, list<non-empty-string>>
     */
    public array $violations = [];

    /**
     * @var list<non-empty-string>
     */
    private array $trace = [];

    public function __construct(public readonly OutputInterface $output)
    {
    }

    public function onStarted(): void
    {
        $this->violations = [];
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
        $this->violations[$license][] = $package->name->toString();
    }

    public function onUnlicensedPackageNotExplicitlyAllowed(Package $package): void
    {
        $this->violations['UNLICENSED'][] = $package->name->toString();
    }

    public function onOutcomeFailure(): void
    {
        $data = ['result' => 'failure', 'violations' => $this->violations];

        if ($this->output->isVerbose()) {
            $data['trace'] = $this->trace;
        }

        $this->output->writeln(
            \json_encode(
                $data,
                self::ENCODING_FLAGS,
            )
        );
    }

    public function onOutcomeSuccess(): void
    {
        $data = ['result' => 'success'];

        if ($this->output->isVerbose()) {
            $data['trace'] = $this->trace;
        }

        $this->output->writeln(
            \json_encode(
                $data,
                self::ENCODING_FLAGS,
            )
        );
    }

    public function onTraceInformation(string $message): void
    {
        $this->trace[] = $message;
    }
}
