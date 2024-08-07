<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

use Lendable\ComposerLicenseChecker\ComposerRunner;

final readonly class StubComposerRunner implements ComposerRunner
{
    /**
     * @param Result<string> $result
     */
    public function __construct(private Result $result) {}

    public function licenses(string $projectPath, bool $ignoreDev): string
    {
        return $this->result->provide();
    }
}
