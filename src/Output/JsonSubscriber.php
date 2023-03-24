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
use Lendable\ComposerLicenseChecker\Event\UnlicensedPackageNotExplicitlyAllowed;
use Symfony\Component\Console\Output\OutputInterface;

final class JsonSubscriber implements Subscriber
{
    /**
     * @var array<string, list<string>>
     */
    private array $packagesWithViolatingLicenses = [];

    public function __construct(private readonly OutputInterface $output)
    {
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
        ];
    }

    private function onStarted(Started $event): void
    {
        $this->packagesWithViolatingLicenses = [];
    }

    private function onFatalError(FatalError $event): void
    {
        $this->output->writeln(
            \json_encode(
                ['result' => 'error', 'message' => $event->message],
                \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT
            )
        );
    }

    private function onPackageWithViolatingLicense(PackageWithViolatingLicense $event): void
    {
        $this->packagesWithViolatingLicenses[$event->package][] = $event->license;
    }

    private function onUnlicensedPackageNotExplicitlyAllowed(UnlicensedPackageNotExplicitlyAllowed $event): void
    {
        $this->packagesWithViolatingLicenses[$event->package][] = 'UNLICENSED';
    }

    private function onOutcomeFailure(OutcomeFailure $event): void
    {
        $this->output->writeln(
            \json_encode(
                ['result' => 'failure', 'violations' => $this->packagesWithViolatingLicenses],
                \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT,
            )
        );
    }

    private function onOutcomeSuccess(OutcomeSuccess $event): void
    {
        $this->output->writeln(
            \json_encode(
                ['result' => 'success'],
                \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT
            )
        );
    }
}
