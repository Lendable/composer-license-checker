<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

/**
 * @template-implements Result<never>
 */
final readonly class Throwing implements Result
{
    private function __construct(private \Throwable $throwable) {}

    public static function exception(\Throwable $throwable): self
    {
        return new self($throwable);
    }

    public function provide(): never
    {
        throw $this->throwable;
    }
}
