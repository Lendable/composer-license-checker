<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

use Lendable\ComposerLicenseChecker\Package;

final class UnlicensedPackageNotExplicitlyAllowed implements Event
{
    public function __construct(public readonly Package $package)
    {
    }
}
