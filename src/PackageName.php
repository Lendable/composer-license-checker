<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\Exception\InvalidPackageName;

final class PackageName
{
    /**
     * @var non-empty-string
     */
    public readonly string $vendor;

    /**
     * @var non-empty-string
     */
    public readonly string $project;

    /**
     * @param non-empty-string $packageName
     *
     * @throws InvalidPackageName
     */
    public function __construct(string $packageName)
    {
        $data = \explode('/', $packageName);
        if (\count($data) !== 2) {
            throw InvalidPackageName::for($packageName);
        }

        if ($data[0] === '' || $data[1] === '') {
            throw InvalidPackageName::for($packageName);
        }

        $this->vendor = $data[0];
        $this->project = $data[1];
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        return \sprintf('%s/%s', $this->vendor, $this->project);
    }
}
