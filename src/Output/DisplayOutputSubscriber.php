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

final readonly class DisplayOutputSubscriber implements Subscriber
{
    public function __construct(private Display $display)
    {
    }

    public function subscriptions(): iterable
    {
        yield new Subscription(Started::class, $this->onStarted(...));
        yield new Subscription(FatalError::class, $this->onFatalError(...));
        yield new Subscription(PackageWithViolatingLicense::class, $this->onPackageWithViolatingLicense(...));
        yield new Subscription(UnlicensedPackageNotExplicitlyAllowed::class, $this->onUnlicensedPackageNotExplicitlyAllowed(...));
        yield new Subscription(OutcomeFailure::class, $this->onOutcomeFailure(...));
        yield new Subscription(OutcomeSuccess::class, $this->onOutcomeSuccess(...));
        yield new Subscription(TraceInformation::class, $this->onTraceInformation(...));
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
        $this->display->onPackageWithViolatingLicense($event->package);
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
