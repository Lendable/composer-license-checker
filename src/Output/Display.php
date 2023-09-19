<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Output;

use Lendable\ComposerLicenseChecker\Package;

interface Display
{
    public function onStarted(): void;

    /**
     * @param non-empty-string $message
     */
    public function onFatalError(string $message): void;

    public function onOutcomeFailure(): void;

    public function onOutcomeSuccess(): void;

    public function onPackageWithViolatingLicense(Package $package): void;

    public function onUnlicensedPackageNotExplicitlyAllowed(Package $package): void;

    /**
     * @param non-empty-string $message
     */
    public function onTraceInformation(string $message): void;
}
