<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

final readonly class Subscription
{
    /**
     * @template T of Event
     *
     * @param class-string<T> $eventClass
     * @param \Closure(T): void $handler
     */
    public function __construct(public string $eventClass, public \Closure $handler)
    {
    }
}
