<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker;

/**
 * @implements \IteratorAggregate<int, Package>
 */
final readonly class Packages implements \Countable, \IteratorAggregate
{
    /**
     * @param array<Package> $items
     */
    public function __construct(
        private array $items,
    ) {}

    public function sort(): self
    {
        $items = $this->items;
        \usort($items, static fn(Package $a, Package $b): int => $a->name->toString() <=> $b->name->toString());

        return new self($items);
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->items;
    }
}
