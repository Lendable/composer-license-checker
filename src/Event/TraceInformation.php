<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

final class TraceInformation implements Event
{
    /**
     * @param non-empty-string $message
     */
    public function __construct(public readonly string $message)
    {
    }
}
