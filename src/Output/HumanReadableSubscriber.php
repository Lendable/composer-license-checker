<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Output;

use Lendable\ComposerLicenseChecker\Event\FatalError;
use Lendable\ComposerLicenseChecker\Event\OutcomeFailure;
use Lendable\ComposerLicenseChecker\Event\OutcomeSuccess;
use Lendable\ComposerLicenseChecker\Event\PackageWithViolatingLicense;
use Lendable\ComposerLicenseChecker\Event\Started;
use Lendable\ComposerLicenseChecker\Event\Subscriber;
use Lendable\ComposerLicenseChecker\Event\Subscription;
use Lendable\ComposerLicenseChecker\Event\TraceInformation;
use Lendable\ComposerLicenseChecker\Event\UnlicensedPackageNotExplicitlyAllowed;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class HumanReadableSubscriber implements Subscriber
{
    private readonly SymfonyStyle $style;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->style = new SymfonyStyle($input, $output);
    }

    public function subscriptions(): array
    {
        return [
            new Subscription(Started::class, $this->onStarted(...)),
            new Subscription(FatalError::class, $this->onFatalError(...)),
            new Subscription(PackageWithViolatingLicense::class, $this->onPackageWithViolatingLicense(...)),
            new Subscription(UnlicensedPackageNotExplicitlyAllowed::class, $this->onUnlicensedPackageNotExplicitlyAllowed(...)),
            new Subscription(OutcomeFailure::class, $this->onOutcomeFailure(...)),
            new Subscription(OutcomeSuccess::class, $this->onOutcomeSuccess(...)),
            new Subscription(TraceInformation::class, $this->onTraceInformation(...)),
        ];
    }

    public function onStarted(Started $event): void
    {
        $this->style->title('Composer License Checker');
    }

    public function onFatalError(FatalError $event): void
    {
        $this->style->error($event->message);
    }

    public function onOutcomeFailure(OutcomeFailure $event): void
    {
    }

    public function onOutcomeSuccess(OutcomeSuccess $event): void
    {
        $this->style->success('All dependencies have allowed licenses.');
    }

    public function onPackageWithViolatingLicense(PackageWithViolatingLicense $event): void
    {
        $this->style->error(
            \sprintf(
                'Dependency "%s" has license "%s" which is not in the allowed list.',
                $event->package->name->toString(),
                $event->license,
            )
        );
    }

    public function onUnlicensedPackageNotExplicitlyAllowed(UnlicensedPackageNotExplicitlyAllowed $event): void
    {
        $this->style->error(
            \sprintf(
                'Dependency "%s" does not have a license and is not explicitly allowed.',
                $event->package->name->toString(),
            )
        );
    }

    public function onTraceInformation(TraceInformation $event): void
    {
        $this->style->writeln($event->message, OutputInterface::VERBOSITY_VERBOSE);
    }
}
