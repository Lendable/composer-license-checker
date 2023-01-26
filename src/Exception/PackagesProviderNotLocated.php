<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Exception;

final class PackagesProviderNotLocated extends \RuntimeException
{
    private function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function withId(string $id): self
    {
        return new self(\sprintf('Could not locate packages provider with id "%s"', $id));
    }
}
