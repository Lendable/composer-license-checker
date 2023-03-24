<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Exception;

final class FailedRunningComposer extends \RuntimeException
{
    /**
     * @param non-empty-string $message
     */
    private function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function withCommand(string $command): self
    {
        return new self(\sprintf('Failed to run: %s', $command));
    }
}
