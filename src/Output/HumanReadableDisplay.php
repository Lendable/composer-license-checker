<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Output;

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

    public function onStart(): void
    {
        $this->style->title('Composer License Checker');
    }

    public function onFatalError(string $message): void
    {
    }

    public function onOverallFailure(): void
    {
    }

    public function onOverallSuccess(): void
    {
        $this->style->success('All dependencies have allowed licenses.');
    }

    public function onPackageWithViolatingLicense(string $package, string $license): void
    {
        $this->style->error(
            \sprintf(
                'Dependency "%s" has license "%s" which is not in the allowed list.',
                $package,
                $license,
            )
        );
    }
}
