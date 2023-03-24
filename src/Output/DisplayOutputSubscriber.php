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

final class DisplayOutputSubscriber implements Subscriber
{
    public function __construct(private readonly Display $display)
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
            new Subscription(TraceInformation::class, $this->onTraceInformation(...)),
        ];
    }

    private function onStarted(Started $event): void
    {
        $this->display->onStarted();
    }

    private function onFatalError(FatalError $event): void
    {
        $this->display->onFatalError($event->message);
    }

    private function onOutcomeFailure(OutcomeFailure $event): void
    {
        $this->display->onOutcomeFailure();
    }

    private function onOutcomeSuccess(OutcomeSuccess $event): void
    {
        $this->display->onOutcomeSuccess();
    }

    private function onPackageWithViolatingLicense(PackageWithViolatingLicense $event): void
    {
        $this->display->onPackageWithViolatingLicense($event->package, $event->license);
    }

    private function onUnlicensedPackageNotExplicitlyAllowed(UnlicensedPackageNotExplicitlyAllowed $event): void
    {
        $this->display->onUnlicensedPackageNotExplicitlyAllowed($event->package);
    }

    private function onTraceInformation(TraceInformation $event): void
    {
        $this->display->onTraceInformation($event->message);
    }
}
