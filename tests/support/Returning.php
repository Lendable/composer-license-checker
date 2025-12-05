<?php

declare(strict_types=1);

namespace Tests\Support\Lendable\ComposerLicenseChecker;

/**
 * @template T
 *
 * @template-implements Result<T>
 */
final readonly class Returning implements Result
{
    /**
     * @param T $value
     */
    private function __construct(private mixed $value) {}

    /**
     * @template Y
     *
     * @param Y $value
     *
     * @return self<Y>
     */
    public static function value(mixed $value): self
    {
        return new self($value);
    }

    #[\Override]
    public function provide(): mixed
    {
        return $this->value;
    }
}
