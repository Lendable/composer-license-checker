<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

final class PackageWithViolatingLicense implements Event
{
    /**
     * @param non-empty-string $package
     * @param non-empty-string $license
     */
    public function __construct(public readonly string $package, public readonly string $license)
    {
    }
}
