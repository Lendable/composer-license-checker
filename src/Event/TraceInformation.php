<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

final readonly class TraceInformation implements Event
{
    /**
     * @param non-empty-string $message
     */
    public function __construct(public string $message)
    {
    }
}
