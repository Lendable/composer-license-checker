<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

/**
 * @template-covariant T
 */
interface Result
{
    /**
     * @return T
     */
    public function provide(): mixed;
}
