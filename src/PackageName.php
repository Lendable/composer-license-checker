<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\InvalidPackageName;

final class PackageName
{
    public readonly string $vendor;

    public readonly string $project;

    /**
     * @throws InvalidPackageName
     */
    public function __construct(string $packageName)
    {
        $data = \explode('/', $packageName);
        if (\count($data) !== 2) {
            throw InvalidPackageName::for($packageName);
        }

        [$this->vendor, $this->project] = $data;
    }

    public function toString(): string
    {
        return \sprintf('%s/%s', $this->vendor, $this->project);
    }
}
