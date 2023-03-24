<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Exception;

final class InvalidPackageName extends \RuntimeException
{
    /**
     * @param non-empty-string $message
     */
    private function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @param non-empty-string $packageName
     */
    public static function for(string $packageName): self
    {
        return new self(\sprintf('Invalid package name: %s', $packageName));
    }
}
