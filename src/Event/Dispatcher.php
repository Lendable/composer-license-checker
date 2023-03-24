<?php

declare(strict_types=1);

namespace Lendable\ComposerLicenseChecker\Event;

final class Dispatcher
{
    /**
     * @var array<class-string<Event>, non-empty-list<\Closure>>
     */
    private array $subscriptions = [];

    public function attach(Subscriber $subscriber): void
    {
        foreach ($subscriber->subscriptions() as $subscription) {
            $this->subscriptions[$subscription->eventClass][] = $subscription->handler;
        }
    }

    public function dispatch(Event $event): void
    {
        foreach ($this->subscriptions[$event::class] ?? [] as $handler) {
            $handler($event);
        }
    }
}
