<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

interface Subscriber
{
    /**
     * @return iterable<Subscription>
     */
    public function subscriptions(): iterable;
}
