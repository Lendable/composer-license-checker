<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

final class UnlicensedPackageNotExplicitlyAllowed implements Event
{
    public function __construct(public readonly string $package)
    {
    }
}
