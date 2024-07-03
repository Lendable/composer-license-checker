<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

final readonly class Package
{
    public function __construct(
        public PackageName $name,
        public Licenses $licenses,
    ) {}

    public function isUnlicensed(): bool
    {
        return $this->licenses->isEmpty();
    }
}
