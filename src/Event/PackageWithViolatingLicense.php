<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

use Lendable\ComposerLicenseChecker\Package;

final class PackageWithViolatingLicense implements Event
{
    /**
     * @param non-empty-string $license
     */
    public function __construct(public readonly Package $package, public readonly string $license)
    {
    }
}
