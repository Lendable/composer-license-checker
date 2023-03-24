<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

interface Subscriber
{
    /**
     * @return non-empty-list<Subscription>
     */
    public function subscriptions(): array;
}
