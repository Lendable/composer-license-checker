<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Output;

use Lendable\ComposerLicenseChecker\Package;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class HumanReadableDisplay implements Display
{
    private readonly SymfonyStyle $style;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->style = new SymfonyStyle($input, $output);
    }

    public function onStarted(): void
    {
        $this->style->title('Composer License Checker');
    }

    public function onFatalError(string $message): void
    {
        $this->style->error($message);
    }

    public function onOutcomeFailure(): void
    {
    }

    public function onOutcomeSuccess(): void
    {
        $this->style->success('All dependencies have allowed licenses.');
    }

    public function onPackageWithViolatingLicense(Package $package): void
    {
        if ($package->licenses->isDisjunctive()) {
            $this->style->error(
                \sprintf(
                    'Dependency "%s" is licensed under any of "%s", none of which are allowed.',
                    $package->name->toString(),
                    $package->licenses->toString(),
                ),
            );
        } else {
            $this->style->error(
                \sprintf(
                    'Dependency "%s" is licensed under "%s" which is not in the allowed list.',
                    $package->name->toString(),
                    $package->licenses->toString(),
                ),
            );
        }
    }

    public function onUnlicensedPackageNotExplicitlyAllowed(Package $package): void
    {
        $this->style->error(
            \sprintf(
                'Dependency "%s" does not have a license and is not explicitly allowed.',
                $package->name->toString(),
            ),
        );
    }

    public function onTraceInformation(string $message): void
    {
        $this->style->writeln($message, OutputInterface::VERBOSITY_VERBOSE);
    }
}
