<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Exception;

final class FailedProvidingPackages extends \RuntimeException
{
    private function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function withReason(string $reason): self
    {
        return new self(\sprintf('Failed to provide packages: %s', $reason));
    }

    public static function dueTo(\Throwable $cause): self
    {
        return new self('Failed to provide packages', $cause);
    }
}
