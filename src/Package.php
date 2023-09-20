<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

final class Package
{
    public function __construct(
        public readonly PackageName $name,
        public readonly Licenses $licenses,
    ) {
    }

    public function isUnlicensed(): bool
    {
        return $this->licenses->isEmpty();
    }
}
