<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

/**
 * @template T
 */
interface Result
{
    /**
     * @return T
     */
    public function provide(): mixed;
}
