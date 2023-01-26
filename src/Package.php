<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

final class Package
{
    /**
     * @param list<string> $licenses
     */
    public function __construct(
        public readonly PackageName $name,
        public readonly array $licenses,
    ) {
    }
}
