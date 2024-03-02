<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

use Lendable\ComposerLicenseChecker\Package;

final readonly class UnlicensedPackageNotExplicitlyAllowed implements Event
{
    public function __construct(public Package $package)
    {
    }
}
