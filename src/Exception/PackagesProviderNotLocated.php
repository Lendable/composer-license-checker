<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Exception;

use Lendable\ComposerLicenseChecker\PackagesProviderType;

final class PackagesProviderNotLocated extends \RuntimeException
{
    /**
     * @param non-empty-string $message
     */
    private function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function withType(PackagesProviderType $type): self
    {
        return new self(\sprintf('Could not locate packages provider with type "%s"', $type->value));
    }
}
