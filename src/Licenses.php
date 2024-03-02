<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

/**
 * @implements \IteratorAggregate<int, string>
 */
final readonly class Licenses implements \IteratorAggregate
{
    /**
     * @param list<non-empty-string> $licenses
     */
    public function __construct(
        private array $licenses,
    ) {
    }

    public function toString(): string
    {
        return \implode(', ', $this->licenses);
    }

    public function isEmpty(): bool
    {
        return $this->licenses === [];
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->licenses);
    }

    public function isDisjunctive(): bool
    {
        return \count($this->licenses) > 1;
    }
}
