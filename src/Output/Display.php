<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Output;

interface Display
{
    public function onStart(): void;

    /**
     * @param non-empty-string $message
     */
    public function onFatalError(string $message): void;

    /**
     * @param non-empty-string $package
     * @param non-empty-string $license
     */
    public function onPackageWithViolatingLicense(string $package, string $license): void;

    public function onOverallFailure(): void;

    public function onOverallSuccess(): void;

    public function onDetail(string $message): void;

    public function onPackageWithNoLicenseNotExplicitlyAllowed(string $package): void;
}
